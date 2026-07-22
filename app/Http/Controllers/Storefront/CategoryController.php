<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        // Collect this category + all child category IDs
        $categoryIds = collect([$category->id]);
        $childIds = $category->children()->where('is_active', true)->pluck('id');
        if ($childIds->isNotEmpty()) {
            $categoryIds = $categoryIds->merge($childIds);
        }

        $query = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Sort handling
        switch (request('sort')) {
            case 'price_asc':
                $query->orderBy(
                    \App\Models\ProductVariant::select('price_retail')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price_retail')
                        ->limit(1),
                    'asc'
                );
                break;
            case 'price_desc':
                $query->orderByDesc(
                    \App\Models\ProductVariant::select('price_retail')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price_retail')
                        ->limit(1)
                );
                break;
            case 'bestseller':
                $query->orderByDesc('is_bestseller')->latest();
                break;
            default: // newest
                $query->latest();
                break;
        }

        $products = $query->paginate(24)->appends(request()->query());

        // Transparent In-Place Search Fallback if 0 products are linked to the category
        if ($products->total() === 0) {
            $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', $category->name)));
            if (!empty($words)) {
                $fallbackQuery = Product::query()
                    ->where('status', Product::STATUS_ACTIVE)
                    ->withAvg(['reviews' => fn($q) => $q->where('is_approved', true)], 'rating')
                    ->withCount(['reviews' => fn($q) => $q->where('is_approved', true)])
                    ->with(['variants', 'images']);

                $fallbackQuery->where(function ($qBuilder) use ($words): void {
                    foreach ($words as $word) {
                        $wordLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $word).'%';
                        $qBuilder->where(function ($subBuilder) use ($wordLike): void {
                            $subBuilder->where('name', 'like', $wordLike)
                                ->orWhere('sku', 'like', $wordLike)
                                ->orWhere('short_description', 'like', $wordLike);
                        });
                    }
                });

                // Apply sorting to fallback query
                switch (request('sort')) {
                    case 'price_asc':
                        $fallbackQuery->orderBy(
                            \App\Models\ProductVariant::select('price_retail')
                                ->whereColumn('product_id', 'products.id')
                                ->where('is_active', true)
                                ->orderBy('price_retail')
                                ->limit(1),
                            'asc'
                        );
                        break;
                    case 'price_desc':
                        $fallbackQuery->orderByDesc(
                            \App\Models\ProductVariant::select('price_retail')
                                ->whereColumn('product_id', 'products.id')
                                ->where('is_active', true)
                                ->orderBy('price_retail')
                                ->limit(1)
                        );
                        break;
                    case 'bestseller':
                        $fallbackQuery->orderByDesc('is_bestseller')->latest();
                        break;
                    default:
                        $fallbackQuery->latest();
                        break;
                }

                $products = $fallbackQuery->paginate(24)->appends(request()->query());
            }
        }

        return view('storefront.category', compact('category', 'products'));
    }
}
