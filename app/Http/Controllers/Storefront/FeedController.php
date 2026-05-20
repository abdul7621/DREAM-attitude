<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function google(): Response
    {
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images', 'category'])
            ->orderBy('id')
            ->get();

        $store = e(config('commerce.name', config('app.name')));
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">', '<channel>', '<title>'.$store.'</title>', '<link>'.e(url('/')).'</link>', '<description>'.e($store).'</description>'];

        foreach ($products as $product) {
            $variant = $product->variants->where('is_active', true)->sortBy('price_retail')->first();
            if (! $variant) {
                continue;
            }
            $img = $product->primaryImage();
            $imageLink = $img ? $img->url() : '';
            if ($imageLink !== '' && ! str_starts_with($imageLink, 'http')) {
                $imageLink = url($imageLink);
            }
            $availability = 'out of stock';
            if (! $variant->track_inventory || $variant->stock_qty > 0) {
                $availability = 'in stock';
            }
            $lines[] = '<item>';
            $lines[] = '<g:id>'.e($variant->sku ?: 'p'.$product->id.'-v'.$variant->id).'</g:id>';
            $lines[] = '<g:title>'.e($product->name.' — '.$variant->title).'</g:title>';
            $lines[] = '<g:description>'.e(\Illuminate\Support\Str::limit(strip_tags((string) $product->description), 4900)).'</g:description>';
            $lines[] = '<g:link>'.e(route('product.show', $product, true)).'</g:link>';
            if ($imageLink !== '') {
                $lines[] = '<g:image_link>'.e($imageLink).'</g:image_link>';
            }
            $lines[] = '<g:condition>new</g:condition>';
            $lines[] = '<g:availability>'.e($availability).'</g:availability>';
            $lines[] = '<g:price>'.e(number_format((float) $variant->price_retail, 2, '.', '')).' '.e(config('commerce.currency', 'INR')).'</g:price>';
            if ($product->brand) {
                $lines[] = '<g:brand>'.e($product->brand).'</g:brand>';
            }
            if ($variant->barcode) {
                $lines[] = '<g:gtin>'.e($variant->barcode).'</g:gtin>';
            }
            $lines[] = '<g:mpn>'.e($variant->sku ?: 'SKU-'.$variant->id).'</g:mpn>';
            if ($product->category) {
                $lines[] = '<g:product_type>'.e($product->category->name).'</g:product_type>';
            }
            $lines[] = '</item>';
        }

        $lines[] = '</channel></rss>';

        return response(implode("\n", $lines), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Build category hierarchy string: "Parent > Child > Grandchild"
     */
    private function buildCategoryPath($category): string
    {
        if (! $category) {
            return '';
        }

        $path = [$category->name];
        $parent = $category->parent;

        // Walk up the parent chain (max 5 levels to prevent infinite loops)
        $depth = 0;
        while ($parent && $depth < 5) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
            $depth++;
        }

        return implode(' > ', $path);
    }

    public function facebook(): Response
    {
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images', 'category.parent'])
            ->orderBy('id')
            ->get();

        $currency = config('commerce.currency', 'INR');
        $storeName = e(config('commerce.name', config('app.name')));

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">',
            '<channel>',
            '<title>'.$storeName.'</title>',
            '<link>'.e(url('/')).'</link>',
            '<description>Product catalog for '.$storeName.'</description>',
        ];

        foreach ($products as $product) {
            $activeVariants = $product->variants->where('is_active', true)->sortBy('price_retail');

            if ($activeVariants->isEmpty()) {
                continue;
            }

            // Category hierarchy (e.g. "Hair Care > Shampoo")
            $categoryPath = $this->buildCategoryPath($product->category);

            // Primary image
            $primaryImg = $product->primaryImage();
            $primaryImageLink = $primaryImg ? $primaryImg->url() : '';
            if ($primaryImageLink !== '' && ! str_starts_with($primaryImageLink, 'http')) {
                $primaryImageLink = url($primaryImageLink);
            }

            // Additional images (up to 10 as per Facebook spec)
            $additionalImages = [];
            foreach ($product->images as $img) {
                if ($primaryImg && $img->id === $primaryImg->id) {
                    continue;
                }
                $imgUrl = $img->url();
                if (! str_starts_with($imgUrl, 'http')) {
                    $imgUrl = url($imgUrl);
                }
                $additionalImages[] = $imgUrl;
                if (count($additionalImages) >= 10) {
                    break;
                }
            }

            // Description — prefer short_description, fallback to full description
            $description = strip_tags((string) ($product->short_description ?: $product->description));
            $description = \Illuminate\Support\Str::limit($description, 9900);

            // Product link
            $productLink = route('product.show', $product, true);

            // Brand
            $brand = $product->brand ?: config('app.name');

            // Emit one <item> per active variant (Facebook groups them via item_group_id)
            foreach ($activeVariants as $variant) {
                $availability = (! $variant->track_inventory || $variant->stock_qty > 0)
                    ? 'in stock'
                    : 'out of stock';

                $retailPrice = (float) $variant->price_retail;
                $comparePrice = (float) $variant->compare_at_price;

                // Facebook pricing: if compare_at_price exists and is higher, that's the "price" (MRP)
                // and price_retail becomes the "sale_price"
                $hasDiscount = $comparePrice > 0 && $comparePrice > $retailPrice;

                $lines[] = '<item>';

                // — Required fields —
                $lines[] = '<g:id>'.e($variant->sku ?: 'p'.$product->id.'-v'.$variant->id).'</g:id>';
                $lines[] = '<g:title>'.e($product->name.($variant->title !== 'Default' ? ' — '.$variant->title : '')).'</g:title>';
                $lines[] = '<g:description>'.e($description).'</g:description>';
                $lines[] = '<g:link>'.e($productLink).'</g:link>';
                $lines[] = '<g:condition>new</g:condition>';
                $lines[] = '<g:availability>'.e($availability).'</g:availability>';
                $lines[] = '<g:brand>'.e($brand).'</g:brand>';

                // — Pricing —
                if ($hasDiscount) {
                    // MRP as price, discounted as sale_price
                    $lines[] = '<g:price>'.number_format($comparePrice, 2, '.', '').' '.$currency.'</g:price>';
                    $lines[] = '<g:sale_price>'.number_format($retailPrice, 2, '.', '').' '.$currency.'</g:sale_price>';
                } else {
                    $lines[] = '<g:price>'.number_format($retailPrice, 2, '.', '').' '.$currency.'</g:price>';
                }

                // — Images —
                if ($primaryImageLink !== '') {
                    $lines[] = '<g:image_link>'.e($primaryImageLink).'</g:image_link>';
                }
                foreach ($additionalImages as $addImg) {
                    $lines[] = '<g:additional_image_link>'.e($addImg).'</g:additional_image_link>';
                }

                // — Category (product_type) —
                if ($categoryPath !== '') {
                    $lines[] = '<g:product_type>'.e($categoryPath).'</g:product_type>';
                }

                // — Variant grouping (all variants of same product grouped together) —
                if ($activeVariants->count() > 1) {
                    $lines[] = '<g:item_group_id>'.e('DA-'.$product->id).'</g:item_group_id>';
                }

                // — Variant attributes (size, color etc.) —
                if ($variant->option1) {
                    $lines[] = '<g:size>'.e($variant->option1).'</g:size>';
                }
                if ($variant->option2) {
                    $lines[] = '<g:color>'.e($variant->option2).'</g:color>';
                }

                // — Identifiers —
                if ($variant->sku) {
                    $lines[] = '<g:mpn>'.e($variant->sku).'</g:mpn>';
                }
                if ($variant->barcode) {
                    $lines[] = '<g:gtin>'.e($variant->barcode).'</g:gtin>';
                }

                // — Inventory quantity —
                if ($variant->track_inventory) {
                    $lines[] = '<g:quantity_to_sell_on_facebook>'.max(0, (int) $variant->stock_qty).'</g:quantity_to_sell_on_facebook>';
                }

                $lines[] = '</item>';
            }
        }

        $lines[] = '</channel></rss>';

        return response(implode("\n", $lines), 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
