<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsEvent;
use App\Models\ProductMetricDaily;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getTrafficOverview(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $stats = AnalyticsSession::whereBetween('started_at', [$start, $end])
            ->selectRaw('
                COUNT(id) as total_sessions,
                COUNT(DISTINCT visitor_id) as total_visitors,
                SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounces,
                AVG(duration_seconds) as avg_duration,
                SUM(CASE WHEN reached_purchase = 1 THEN 1 ELSE 0 END) as conversions,
                SUM(revenue) as total_revenue
            ')->first();

        $sessions = (int) $stats->total_sessions;
        $visitors = (int) $stats->total_visitors;
        $bounceRate = $sessions > 0 ? round(($stats->bounces / $sessions) * 100, 1) : 0;
        $conversionRate = $sessions > 0 ? round(($stats->conversions / $sessions) * 100, 2) : 0;
        $avgDuration = round((float) $stats->avg_duration);

        return [
            'sessions' => $sessions,
            'visitors' => $visitors,
            'bounce_rate' => $bounceRate,
            'conversion_rate' => $conversionRate,
            'avg_duration_formatted' => gmdate("i:s", $avgDuration),
            'revenue' => (float) $stats->total_revenue,
        ];
    }

    public function getFunnel(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $funnel = AnalyticsSession::whereBetween('started_at', [$start, $end])
            ->selectRaw('
                COUNT(id) as total,
                SUM(CASE WHEN reached_product = 1 THEN 1 ELSE 0 END) as product_views,
                SUM(CASE WHEN reached_cart = 1 THEN 1 ELSE 0 END) as add_to_cart,
                SUM(CASE WHEN reached_checkout = 1 THEN 1 ELSE 0 END) as checkouts,
                SUM(CASE WHEN reached_purchase = 1 THEN 1 ELSE 0 END) as purchases
            ')->first();

        $total = max(1, (int) $funnel->total);

        return [
            'visitors' => ['count' => (int) $funnel->total, 'pct' => 100],
            'product' => ['count' => (int) $funnel->product_views, 'pct' => round(((int) $funnel->product_views / $total) * 100, 1)],
            'cart' => ['count' => (int) $funnel->add_to_cart, 'pct' => round(((int) $funnel->add_to_cart / $total) * 100, 1)],
            'checkout' => ['count' => (int) $funnel->checkouts, 'pct' => round(((int) $funnel->checkouts / $total) * 100, 1)],
            'purchase' => ['count' => (int) $funnel->purchases, 'pct' => round(((int) $funnel->purchases / $total) * 100, 1)],
        ];
    }

    public function getTrafficSources(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return AnalyticsSession::whereBetween('started_at', [$start, $end])
            ->selectRaw('
                COALESCE(source, "Direct") as source_name,
                COUNT(id) as sessions,
                SUM(revenue) as revenue
            ')
            ->groupBy('source_name')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get();
    }

    public function getTopLandingPages(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return AnalyticsSession::whereBetween('started_at', [$start, $end])
            ->whereNotNull('landing_page')
            ->selectRaw('landing_page, COUNT(id) as sessions')
            ->groupBy('landing_page')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get();
    }

    public function getProductIntelligence(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $start = Carbon::parse($startDate)->toDateString();
        $end = Carbon::parse($endDate)->toDateString();

        return ProductMetricDaily::with('product:id,name,slug')
            ->whereBetween('date', [$start, $end])
            ->selectRaw('
                product_id,
                SUM(views) as total_views,
                SUM(add_to_cart) as total_atc,
                SUM(purchases) as total_purchases,
                SUM(revenue) as total_revenue
            ')
            ->groupBy('product_id')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();
    }

    public function getAbandonmentIntelligence(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $stats = AnalyticsSession::whereBetween('started_at', [$start, $end])
            ->selectRaw('
                SUM(CASE WHEN reached_cart = 1 THEN 1 ELSE 0 END) as total_atc_sessions,
                SUM(CASE WHEN reached_checkout = 1 THEN 1 ELSE 0 END) as total_checkout_sessions,
                SUM(CASE WHEN reached_purchase = 1 THEN 1 ELSE 0 END) as total_purchases
            ')->first();

        // Abandoned Carts = Reached Cart BUT did not purchase
        $abandonedCarts = max(0, (int)$stats->total_atc_sessions - (int)$stats->total_purchases);
        $abandonmentRate = $stats->total_atc_sessions > 0 ? round(($abandonedCarts / $stats->total_atc_sessions) * 100, 1) : 0;

        // Checkout Drop-off = Reached Checkout BUT did not purchase
        $checkoutDrops = max(0, (int)$stats->total_checkout_sessions - (int)$stats->total_purchases);
        $checkoutDropRate = $stats->total_checkout_sessions > 0 ? round(($checkoutDrops / $stats->total_checkout_sessions) * 100, 1) : 0;

        // Estimate Lost Revenue (Very rough estimate based on average AOV, or we could sum up value of abandoned ATCs)
        // A better estimate: Look at ATC events without a purchase in that session
        $lostRevenueEvents = AnalyticsEvent::whereBetween('created_at', [$start, $end])
            ->where('event_name', 'add_to_cart')
            ->whereHas('session', function($q) {
                $q->where('reached_purchase', false);
            })
            ->get();
            
        $lostRevenue = 0;
        foreach ($lostRevenueEvents as $ev) {
            $val = $ev->meta['value'] ?? 0;
            $lostRevenue += (float) $val;
        }

        return [
            'abandoned_carts' => $abandonedCarts,
            'abandonment_rate' => $abandonmentRate,
            'checkout_drops' => $checkoutDrops,
            'checkout_drop_rate' => $checkoutDropRate,
            'lost_revenue' => $lostRevenue
        ];
    }

    public function getSearchIntelligence(string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // This relies on JSON extraction, which works in MySQL 5.7+ and MariaDB
        $searches = AnalyticsEvent::whereBetween('created_at', [$start, $end])
            ->where('event_name', 'search')
            ->get();

        $terms = [];
        foreach ($searches as $search) {
            $q = strtolower(trim($search->meta['query'] ?? ''));
            if (!$q) continue;
            
            $results = (int) ($search->meta['results'] ?? 0);
            
            if (!isset($terms[$q])) {
                $terms[$q] = ['query' => $q, 'count' => 0, 'zero_results' => $results === 0];
            }
            $terms[$q]['count']++;
        }

        return collect(array_values($terms))->sortByDesc('count')->take(10);
    }

    public function getDecisionFlags(string $startDate, string $endDate): array
    {
        $flags = [];
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Conversion Leak Detector (High Traffic, Low ATC)
        $products = ProductMetricDaily::with('product:id,name')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('product_id, SUM(views) as v, SUM(add_to_cart) as a, SUM(purchases) as p')
            ->groupBy('product_id')
            ->having('v', '>=', 50)
            ->get();

        foreach ($products as $prod) {
            $atcRate = $prod->v > 0 ? ($prod->a / $prod->v) * 100 : 0;
            $crRate = $prod->v > 0 ? ($prod->p / $prod->v) * 100 : 0;
            
            if ($prod->v >= 100 && $atcRate < 2.0) {
                $flags[] = [
                    'type' => 'warning',
                    'icon' => 'bi-funnel',
                    'title' => 'Product Page Leak',
                    'message' => "{$prod->product->name} has high traffic ({$prod->v} views) but very low Add-To-Cart rate (" . round($atcRate, 1) . "%). Review pricing or images."
                ];
            }
            
            if ($prod->a >= 20 && $crRate < 1.0) {
                $flags[] = [
                    'type' => 'danger',
                    'icon' => 'bi-cart-x',
                    'title' => 'High Product Abandonment',
                    'message' => "{$prod->product->name} was added to cart {$prod->a} times but rarely purchased. Check shipping rates or availability."
                ];
            }
        }

        // 2. Zero-Result Search Demands
        $searches = $this->getSearchIntelligence($startDate, $endDate);
        foreach ($searches as $search) {
            if ($search['zero_results'] && $search['count'] >= 5) {
                $flags[] = [
                    'type' => 'info',
                    'icon' => 'bi-search',
                    'title' => 'Unmet Demand',
                    'message' => "Customers searched for '{$search['query']}' {$search['count']} times but found nothing. Consider stocking this."
                ];
            }
        }

        return $flags;
    }

    public function getLiveFeed(): \Illuminate\Support\Collection
    {
        return AnalyticsEvent::with(['visitor:id,country', 'product:id,name'])
            ->latest('created_at')
            ->limit(15)
            ->get();
    }

    public function getLiveActiveVisitors(): int
    {
        return AnalyticsSession::where('ended_at', '>=', now()->subMinutes(5))->count();
    }
}
