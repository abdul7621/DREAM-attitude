<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ImportJob;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

/**
 * Shopify Products CSV Importer
 *
 * Shopify CSV is row-per-variant; rows sharing same Handle = one product.
 */
class ShopifyImporter
{
    public function __construct(private readonly ImageFetchPipeline $images) {}

    /** Dry run — returns stats only, writes nothing */
    public function dryRun(string $filePath): array
    {
        $rows  = $this->readCsv($filePath);
        $stats = $this->aggregate($rows);

        return [
            'products'    => count($stats['products']),
            'variants'    => $stats['totalVariants'],
            'categories'  => count($stats['categories']),
            'images'      => $stats['totalImages'],
            'dry_run'     => true,
            'sample_rows' => array_slice(array_values($stats['products']), 0, 3, true),
        ];
    }

    /** Real import — writes catalog, queues image downloads */
    public function import(string $filePath): array
    {
        $rows   = $this->readCsv($filePath);
        $data   = $this->aggregate($rows);
        $counts = ['products' => 0, 'variants' => 0, 'images' => 0, 'errors' => []];

        foreach ($data['products'] as $handle => $product) {
            try {
                $category = $this->resolveCategory($product['category']);

                $slug = Str::slug($handle);
                $existing = Product::withTrashed()->where('slug', $slug)->first();

                $bodyHtml = $this->cleanHtml($product['body']);

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore(); // Restore if soft deleted
                    }
                    $existing->update([
                        'name'        => $product['title'],
                        'description' => $bodyHtml,
                        'status'      => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                        'category_id' => $category?->id,
                    ]);
                    $p = $existing;
                } else {
                    $p = Product::query()->create([
                        'name'          => $product['title'],
                        'slug'          => $this->uniqueSlug($handle),
                        'description'   => $bodyHtml,
                        'status'        => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                        'category_id'   => $category?->id,
                        'is_bestseller' => false,
                    ]);
                }

                $counts['products']++;

                // Pre-map variant images so we don't duplicate downloads
                $variantImageMap = [];
                foreach ($product['variants'] as $v) {
                    if (empty($v['price']) || $v['price'] <= 0) {
                        $counts['errors'][] = [
                            'message' => "Product {$handle}: Variant '{$v['title']}' skipped — price is 0 or empty",
                            'raw'     => $product['raw_rows']
                        ];
                        continue;
                    }

                    $testSku = $v['sku'];
                    
                    // [STEP 4] SKU SYSTEM (FINAL FIX) - Global Uniqueness Check
                    $conflict = ProductVariant::query()
                        ->where('sku', $testSku)
                        ->where('product_id', '!=', $p->id)
                        ->exists();

                    if ($conflict) {
                        $optSlug = Str::slug($v['title'] ?: 'default');
                        $baseConf = $handle . '-' . $optSlug;
                        $idx = 1;
                        $testSku = $baseConf . '-' . $idx;
                        while(ProductVariant::query()->where('sku', $testSku)->where('product_id', '!=', $p->id)->exists()) {
                            $testSku = $baseConf . '-' . (++$idx);
                        }
                    }

                    $variant = ProductVariant::query()->updateOrCreate(
                        ['product_id' => $p->id, 'sku' => $testSku],
                        [
                            'title'            => $v['title'],
                            'price_retail'     => $v['price'],
                            'compare_at_price' => $v['compare_at'] ?: null,
                            'weight_grams'     => $v['grams'],
                            'stock_qty'        => max(0, (int) $v['stock']),
                            'track_inventory'  => $v['track'] ?? true,
                            'is_active'        => true,
                        ]
                    );
                    $counts['variants']++;

                    if ($v['image']) {
                        $variantImageMap[$v['image']] = $variant->id;
                    }
                }

                $expectedCount = count($product['variants']);
                $actualCount = $p->variants()->count();
                if ($actualCount !== $expectedCount) {
                    $counts['errors'][] = [
                        'message' => "Warning: Product {$handle} variant count mismatch. Expected {$expectedCount}, found {$actualCount}.",
                        'raw'     => $product['raw_rows'],
                    ];
                }

                foreach ($product['images'] as $imgUrl) {
                    if (! $imgUrl) {
                        continue;
                    }
                    try {
                        $path = $this->images->fetch($imgUrl);
                        if ($path) {
                            $varId = $variantImageMap[$imgUrl] ?? null;
                            ProductImage::query()->firstOrCreate(
                                ['product_id' => $p->id, 'path' => $path],
                                ['alt_text' => $product['title'], 'sort_order' => 0, 'variant_id' => $varId]
                            );
                            $counts['images']++;
                        }
                    } catch (\Throwable $e) {
                        $counts['errors'][] = "Image failed ({$imgUrl}): ".$e->getMessage();
                    }
                }
            } catch (\Throwable $e) {
                // Log the exact error along with the full row raw data context if possible
                $counts['errors'][] = [
                    'message' => "Product {$handle}: ".$e->getMessage(),
                    'raw'     => $product['raw_rows'],
                ];
            }
        }

        return $counts;
    }

    /**
     * Import a chunk of products (offset-based).
     * Processes $limit products starting from $offset in the aggregated handles list.
     */
    public function importChunk(string $filePath, int $offset, int $limit): array
    {
        $rows   = $this->readCsv($filePath);
        $data   = $this->aggregate($rows);
        $allHandles = array_keys($data['products']);
        $totalParents = count($allHandles);

        $counts = ['products' => 0, 'variants' => 0, 'images' => 0, 'errors' => [], 'total_parents' => $totalParents, 'processed_parents' => 0, 'done' => false];

        if ($offset >= $totalParents) {
            $counts['done'] = true;
            return $counts;
        }

        $chunkHandles = array_slice($allHandles, $offset, $limit);
        $counts['processed_parents'] = count($chunkHandles);

        foreach ($chunkHandles as $handle) {
            $product = $data['products'][$handle];
            try {
                $category = $this->resolveCategory($product['category']);
                $slug = Str::slug($handle);
                $existing = Product::withTrashed()->where('slug', $slug)->first();
                $bodyHtml = $this->cleanHtml($product['body']);

                if ($existing) {
                    if ($existing->trashed()) $existing->restore();
                    $existing->update([
                        'name'        => $product['title'],
                        'description' => $bodyHtml,
                        'status'      => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                        'category_id' => $category?->id,
                    ]);
                    $p = $existing;
                } else {
                    $p = Product::query()->create([
                        'name'          => $product['title'],
                        'slug'          => $this->uniqueSlug($handle),
                        'description'   => $bodyHtml,
                        'status'        => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                        'category_id'   => $category?->id,
                        'is_bestseller' => false,
                    ]);
                }
                $counts['products']++;

                $variantImageMap = [];
                foreach ($product['variants'] as $v) {
                    if (empty($v['price']) || $v['price'] <= 0) {
                        $counts['errors'][] = "Product {$handle}: Variant '{$v['title']}' skipped — price is 0 or empty";
                        continue;
                    }

                    $testSku = $v['sku'];
                    $conflict = ProductVariant::query()->where('sku', $testSku)->where('product_id', '!=', $p->id)->exists();
                    if ($conflict) {
                        $optSlug = Str::slug($v['title'] ?: 'default');
                        $baseConf = $handle . '-' . $optSlug;
                        $idx = 1;
                        $testSku = $baseConf . '-' . $idx;
                        while(ProductVariant::query()->where('sku', $testSku)->where('product_id', '!=', $p->id)->exists()) {
                            $testSku = $baseConf . '-' . (++$idx);
                        }
                    }

                    $variant = ProductVariant::query()->updateOrCreate(
                        ['product_id' => $p->id, 'sku' => $testSku],
                        [
                            'title'            => $v['title'],
                            'price_retail'     => $v['price'],
                            'compare_at_price' => $v['compare_at'] ?: null,
                            'weight_grams'     => $v['grams'],
                            'stock_qty'        => max(0, (int) $v['stock']),
                            'track_inventory'  => $v['track'] ?? true,
                            'is_active'        => true,
                        ]
                    );
                    $counts['variants']++;

                    if ($v['image']) {
                        $variantImageMap[$v['image']] = $variant->id;
                    }
                }

                foreach ($product['images'] as $imgUrl) {
                    if (!$imgUrl) continue;
                    try {
                        $path = $this->images->fetch($imgUrl);
                        if ($path) {
                            $varId = $variantImageMap[$imgUrl] ?? null;
                            ProductImage::query()->firstOrCreate(
                                ['product_id' => $p->id, 'path' => $path],
                                ['alt_text' => $product['title'], 'sort_order' => 0, 'variant_id' => $varId]
                            );
                            $counts['images']++;
                        }
                    } catch (\Throwable $e) {
                        $counts['errors'][] = "Image failed ({$imgUrl}): ".$e->getMessage();
                    }
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = "Product {$handle}: ".$e->getMessage();
            }
        }

        if ($offset + $counts['processed_parents'] >= $totalParents) {
            $counts['done'] = true;
        }

        return $counts;
    }

    // ─── Customer Import ──────────────────────────────────────────────────────

    /** Dry run for customer CSV */
    public function dryRunCustomers(string $filePath): array
    {
        $rows = $this->readCsv($filePath);

        return [
            'customers' => count($rows),
            'dry_run'   => true,
            'sample_rows' => array_map(fn($r) => [
                'name'  => trim(($r['First Name'] ?? '') . ' ' . ($r['Last Name'] ?? '')),
                'email' => $r['Email'] ?? '',
                'phone' => $r['Phone'] ?? '',
            ], array_slice($rows, 0, 5)),
        ];
    }

    /** Import customers from Shopify CSV */
    public function importCustomers(string $filePath): array
    {
        $rows   = $this->readCsv($filePath);
        $counts = ['customers' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            try {
                $email = strtolower(trim($row['Email'] ?? ''));
                $rawPhone = trim($row['Phone'] ?? '');
                
                $phone = null;
                if ($rawPhone !== '') {
                    $phone = preg_replace('/[^\d+]/', '', $rawPhone);
                }
                
                $name  = trim(($row['First Name'] ?? '') . ' ' . ($row['Last Name'] ?? ''));

                if (! $email && ! $phone && ! $name) {
                    $counts['skipped']++;
                    continue;
                }

                // Identity System - Primary identifier is email, fallback is phone
                $user = null;
                if ($email) {
                    $user = \App\Models\User::withTrashed()->where('email', $email)->first();
                }

                if (! $user && $phone) {
                    $user = \App\Models\User::withTrashed()->where('phone', $phone)->first();
                }

                if (! $user) {
                    // Generate a unique placeholder email if none provided
                    $safeEmail = $email ?: 'imported_' . time() . '_' . $index . '_' . mt_rand(100,999) . '@placeholder.local';

                    try {
                        $user = \App\Models\User::create([
                            'name'     => $name ?: 'Imported Customer',
                            'email'    => $safeEmail,
                            'phone'    => $phone,
                            'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                            'is_admin' => false,
                        ]);
                        $counts['customers']++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Fallback completely if any obscure duplicate constraint hits (e.g. race conditions)
                        if ($e->errorInfo[1] === 1062) {
                            $counts['skipped']++;
                            continue;
                        }
                        throw $e;
                    }
                } else {
                    // Update missing fields on existing user
                    if ($user->trashed()) {
                        $user->restore();
                    }
                    $updates = [];
                    if ($phone && ! $user->phone) $updates['phone'] = $phone;
                    if ($email && str_contains($user->email, '@placeholder.local')) $updates['email'] = $email;
                    if ($name && $user->name === 'Imported Customer') $updates['name'] = $name;
                    if (! empty($updates)) $user->update($updates);
                    $counts['skipped']++;
                }

                // Create address if data exists
                $addr1 = trim($row['Address1'] ?? '');
                if ($addr1) {
                    \App\Models\Address::firstOrCreate(
                        ['user_id' => $user->id, 'address_line1' => $addr1],
                        [
                            'name'          => $name,
                            'phone'         => $phone,
                            'address_line2' => trim($row['Address2'] ?? ''),
                            'city'          => trim($row['City'] ?? ''),
                            'state'         => trim($row['Province'] ?? ''),
                            'postal_code'   => trim($row['Zip'] ?? ''),
                            'country'       => trim($row['Country Code'] ?? 'IN'),
                            'label'         => 'Imported',
                            'is_default'    => true,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = ($row['Email'] ?? $row['Phone'] ?? 'Row '.($index+1)) . ': ' . $e->getMessage();
            }
        }

        return $counts;
    }

    // ─── Order Import ────────────────────────────────────────────────────────

    /** Dry run for order CSV */
    public function dryRunOrders(string $filePath): array
    {
        $rows = $this->readCsv($filePath);

        $orderNumbers = [];
        foreach ($rows as $r) {
            $num = trim($r['Name'] ?? $r['Order Number'] ?? '');
            if ($num) {
                $orderNumbers[$num] = true;
            }
        }

        return [
            'orders'   => count($orderNumbers),
            'line_items' => count($rows),
            'dry_run'  => true,
        ];
    }

    /**
     * Import orders from Shopify CSV.
     *
     * CRITICAL: These are historical imports.
     * - NO OrderPlaced event
     * - NO inventory deduction
     * - NO Meta CAPI
     * - NO notifications
     */
    public function importOrders(string $filePath): array
    {
        $rows   = $this->readCsv($filePath);
        $counts = ['orders' => 0, 'line_items' => 0, 'errors' => []];

        // Group rows by order number (Shopify has one row per line item)
        $grouped = [];
        foreach ($rows as $row) {
            $num = trim($row['Name'] ?? $row['Order Number'] ?? '');
            if (! $num) continue;
            $grouped[$num][] = $row;
        }

        foreach ($grouped as $orderNum => $items) {
            try {
                $first = $items[0];

                // Skip if order already exists
                if (\App\Models\Order::where('order_number', $orderNum)->exists()) {
                    continue;
                }

                // Find customer by email
                $email  = trim($first['Email'] ?? '');
                $user   = $email ? \App\Models\User::where('email', $email)->first() : null;

                // Calculate totals from line items
                $subtotal = 0;
                foreach ($items as $item) {
                    $qty   = max(1, (int) ($item['Lineitem quantity'] ?? 1));
                    $price = (float) ($item['Lineitem price'] ?? 0);
                    $subtotal += $qty * $price;
                }

                $shipping  = (float) ($first['Shipping'] ?? 0);
                $discount  = abs((float) ($first['Discount Amount'] ?? 0));
                $tax       = (float) ($first['Taxes'] ?? $first['Tax 1 Value'] ?? 0);
                $grand     = $subtotal + $shipping + $tax - $discount;

                // Map Shopify financial status to our payment status
                $financialStatus = strtolower(trim($first['Financial Status'] ?? 'paid'));
                $paymentStatus = match($financialStatus) {
                    'paid', 'partially_paid' => 'paid',
                    'refunded', 'partially_refunded' => 'refunded',
                    default => 'paid',
                };

                // Map Shopify fulfillment status to our order status
                $fulfillment = strtolower(trim($first['Fulfillment Status'] ?? 'fulfilled'));
                $orderStatus = match($fulfillment) {
                    'fulfilled' => 'delivered',
                    'shipped'   => 'shipped',
                    'unfulfilled', '' => 'placed',
                    default => 'delivered',
                };

                // Parse order date
                $placedAt = null;
                $dateStr = $first['Created at'] ?? $first['Date'] ?? null;
                if ($dateStr) {
                    try { $placedAt = \Carbon\Carbon::parse($dateStr); } catch (\Throwable) {}
                }

                // Create order SILENTLY — no events, no inventory deduction
                $order = \App\Models\Order::create([
                    'order_number'    => $orderNum,
                    'user_id'         => $user?->id,
                    'customer_name'   => trim(($first['Shipping Name'] ?? $first['Billing Name'] ?? 'Imported Customer')),
                    'email'           => $email ?: null,
                    'phone'           => trim($first['Shipping Phone'] ?? $first['Phone'] ?? ''),
                    'address_line1'   => trim($first['Shipping Address1'] ?? $first['Shipping Street'] ?? ''),
                    'address_line2'   => trim($first['Shipping Address2'] ?? ''),
                    'city'            => trim($first['Shipping City'] ?? ''),
                    'state'           => trim($first['Shipping Province'] ?? ''),
                    'postal_code'     => trim($first['Shipping Zip'] ?? ''),
                    'country'         => trim($first['Shipping Country'] ?? 'IN'),
                    'subtotal'        => round($subtotal, 2),
                    'shipping_total'  => round($shipping, 2),
                    'discount_total'  => round($discount, 2),
                    'tax_total'       => round($tax, 2),
                    'grand_total'     => round(max(0, $grand), 2),
                    'currency'        => trim($first['Currency'] ?? 'INR'),
                    'payment_method'  => strtolower(trim($first['Payment Method'] ?? 'imported')),
                    'payment_status'  => $paymentStatus,
                    'order_status'    => $orderStatus,
                    'notes'           => 'Historical import from Shopify',
                    'placed_at'       => $placedAt ?? now(),
                ]);

                $counts['orders']++;

                // Create line items — NO inventory deduction
                foreach ($items as $item) {
                    $productName = trim($item['Lineitem name'] ?? '');
                    $sku         = trim($item['Lineitem sku'] ?? '');
                    $qty         = max(1, (int) ($item['Lineitem quantity'] ?? 1));
                    $unitPrice   = (float) ($item['Lineitem price'] ?? 0);

                    // Try to match to existing product/variant by SKU
                    $variant = $sku
                        ? \App\Models\ProductVariant::where('sku', $sku)->first()
                        : null;

                    \App\Models\OrderItem::create([
                        'order_id'               => $order->id,
                        'product_id'             => $variant?->product_id,
                        'product_variant_id'     => $variant?->id,
                        'product_name_snapshot'  => $productName,
                        'variant_title_snapshot' => $variant?->title ?? 'Imported',
                        'sku_snapshot'           => $sku,
                        'qty'                    => $qty,
                        'unit_price'             => $unitPrice,
                        'line_total'             => round($qty * $unitPrice, 2),
                    ]);

                    $counts['line_items']++;
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = "Order {$orderNum}: " . $e->getMessage();
            }
        }

        return $counts;
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

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

    private function aggregate(array $rows): array
    {
        $products       = [];
        $categories     = [];
        $totalVariants  = 0;
        $totalImages    = 0;

        // Per-handle carry-forward state for Shopify CSV quirks:
        // Subsequent variant rows may leave price/compare-at empty.
        $lastKnownPrice     = [];
        $lastKnownCompareAt = [];
        $lastKnownGrams     = [];

        foreach ($rows as $row) {
            $handle = trim($row['Handle'] ?? '');
            if (! $handle) {
                continue;
            }

            if (! isset($products[$handle])) {
                $cat = trim($row['Type'] ?? '');
                if ($cat) {
                    $categories[$cat] = true;
                }
                $products[$handle] = [
                    'title'     => trim($row['Title'] ?? $handle),
                    'body'      => trim($row['Body (HTML)'] ?? ''),
                    'published' => strtolower(trim($row['Published'] ?? 'true')) !== 'false',
                    'category'  => $cat,
                    'variants'  => [],
                    'images'    => [],
                    'raw_rows'  => [],
                ];
                $lastKnownPrice[$handle]     = 0;
                $lastKnownCompareAt[$handle] = null;
                $lastKnownGrams[$handle]     = 0;
            }

            $products[$handle]['raw_rows'][] = $row;

            $imgSrc = trim($row['Image Src'] ?? '');
            if ($imgSrc && ! in_array($imgSrc, $products[$handle]['images'], true)) {
                $products[$handle]['images'][] = $imgSrc;
                $totalImages++;
            }

            // RULE 1: Image mapping is already handled safely above (images preserved)
            
            // RULE 2: VARIANT MUST HAVE PRICE TO BE CREATED
            $rawPrice = trim($row['Variant Price'] ?? '');

            // If Price is empty, this row cannot form a valid variant (even if it has an SKU). 
            // Skip the rest of the parsing logic for this row.
            if ($rawPrice === '') {
                continue;
            }

            $varTitle = collect([
                $row['Option1 Value'] ?? '',
                $row['Option2 Value'] ?? '',
                $row['Option3 Value'] ?? '',
            ])->filter()->implode(' / ');

            $rawCompareAt = trim($row['Variant Compare At Price'] ?? '');
            $rawGrams     = trim($row['Variant Grams'] ?? '');

            if ($rawPrice !== '') {
                $lastKnownPrice[$handle] = (float) $rawPrice;
            }
            if ($rawCompareAt !== '') {
                $lastKnownCompareAt[$handle] = ((float) $rawCompareAt) ?: null;
            }
            if ($rawGrams !== '') {
                $lastKnownGrams[$handle] = (int) $rawGrams;
            }

            $policy = strtolower(trim($row['Variant Inventory Policy'] ?? ''));
            $track = $policy !== 'continue';

            // Deterministic SKU computation
            $skuRaw = trim($row['Variant SKU'] ?? '');
            $skuRaw = str_replace(["'", '"'], '', $skuRaw);
            $skuRaw = trim($skuRaw);

            if (!$skuRaw) {
                $optVal = Str::slug($varTitle ?: 'default');
                // Use handle as base + options slug
                $skuRaw = $handle . '-' . ($optVal ? $optVal : count($products[$handle]['variants']));
            }

            $products[$handle]['variants'][] = [
                'title'      => $varTitle ?: 'Default',
                'sku'        => $skuRaw,
                'price'      => $lastKnownPrice[$handle],
                'compare_at' => $lastKnownCompareAt[$handle],
                'grams'      => $lastKnownGrams[$handle],
                'stock'      => (int) ($row['Variant Inventory Qty'] ?? 0),
                'track'      => $track,
                'image'      => trim($row['Variant Image'] ?? ''),
            ];
            $totalVariants++;
        }

        return compact('products', 'categories', 'totalVariants', 'totalImages');
    }

    private function cleanHtml(string $html): string
    {
        if (!$html) return '';

        // Safe HTML Whitelist (No divs, spans, tables unless required)
        $allowedTags = '<p><ul><li><br><strong><b><i><em><img>';
        $cleaned = strip_tags($html, $allowedTags);

        // Strip inline styles, classes, and data-* attributes
        $cleaned = preg_replace('/(style|class|data-[a-z0-9\-]+)="[^"]*"/i', '', $cleaned);

        // Clean up empty tags and multiple spaces if needed
        return trim($cleaned);
    }

    private function resolveCategory(string $name): ?Category
    {
        if (! $name) {
            return null;
        }

        $parts = array_map('trim', explode('>', $name));
        $parent = null;
        $category = null;

        foreach ($parts as $part) {
            if (!$part) continue;

            $slug = $parent ? $parent->slug . '-' . Str::slug($part) : Str::slug($part);
            
            $category = Category::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $part, 
                    'is_active' => true,
                    'parent_id' => $parent ? $parent->id : null,
                ]
            );
            $parent = $category;
        }

        return $category;
    }

    private function uniqueSlug(string $handle): string
    {
        $base  = Str::slug($handle);
        $slug  = $base;
        $i     = 1;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
