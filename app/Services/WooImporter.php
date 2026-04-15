<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
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
}
