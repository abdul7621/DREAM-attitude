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

    public function facebook(): Response
    {
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images'])
            ->orderBy('id')
            ->get();

        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">', '<channel>', '<title>'.e(config('commerce.name', config('app.name'))).'</title>'];

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
            $availability = (! $variant->track_inventory || $variant->stock_qty > 0) ? 'in stock' : 'out of stock';

            $lines[] = '<item>';
            $lines[] = '<g:id>'.e($variant->sku ?: 'p'.$product->id.'-v'.$variant->id).'</g:id>';
            $lines[] = '<g:title>'.e($product->name.' — '.$variant->title).'</g:title>';
            $lines[] = '<g:description>'.e(\Illuminate\Support\Str::limit(strip_tags((string) $product->short_description ?: $product->description), 9900)).'</g:description>';
            $lines[] = '<g:link>'.e(route('product.show', $product, true)).'</g:link>';
            if ($imageLink !== '') {
                $lines[] = '<g:image_link>'.e($imageLink).'</g:image_link>';
            }
            $lines[] = '<g:condition>new</g:condition>';
            $lines[] = '<g:availability>'.e($availability).'</g:availability>';
            $lines[] = '<g:price>'.e(number_format((float) $variant->price_retail, 2, '.', '')).' '.e(config('commerce.currency', 'INR')).'</g:price>';
            $lines[] = '<g:brand>'.e($product->brand ?: config('app.name')).'</g:brand>';
            $lines[] = '</item>';
        }

        $lines[] = '</channel></rss>';

        return response(implode("\n", $lines), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
