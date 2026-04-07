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

                $existing = Product::query()->where('slug', Str::slug($handle))->first();

                $p = $existing ?? Product::query()->create([
                    'name'        => $product['title'],
                    'slug'        => $this->uniqueSlug($handle),
                    'description' => $product['body'],
                    'status'      => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                    'category_id' => $category?->id,
                    'is_bestseller' => false,
                ]);

                if ($existing) {
                    $existing->update([
                        'name'        => $product['title'],
                        'description' => $product['body'],
                        'status'      => $product['published'] ? Product::STATUS_ACTIVE : 'draft',
                        'category_id' => $category?->id,
                    ]);
                    $p = $existing;
                }

                $counts['products']++;

                foreach ($product['variants'] as $v) {
                    $variant = ProductVariant::query()->updateOrCreate(
                        ['product_id' => $p->id, 'sku' => $v['sku'] ?: null],
                        [
                            'title'            => $v['title'],
                            'sku'              => $v['sku'] ?: null,
                            'price_retail'     => $v['price'],
                            'compare_at_price' => $v['compare_at'] ?: null,
                            'weight_grams'     => $v['grams'],
                            'stock_qty'        => max(0, (int) $v['stock']),
                            'track_inventory'  => true,
                            'is_active'        => true,
                        ]
                    );
                    $counts['variants']++;
                }

                foreach ($product['images'] as $imgUrl) {
                    if (! $imgUrl) {
                        continue;
                    }
                    try {
                        $path = $this->images->fetch($imgUrl);
                        if ($path) {
                            ProductImage::query()->firstOrCreate(
                                ['product_id' => $p->id, 'path' => $path],
                                ['alt_text' => $product['title'], 'sort_order' => 0]
                            );
                            $counts['images']++;
                        }
                    } catch (\Throwable) {
                        $counts['errors'][] = "Image failed: {$imgUrl}";
                    }
                }
            } catch (\Throwable $e) {
                $counts['errors'][] = "Product {$handle}: ".$e->getMessage();
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
                ];
            }

            $imgSrc = trim($row['Image Src'] ?? '');
            if ($imgSrc && ! in_array($imgSrc, $products[$handle]['images'], true)) {
                $products[$handle]['images'][] = $imgSrc;
                $totalImages++;
            }

            $varTitle = collect([
                $row['Option1 Value'] ?? '',
                $row['Option2 Value'] ?? '',
                $row['Option3 Value'] ?? '',
            ])->filter()->implode(' / ');

            $products[$handle]['variants'][] = [
                'title'      => $varTitle ?: 'Default',
                'sku'        => trim($row['Variant SKU'] ?? ''),
                'price'      => (float) ($row['Variant Price'] ?? 0),
                'compare_at' => (float) ($row['Variant Compare At Price'] ?? 0) ?: null,
                'grams'      => (int) ($row['Variant Grams'] ?? 0),
                'stock'      => (int) ($row['Variant Inventory Qty'] ?? 0),
            ];
            $totalVariants++;
        }

        return compact('products', 'categories', 'totalVariants', 'totalImages');
    }

    private function resolveCategory(string $name): ?Category
    {
        if (! $name) {
            return null;
        }

        return Category::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'is_active' => true]
        );
    }

    private function uniqueSlug(string $handle): string
    {
        $base  = Str::slug($handle);
        $slug  = $base;
        $i     = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
