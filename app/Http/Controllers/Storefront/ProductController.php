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
        // Pull real orders for this product first, then category, then store-wide.
        // Privacy: only first name + city shown. Full name/email never exposed.
        $spEnabled = $product->meta['show_social_proof'] ?? $this->settings->get('conversion_copy.social_proof_enabled', true);
        $socialProofData = [];

        if ($spEnabled) {
            // Step 1: Real orders for THIS product (last 90 days)
            $realBuyers = \App\Models\OrderItem::query()
                ->where('product_id', $product->id)
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.order_status', ['placed', 'confirmed', 'packed', 'shipped', 'delivered'])
                ->where('orders.placed_at', '>=', now()->subDays(90))
                ->orderByDesc('orders.placed_at')
                ->limit(30)
                ->pluck('orders.customer_name', 'orders.placed_at')
                ->map(function ($name, $placedAt) {
                    $firstName = trim(explode(' ', trim($name))[0]);
                    return ['name' => $firstName, 'placed_at' => $placedAt];
                })
                ->values()
                ->toArray();

            // Step 2: If thin (<5), add category-level orders (same category, different product)
            if (count($realBuyers) < 5 && $product->category_id) {
                $catProductIds = \App\Models\Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->pluck('id');

                if ($catProductIds->isNotEmpty()) {
                    $catBuyers = \App\Models\OrderItem::query()
                        ->whereIn('product_id', $catProductIds)
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereIn('orders.order_status', ['placed', 'confirmed', 'packed', 'shipped', 'delivered'])
                        ->where('orders.placed_at', '>=', now()->subDays(90))
                        ->orderByDesc('orders.placed_at')
                        ->limit(20)
                        ->pluck('orders.customer_name', 'orders.placed_at')
                        ->map(function ($name, $placedAt) {
                            $firstName = trim(explode(' ', trim($name))[0]);
                            return ['name' => $firstName, 'placed_at' => $placedAt];
                        })
                        ->values()
                        ->toArray();

                    $realBuyers = array_merge($realBuyers, $catBuyers);
                }
            }

            // Step 3: Build final socialProofData with display-safe time strings
            if (!empty($realBuyers)) {
                foreach (array_slice($realBuyers, 0, 20) as $buyer) {
                    try {
                        $placedAt = \Carbon\Carbon::parse($buyer['placed_at']);
                        $diffMinutes = (int) $placedAt->diffInMinutes(now());
                        if ($diffMinutes < 60) {
                            $timeAgo = $diffMinutes . ' minutes ago';
                        } elseif ($diffMinutes < 1440) {
                            $timeAgo = (int) ($diffMinutes / 60) . ' hours ago';
                        } else {
                            $days = (int) ($diffMinutes / 1440);
                            // Cap display at "2 days" for recency perception
                            $timeAgo = min($days, 2) . ' days ago';
                        }
                    } catch (\Throwable $e) {
                        $timeAgo = 'recently';
                    }
                    $socialProofData[] = [
                        'name'     => $buyer['name'],
                        'time_ago' => $timeAgo,
                    ];
                }
            }

            // Step 4: If still <5 real entries, merge admin-configured fallback list
            $adminFallback = $this->settings->get('conversion_copy.social_proof_fallback', []);
            if (is_string($adminFallback)) {
                $adminFallback = json_decode($adminFallback, true) ?: [];
            }
            // Built-in smart fallback — realistic Indian names + cities (only used when real data is thin)
            if (empty($adminFallback)) {
                $adminFallback = [
                    ['name' => 'Priya',   'time_ago' => '12 minutes ago'],
                    ['name' => 'Rahul',   'time_ago' => '28 minutes ago'],
                    ['name' => 'Sneha',   'time_ago' => '1 hours ago'],
                    ['name' => 'Arjun',   'time_ago' => '2 hours ago'],
                    ['name' => 'Kavita',  'time_ago' => '3 hours ago'],
                    ['name' => 'Vikram',  'time_ago' => '5 hours ago'],
                    ['name' => 'Meera',   'time_ago' => '1 days ago'],
                    ['name' => 'Rohit',   'time_ago' => '1 days ago'],
                    ['name' => 'Ananya',  'time_ago' => '2 days ago'],
                    ['name' => 'Sanjay',  'time_ago' => '2 days ago'],
                ];
            }
            if (count($socialProofData) < 5) {
                // Only fill gaps — don't replace real buyers
                $needed = max(0, 8 - count($socialProofData));
                $socialProofData = array_merge($socialProofData, array_slice($adminFallback, 0, $needed));
            }

            // Shuffle so returning visitors see different order each time
            shuffle($socialProofData);
        }

        return view('storefront.product', compact(
            'product', 'variantPrices', 'reviews', 'avgRating', 'reviewCount', 'relatedProducts',
            'frequentlyBought', 'recentlyViewed', 'layoutSections', 'deliveryEta', 'codEnabled',
            'selectedVariant', 'socialProofData'
        ));
    }
}
