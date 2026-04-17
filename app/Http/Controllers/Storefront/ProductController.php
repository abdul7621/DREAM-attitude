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

        // Related products (same category, active, exclude current AND frequentlyBought IDs)
        $relatedProducts = collect();
        if ($product->category_id) {
            $excludeIds = $frequentlyBought->pluck('id')->push($product->id)->unique()->toArray();
            $relatedProducts = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->where('category_id', $product->category_id)
                ->whereNotIn('id', $excludeIds)
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

        $selectedVariant = $product->variants->first();

        // Layout Config
        $defaultLayout = '[{"key":"gallery","enabled":true},{"key":"title_price","enabled":true},{"key":"variants","enabled":true},{"key":"buy_buttons","enabled":true},{"key":"trust_badges","enabled":true},{"key":"description","enabled":true},{"key":"specs","enabled":true},{"key":"faq","enabled":true},{"key":"recently_viewed","enabled":true},{"key":"reviews","enabled":true},{"key":"frequently_bought","enabled":true},{"key":"related","enabled":true}]';
        $layoutSections = json_decode($product->layout_config ?? $this->settings->get('theme.product_layout', $defaultLayout), true) ?? json_decode($defaultLayout, true);

        $deliveryEta = $this->settings->get('store.delivery_eta', '2-5 Business Days');
        $codEnabled = (bool)$this->settings->get('payment.cod_enabled', true);

        // ── Social Proof: Real buyer data ─────────────────────────────────────────
        // Wrapped in try-catch: any DB issue must NEVER break the product page.
        $socialProofData = [];
        try {
            $spEnabled = ($product->meta['show_social_proof'] ?? null) !== false
                && $this->settings->get('conversion_copy.social_proof_enabled', true) !== '0';

            if ($spEnabled) {
                // Step 1: Real orders for THIS product (last 90 days)
                $realRows = \App\Models\OrderItem::query()
                    ->select(['orders.customer_name', 'orders.placed_at'])
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.product_id', $product->id)
                    ->whereIn('orders.order_status', ['placed', 'confirmed', 'packed', 'shipped', 'delivered'])
                    ->whereNotNull('orders.placed_at')
                    ->where('orders.placed_at', '>=', now()->subDays(90))
                    ->orderByDesc('orders.placed_at')
                    ->limit(30)
                    ->get();

                // Step 2: If thin (<5), add same-category orders
                if ($realRows->count() < 5 && $product->category_id) {
                    $catProductIds = \App\Models\Product::where('category_id', $product->category_id)
                        ->where('id', '!=', $product->id)
                        ->pluck('id');

                    if ($catProductIds->isNotEmpty()) {
                        $catRows = \App\Models\OrderItem::query()
                            ->select(['orders.customer_name', 'orders.placed_at'])
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereIn('order_items.product_id', $catProductIds)
                            ->whereIn('orders.order_status', ['placed', 'confirmed', 'packed', 'shipped', 'delivered'])
                            ->whereNotNull('orders.placed_at')
                            ->where('orders.placed_at', '>=', now()->subDays(90))
                            ->orderByDesc('orders.placed_at')
                            ->limit(20)
                            ->get();

                        $realRows = $realRows->merge($catRows);
                    }
                }

                // Step 3: Build display-safe data from real rows
                foreach ($realRows->take(20) as $row) {
                    $name = trim($row->customer_name ?? '');
                    if (empty($name)) continue;
                    $firstName = explode(' ', $name)[0];

                    try {
                        $diffMinutes = (int) \Carbon\Carbon::parse($row->placed_at)->diffInMinutes(now());
                        if ($diffMinutes < 60) {
                            $timeAgo = $diffMinutes . ' minutes ago';
                        } elseif ($diffMinutes < 1440) {
                            $timeAgo = (int)($diffMinutes / 60) . ' hours ago';
                        } else {
                            $timeAgo = min((int)($diffMinutes / 1440), 2) . ' days ago';
                        }
                    } catch (\Throwable $e) {
                        $timeAgo = 'recently';
                    }

                    $socialProofData[] = ['name' => $firstName, 'time_ago' => $timeAgo];
                }

                // Step 4: Fill gaps with curated fallback if real data is thin
                if (count($socialProofData) < 5) {
                    $adminFallback = $this->settings->get('conversion_copy.social_proof_fallback', []);
                    if (is_string($adminFallback) && !empty($adminFallback)) {
                        $adminFallback = json_decode($adminFallback, true) ?: [];
                    }
                    if (empty($adminFallback) || !is_array($adminFallback)) {
                        $adminFallback = [
                            ['name' => 'Priya',  'time_ago' => '12 minutes ago'],
                            ['name' => 'Rahul',  'time_ago' => '28 minutes ago'],
                            ['name' => 'Sneha',  'time_ago' => '1 hours ago'],
                            ['name' => 'Arjun',  'time_ago' => '2 hours ago'],
                            ['name' => 'Kavita', 'time_ago' => '3 hours ago'],
                            ['name' => 'Vikram', 'time_ago' => '5 hours ago'],
                            ['name' => 'Meera',  'time_ago' => '1 days ago'],
                            ['name' => 'Rohit',  'time_ago' => '1 days ago'],
                            ['name' => 'Ananya', 'time_ago' => '2 days ago'],
                            ['name' => 'Sanjay', 'time_ago' => '2 days ago'],
                        ];
                    }
                    $needed = max(0, 8 - count($socialProofData));
                    $socialProofData = array_merge($socialProofData, array_slice($adminFallback, 0, $needed));
                }

                if (!empty($socialProofData)) {
                    shuffle($socialProofData);
                }
            }
        } catch (\Throwable $e) {
            // Safety net: social proof failure must never take down the product page
            $socialProofData = [];
        }



        return view('storefront.product', compact(
            'product', 'variantPrices', 'reviews', 'avgRating', 'reviewCount', 'relatedProducts',
            'frequentlyBought', 'recentlyViewed', 'layoutSections', 'deliveryEta', 'codEnabled',
            'selectedVariant', 'socialProofData'
        ));
    }
}
