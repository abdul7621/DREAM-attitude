<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\PricingService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing
    ) {}

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load(['variants', 'images', 'category']);

        $variantPrices = [];
        foreach ($product->variants as $v) {
            $variantPrices[$v->id] = [
                'retail' => $v->price_retail,
                'display' => $this->pricing->unitPriceForCustomer($v, auth()->user(), 1),
                'compare' => $this->pricing->compareAt($v),
            ];
        }

        // Load approved reviews
        $reviews = Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating');
        $reviewCount = $reviews->count();

        // Related products (same category, active, exclude current)
        $relatedProducts = collect();
        if ($product->category_id) {
            $relatedProducts = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with(['variants', 'images'])
                ->take(4)
                ->get();
        }

        return view('storefront.product', compact(
            'product', 'variantPrices', 'reviews', 'avgRating', 'reviewCount', 'relatedProducts'
        ));
    }
}
