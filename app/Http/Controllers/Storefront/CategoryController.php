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

        $products = Product::query()
            ->where('category_id', $category->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images'])
            ->orderByDesc('is_bestseller')
            ->latest()
            ->paginate(24);

        return view('storefront.category', compact('category', 'products'));
    }
}
