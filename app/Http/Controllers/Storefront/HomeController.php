<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Cache::remember('home_featured', 300, fn () => Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where('is_featured', true)
            ->with(['variants', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(8)
            ->get());

        $bestsellers = Cache::remember('home_bestsellers', 300, fn () => Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where('is_bestseller', true)
            ->with(['variants', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('sales_count')
            ->take(8)
            ->get());

        $latest = Cache::remember('home_latest', 300, fn () => Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(12)
            ->get());

        $categories = Cache::remember('home_categories', 300, fn () => Category::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->take(3)
            ->get());

        // For testimonials on home page
        $topReviews = Cache::remember('home_reviews', 300, fn () => Review::query()
            ->where('is_approved', true)
            ->where('rating', '>=', 4)
            ->with('product')
            ->latest()
            ->take(4)
            ->get());

        return view('storefront.home', compact(
            'featured', 'bestsellers', 'latest', 'categories', 'topReviews'
        ));
    }
}

