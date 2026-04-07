<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RecentlyViewed;
use App\Models\Review;
use App\Services\PricingService;
use App\Services\SettingsService;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly SettingsService $settings
    ) {}

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load(['variants', 'images', 'category']);

        // Log recently viewed (track)
        if (auth()->check()) {
            RecentlyViewed::updateOrCreate(
                ['user_id' => auth()->id(), 'product_id' => $product->id],
                ['viewed_at' => now(), 'session_id' => session()->getId()]
            );
        } else {
            RecentlyViewed::updateOrCreate(
                ['session_id' => session()->getId(), 'product_id' => $product->id],
                ['viewed_at' => now(), 'user_id' => null]
            );
        }

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

        // Frequently Bought Together
        $frequentlyBought = collect();
        if ($product->category_id) {
            $frequentlyBought = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('status', Product::STATUS_ACTIVE)
                ->orderByDesc('sales_count')
                ->take(3)
                ->with(['variants', 'images'])
                ->get();
        }

        // Recently Viewed
        $recentIds = RecentlyViewed::query()
            ->where(function($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                } else {
                    $q->where('session_id', session()->getId());
                }
            })
            ->where('product_id', '!=', $product->id)
            ->orderByDesc('viewed_at')
            ->take(6)
            ->pluck('product_id');
            
        $recentlyViewed = collect();
        if ($recentIds->isNotEmpty()) {
            $products = Product::whereIn('id', $recentIds)->with(['variants', 'images'])->get();
            $recentlyViewed = $products->sortBy(function ($product) use ($recentIds) {
                return array_search($product->id, $recentIds->toArray());
            })->values();
        }

        // Layout Config
        $defaultLayout = '[{"key":"gallery","enabled":true},{"key":"title_price","enabled":true},{"key":"variants","enabled":true},{"key":"buy_buttons","enabled":true},{"key":"trust_badges","enabled":true},{"key":"description","enabled":true},{"key":"specs","enabled":true},{"key":"faq","enabled":true},{"key":"recently_viewed","enabled":true},{"key":"reviews","enabled":true},{"key":"frequently_bought","enabled":true},{"key":"related","enabled":true}]';
        $layoutSections = json_decode($product->layout_config ?? $this->settings->get('theme.product_layout', $defaultLayout), true) ?? json_decode($defaultLayout, true);

        $deliveryEta = $this->settings->get('store.delivery_eta', '2-5 Business Days');
        $codEnabled = (bool)$this->settings->get('payment.cod_enabled', true);

        return view('storefront.product', compact(
            'product', 'variantPrices', 'reviews', 'avgRating', 'reviewCount', 'relatedProducts',
            'frequentlyBought', 'recentlyViewed', 'layoutSections', 'deliveryEta', 'codEnabled'
        ));
    }
}
