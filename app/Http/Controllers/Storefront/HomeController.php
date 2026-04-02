<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->where('is_featured', true)
            ->with(['variants', 'images'])
            ->latest()
            ->take(12)
            ->get();

        $latest = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->with(['variants', 'images'])
            ->latest()
            ->take(12)
            ->get();

        return view('storefront.home', compact('featured', 'latest'));
    }
}
