<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * WooCommerce Products CSV Importer
 *
 * Handles both simple and variable products.
 */
class WooImporter
{
    public function __construct(private readonly ImageFetchPipeline $images) {}

    public function dryRun(string $filePath): array
    {
        $rows = $this->readCsv($filePath);
        [$parents, $variations] = $this->split($rows);

        $cats = [];
        foreach ($parents as $r) {
            foreach ($this->parseCategories($r['Categories'] ?? '') as $c) {
                $cats[$c] = true;
            }
        }

        $totalImages = 0;
        foreach ($parents as $r) {
            $imgs = array_filter(explode(',', $r['Images'] ?? ''));
            $totalImages += count($imgs);
        }

        return [
            'products'   => count($parents),
            'variants'   => count($variations),
            'categories' => count($cats),
            'images'     => $totalImages,
            'dry_run'    => true,
        ];
    }

    public function import(string $filePath): array
    {
        // Prevent timeout on large imports (400+ products)
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $rows = $this->readCsv($filePath);
        [$parents, $variations] = $this->split($rows);
        $counts = ['products' => 0, 'variants' => 0, 'images' => 0, 'errors' => []];

        foreach ($parents as $row) {
            try {
                $name     = trim($row['Name'] ?? '');
                $sku      = trim($row['SKU'] ?? '');
                $slug     = $this->uniqueSlug($sku ?: $name);
                $catPaths = $this->parseCategories($row['Categories'] ?? '');
                $category = $this->resolveFirstCategory($catPaths);

                $p = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name'            => $name,
                        'description'     => trim($row['Description'] ?? ''),
                        'short_description' => trim($row['Short description'] ?? ''),
                        'status'          => ($row['Published'] ?? '1') === '1'
                                             ? Product::STATUS_ACTIVE : 'draft',
                        'category_id'     => $category?->id,
                        'is_bestseller'   => false,
                    ]
                );
                $counts['products']++;

                $type = strtolower(trim($row['Type'] ?? 'simple'));

                if ($type === 'simple') {
                    $rawPrice = trim($row['Regular price'] ?? '');
                    $simplePrice = $rawPrice !== '' ? (float) $rawPrice : 0;

                    if ($simplePrice <= 0) {
                        $counts['errors'][] = "{$name}: Skipped — price is 0 or empty";
                    } else {
                        $rawSale = trim($row['Sale price'] ?? '');
                        ProductVariant::query()->updateOrCreate(
                            ['product_id' => $p->id, 'sku' => $sku ?: null],
                            [
                                'title'            => 'Default',
                                'price_retail'     => $simplePrice,
                                'compare_at_price' => ($rawSale !== '' && (float) $rawSale > 0) ? (float) $rawSale : null,
                                'stock_qty'        => max(0, (int) ($row['Stock'] ?? 0)),
                                'track_inventory'  => true,
                                'is_active'        => true,
                            ]
                        );
                        $counts['variants']++;
                    }
                } else {
                    // Variable — pick up child variations
                    $id  = trim($row['ID'] ?? '');
                    $children = array_filter($variations, fn ($v) => trim($v['Parent'] ?? '') === $id);
                    foreach ($children as $v) {
                        $attrParts = [];
                        for ($i = 1; $i <= 3; $i++) {
                            $val = trim($v["Attribute {$i} value(s)"] ?? '');
                            if ($val) {
                                $attrParts[] = $val;
                            }
                        }
                        $varRawPrice = trim($v['Regular price'] ?? '');
                        $varPrice = $varRawPrice !== '' ? (float) $varRawPrice : 0;
                        $varTitle = implode(' / ', $attrParts) ?: 'Variant';

                        if ($varPrice <= 0) {
                            $counts['errors'][] = "{$name}: Variant '{$varTitle}' skipped — price is 0 or empty";
                            continue;
                        }

                        $varRawSale = trim($v['Sale price'] ?? '');
                        ProductVariant::query()->updateOrCreate(
                            ['product_id' => $p->id, 'sku' => trim($v['SKU'] ?? '') ?: null],
                            [
                                'title'            => $varTitle,
                                'price_retail'     => $varPrice,
                                'compare_at_price' => ($varRawSale !== '' && (float) $varRawSale > 0) ? (float) $varRawSale : null,
                                'stock_qty'        => max(0, (int) ($v['Stock'] ?? 0)),
                                'track_inventory'  => true,
                                'is_active'        => true,
                            ]
                        );
                        $counts['variants']++;
                    }
                }

                // Images
                foreach (array_filter(explode(',', $row['Images'] ?? '')) as $imgUrl) {
                    $imgUrl = trim($imgUrl);
                    try {
                        $path = $this->images->fetch($imgUrl);
                        if ($path) {
                            ProductImage::query()->firstOrCreate(
                                ['product_id' => $p->id, 'path' => $path],
                                ['alt_text' => $name, 'sort_order' => 0]
                            );
                            $counts['images']++;
                        }
                    } catch (\Throwable) {
                        $counts['errors'][] = "Image failed: {$imgUrl}";
                    }
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = ($row['Name'] ?? '?').': '.$e->getMessage();
            }
        }

        return $counts;
    }

    /**
     * Import a chunk of products (offset-based).
     * Processes $limit parent products starting from $offset.
     */
    public function importChunk(string $filePath, int $offset, int $limit): array
    {
        $rows = $this->readCsv($filePath);
        [$parents, $variations] = $this->split($rows);
        $totalParents = count($parents);
        $counts = ['products' => 0, 'variants' => 0, 'images' => 0, 'errors' => [], 'total_parents' => $totalParents, 'processed_parents' => 0, 'done' => false];

        // Slice the parents for this chunk
        $chunk = array_slice($parents, $offset, $limit);
        $counts['processed_parents'] = count($chunk);

        if (empty($chunk) || $offset >= $totalParents) {
            $counts['done'] = true;
            return $counts;
        }

        foreach ($chunk as $row) {
            try {
                $name     = trim($row['Name'] ?? '');
                $sku      = trim($row['SKU'] ?? '');
                $slug     = $this->uniqueSlug($sku ?: $name);
                $catPaths = $this->parseCategories($row['Categories'] ?? '');
                $category = $this->resolveFirstCategory($catPaths);

                $p = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name'            => $name,
                        'description'     => trim($row['Description'] ?? ''),
                        'short_description' => trim($row['Short description'] ?? ''),
                        'status'          => ($row['Published'] ?? '1') === '1'
                                             ? Product::STATUS_ACTIVE : 'draft',
                        'category_id'     => $category?->id,
                        'is_bestseller'   => false,
                    ]
                );
                $counts['products']++;

                $type = strtolower(trim($row['Type'] ?? 'simple'));

                if ($type === 'simple') {
                    $rawPrice = trim($row['Regular price'] ?? '');
                    $simplePrice = $rawPrice !== '' ? (float) $rawPrice : 0;

                    if ($simplePrice <= 0) {
                        $counts['errors'][] = "{$name}: Skipped — price is 0 or empty";
                    } else {
                        $rawSale = trim($row['Sale price'] ?? '');
                        ProductVariant::query()->updateOrCreate(
                            ['product_id' => $p->id, 'sku' => $sku ?: null],
                            [
                                'title'            => 'Default',
                                'price_retail'     => $simplePrice,
                                'compare_at_price' => ($rawSale !== '' && (float) $rawSale > 0) ? (float) $rawSale : null,
                                'stock_qty'        => max(0, (int) ($row['Stock'] ?? 0)),
                                'track_inventory'  => true,
                                'is_active'        => true,
                            ]
                        );
                        $counts['variants']++;
                    }
                } else {
                    $id  = trim($row['ID'] ?? '');
                    $children = array_filter($variations, fn ($v) => trim($v['Parent'] ?? '') === $id);
                    foreach ($children as $v) {
                        $attrParts = [];
                        for ($i = 1; $i <= 3; $i++) {
                            $val = trim($v["Attribute {$i} value(s)"] ?? '');
                            if ($val) $attrParts[] = $val;
                        }
                        $varRawPrice = trim($v['Regular price'] ?? '');
                        $varPrice = $varRawPrice !== '' ? (float) $varRawPrice : 0;
                        $varTitle = implode(' / ', $attrParts) ?: 'Variant';

                        if ($varPrice <= 0) {
                            $counts['errors'][] = "{$name}: Variant '{$varTitle}' skipped — price is 0 or empty";
                            continue;
                        }

                        $varRawSale = trim($v['Sale price'] ?? '');
                        ProductVariant::query()->updateOrCreate(
                            ['product_id' => $p->id, 'sku' => trim($v['SKU'] ?? '') ?: null],
                            [
                                'title'            => $varTitle,
                                'price_retail'     => $varPrice,
                                'compare_at_price' => ($varRawSale !== '' && (float) $varRawSale > 0) ? (float) $varRawSale : null,
                                'stock_qty'        => max(0, (int) ($v['Stock'] ?? 0)),
                                'track_inventory'  => true,
                                'is_active'        => true,
                            ]
                        );
                        $counts['variants']++;
                    }
                }

                // Images
                foreach (array_filter(explode(',', $row['Images'] ?? '')) as $imgUrl) {
                    $imgUrl = trim($imgUrl);
                    try {
                        $path = $this->images->fetch($imgUrl);
                        if ($path) {
                            ProductImage::query()->firstOrCreate(
                                ['product_id' => $p->id, 'path' => $path],
                                ['alt_text' => $name, 'sort_order' => 0]
                            );
                            $counts['images']++;
                        }
                    } catch (\Throwable) {
                        $counts['errors'][] = "Image failed: {$imgUrl}";
                    }
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = ($row['Name'] ?? '?').': '.$e->getMessage();
            }
        }

        // Check if this was the last chunk
        if ($offset + $counts['processed_parents'] >= $totalParents) {
            $counts['done'] = true;
        }

        return $counts;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function readCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map('trim', $header ?? []);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $rows;
    }

    /** Split into parents and child variations */
    private function split(array $rows): array
    {
        $parents    = [];
        $variations = [];

        foreach ($rows as $row) {
            $type = strtolower(trim($row['Type'] ?? ''));
            if ($type === 'variation') {
                $variations[] = $row;
            } else {
                $parents[] = $row;
            }
        }

        return [$parents, $variations];
    }

    /** "Tops > T-Shirts, Bottoms" → ['Tops > T-Shirts', 'Bottoms'] */
    private function parseCategories(string $raw): array
    {
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    private function resolveFirstCategory(array $paths): ?Category
    {
        if (empty($paths)) {
            return null;
        }

        $parts = array_map('trim', explode('>', $paths[0]));
        $name  = end($parts);

        return Category::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $i    = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = Str::slug($base).'-'.$i++;
        }

        return $slug;
    }

    // ─── Customer & Order Import ──────────────────────────────────────────────

    /**
     * Auto-detect TSV vs CSV and read all rows.
     */
    private function readTsvOrCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');

        // Peek at first line to detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, "\t") ? "\t" : ',';

        $header = fgetcsv($handle, 0, $delimiter, '"', '\\');
        $header = array_map('trim', $header ?? []);

        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $rows;
    }

    // ─── Bot detection ────────────────────────────────────────────────────────

    private const SMS_GATEWAY_DOMAINS = [
        'vtext.com',
        'tmomail.net',
        'msg.telus.com',
        'pcs.rogers.com',
        'txt.att.net',
        'mms.att.net',
        'messaging.sprintpcs.com',
        'pm.sprint.com',
        'mymetropcs.com',
        'mmst5.tracfone.com',
    ];

    /**
     * Determine whether a user row is a bot / spam account.
     */
    private function isBot(array $row): bool
    {
        $email = strtolower(trim($row['user_email'] ?? ''));

        // Spam domain
        if (str_contains($email, 'nateforutah.com')) {
            return true;
        }

        // SMS-gateway email domain
        $domain = substr($email, strrpos($email, '@') + 1);
        foreach (self::SMS_GATEWAY_DOMAINS as $gateway) {
            if ($domain === $gateway || str_ends_with($domain, '.' . $gateway)) {
                return true;
            }
        }

        // Phone-number-only login with a carrier domain
        $login = trim($row['user_login'] ?? '');
        if (preg_match('/^\+?\d[\d\-]{6,}$/', $login)) {
            foreach (self::SMS_GATEWAY_DOMAINS as $gateway) {
                if ($domain === $gateway || str_ends_with($domain, '.' . $gateway)) {
                    return true;
                }
            }
        }

        return false;
    }

    // ─── Customer import ──────────────────────────────────────────────────────

    /**
     * Dry-run: preview what a customer import would do.
     */
    public function dryRunCustomers(string $filePath): array
    {
        $rows = $this->readTsvOrCsv($filePath);

        $isDetailed = isset($rows[0]['first_name']);
        $existingEmails = User::query()->pluck('email')->map(fn ($e) => strtolower($e))->flip();

        $total       = count($rows);
        $newCount    = 0;
        $skipCount   = 0;
        $botFiltered = 0;
        $addrCount   = 0;

        foreach ($rows as $row) {
            // Bot filter (users_all format)
            if (! $isDetailed && $this->isBot($row)) {
                $botFiltered++;
                continue;
            }

            $email = strtolower(trim($row['user_email'] ?? ''));
            if ($email === '' || $existingEmails->has($email)) {
                $skipCount++;
                continue;
            }

            $newCount++;

            if ($isDetailed) {
                $addrCount++;
            }
        }

        return [
            'total_in_file'      => $total,
            'new_customers'      => $newCount,
            'existing_skip'      => $skipCount,
            'bot_filtered'       => $botFiltered,
            'addresses_to_create' => $addrCount,
            'dry_run'            => true,
        ];
    }

    /**
     * Import customers from a WooCommerce CSV/TSV export.
     *
     * Supports two formats:
     *  - customers_detailed (TSV with first_name, last_name, phone, address…)
     *  - users_all          (CSV with user_login, display_name, roles)
     */
    public function importCustomers(string $filePath): array
    {
        $rows = $this->readTsvOrCsv($filePath);

        $isDetailed = isset($rows[0]['first_name']);

        $counts = [
            'customers'    => 0,
            'addresses'    => 0,
            'skipped'      => 0,
            'bot_filtered' => 0,
            'errors'       => [],
        ];

        foreach ($rows as $index => $row) {
            try {
                // ── Bot filter (users_all only) ──────────────────────
                if (! $isDetailed && $this->isBot($row)) {
                    $counts['bot_filtered']++;
                    continue;
                }

                $email = strtolower(trim($row['user_email'] ?? ''));
                if ($email === '') {
                    $counts['skipped']++;
                    continue;
                }

                // Skip existing
                if (User::query()->where('email', $email)->exists()) {
                    $counts['skipped']++;
                    continue;
                }

                if ($isDetailed) {
                    // ── Detailed-format import ───────────────────────
                    $firstName = rtrim(trim($row['first_name'] ?? ''), '.');
                    $lastName  = rtrim(trim($row['last_name'] ?? ''), '.');
                    $name      = trim($firstName . ' ' . $lastName);
                    if ($name === '') {
                        $name = explode('@', $email)[0];
                    }

                    $phone = trim($row['phone'] ?? '');
                    $phone = preg_replace('/[^\d+]/', '', $phone); // keep digits & leading +

                    $user = User::query()->create([
                        'name'                => $name,
                        'email'               => $email,
                        'phone'               => $phone ?: null,
                        'password'            => Hash::make(Str::random(32)),
                        'is_admin'            => false,
                        'role'                => 'customer',
                        'must_reset_password' => true,
                        'woo_customer_id'     => (int) ($row['ID'] ?? 0) ?: null,
                    ]);

                    // Override created_at if we have user_registered
                    $registered = trim($row['user_registered'] ?? '');
                    if ($registered !== '') {
                        $user->created_at = $registered;
                        $user->saveQuietly();
                    }

                    $counts['customers']++;

                    // ── Address ──────────────────────────────────────
                    $postalCode = trim($row['postcode'] ?? '');
                    if (strtoupper($postalCode) === 'NULL' || $postalCode === '') {
                        $postalCode = null;
                    }

                    $addressLine = trim($row['address'] ?? '');
                    $city        = trim($row['city'] ?? '');

                    if ($addressLine !== '' || $city !== '') {
                        Address::query()->create([
                            'user_id'       => $user->id,
                            'label'         => 'Home',
                            'name'          => $name,
                            'phone'         => $phone ?: null,
                            'address_line1' => $addressLine ?: '-',
                            'city'          => $city ?: '-',
                            'state'         => trim($row['state'] ?? '') ?: null,
                            'postal_code'   => $postalCode,
                            'country'       => trim($row['country'] ?? 'IN'),
                            'is_default'    => true,
                        ]);
                        $counts['addresses']++;
                    }
                } else {
                    // ── Users-all format import ──────────────────────
                    $name = rtrim(trim($row['display_name'] ?? ''), '.');
                    if ($name === '') {
                        $name = trim($row['user_login'] ?? '') ?: explode('@', $email)[0];
                    }

                    $user = User::query()->create([
                        'name'                => $name,
                        'email'               => $email,
                        'password'            => Hash::make(Str::random(32)),
                        'is_admin'            => false,
                        'role'                => 'customer',
                        'must_reset_password' => true,
                        'woo_customer_id'     => (int) ($row['ID'] ?? 0) ?: null,
                    ]);

                    $registered = trim($row['user_registered'] ?? '');
                    if ($registered !== '') {
                        $user->created_at = $registered;
                        $user->saveQuietly();
                    }

                    $counts['customers']++;
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = "Row {$index}: " . $e->getMessage();
            }
        }

        return $counts;
    }

    // ─── Order import ─────────────────────────────────────────────────────────

    /**
     * Dry-run: preview what an order import would do.
     */
    public function dryRunOrders(string $filePath): array
    {
        $rows = $this->readTsvOrCsv($filePath);

        $completed = 0;
        $cancelled = 0;
        $linked    = 0;
        $unlinked  = 0;

        foreach ($rows as $row) {
            $status = trim($row['status'] ?? '');
            if ($status === 'wc-completed') {
                $completed++;
            } else {
                $cancelled++;
            }

            $billingEmail = strtolower(trim($row['billing_email'] ?? ''));
            $customerId   = (int) ($row['customer_id'] ?? 0);

            if ($customerId > 0 && $billingEmail !== '' && User::query()->whereRaw('LOWER(email) = ?', [$billingEmail])->exists()) {
                $linked++;
            } else {
                $unlinked++;
            }
        }

        return [
            'total_orders' => count($rows),
            'completed'    => $completed,
            'cancelled'    => $cancelled,
            'linked'       => $linked,
            'unlinked'     => $unlinked,
            'dry_run'      => true,
        ];
    }

    /**
     * Import orders from a WooCommerce TSV export.
     */
    public function importOrders(string $filePath): array
    {
        $rows = $this->readTsvOrCsv($filePath);

        $counts = [
            'orders'   => 0,
            'linked'   => 0,
            'unlinked' => 0,
            'skipped'  => 0,
            'errors'   => [],
        ];

        foreach ($rows as $index => $row) {
            try {
                $originalId  = trim($row['id'] ?? '');
                $orderNumber = 'RUBY-' . $originalId;

                // Idempotent: skip if already imported
                if (Order::query()->where('order_number', $orderNumber)->exists()) {
                    $counts['skipped']++;
                    continue;
                }

                $billingEmail = strtolower(trim($row['billing_email'] ?? ''));
                $user         = null;

                if ($billingEmail !== '') {
                    $user = User::query()->whereRaw('LOWER(email) = ?', [$billingEmail])->first();
                }

                $isLinked = $user !== null;

                // Resolve address from user's default address
                $address = $isLinked
                    ? Address::query()->where('user_id', $user->id)->where('is_default', true)->first()
                    : null;

                $customerName = $isLinked
                    ? $user->name
                    : ($billingEmail !== '' ? explode('@', $billingEmail)[0] : 'Guest');

                $phone = $isLinked ? ($user->phone ?? '') : '';

                // Status mapping
                $wooStatus = trim($row['status'] ?? '');
                $orderStatus   = $wooStatus === 'wc-completed' ? 'delivered' : 'cancelled';
                $paymentStatus = $wooStatus === 'wc-completed' ? 'paid' : 'failed';

                $grandTotal = round((float) ($row['total_amount'] ?? 0), 2);

                Order::query()->create([
                    'order_number'   => $orderNumber,
                    'user_id'        => $user?->id,
                    'customer_name'  => $customerName,
                    'email'          => $billingEmail ?: null,
                    'phone'          => $phone ?: '',
                    'address_line1'  => $address->address_line1 ?? '-',
                    'address_line2'  => $address->address_line2 ?? null,
                    'city'           => $address->city ?? '-',
                    'state'          => $address->state ?? '-',
                    'postal_code'    => $address->postal_code ?? '000000',
                    'country'        => $address->country ?? 'IN',
                    'subtotal'       => $grandTotal,
                    'shipping_total' => 0,
                    'discount_total' => 0,
                    'tax_total'      => 0,
                    'grand_total'    => $grandTotal,
                    'currency'       => trim($row['currency'] ?? 'INR'),
                    'payment_method' => 'woocommerce_legacy',
                    'payment_status' => $paymentStatus,
                    'order_status'   => $orderStatus,
                    'placed_at'      => trim($row['date_created_gmt'] ?? '') ?: now(),
                ]);

                $counts['orders']++;
                $isLinked ? $counts['linked']++ : $counts['unlinked']++;
            } catch (\Throwable $e) {
                $counts['errors'][] = "Row {$index} (id={$row['id']}): " . $e->getMessage();
            }
        }

        return $counts;
    }
}
