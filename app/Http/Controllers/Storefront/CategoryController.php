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

        $query = Product::query()
            ->where('category_id', $category->id)
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

        return view('storefront.category', compact('category', 'products'));
    }
}
