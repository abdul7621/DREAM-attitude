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

    public function getGeographyReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // City breakdown with sessions, conversions, revenue
        $cities = DB::table('analytics_sessions')
            ->join('visitors', 'analytics_sessions.visitor_id', '=', 'visitors.id')
            ->whereBetween('analytics_sessions.started_at', [$start, $end])
            ->whereNotNull('visitors.city')
            ->where('visitors.city', '!=', '')
            ->selectRaw('
                visitors.city,
                visitors.region,
                COUNT(analytics_sessions.id) as sessions,
                COUNT(DISTINCT analytics_sessions.visitor_id) as unique_visitors,
                SUM(CASE WHEN analytics_sessions.reached_product = 1 THEN 1 ELSE 0 END) as product_views,
                SUM(CASE WHEN analytics_sessions.reached_cart = 1 THEN 1 ELSE 0 END) as add_to_cart,
                SUM(CASE WHEN analytics_sessions.reached_purchase = 1 THEN 1 ELSE 0 END) as purchases,
                SUM(analytics_sessions.revenue) as revenue,
                ROUND(AVG(analytics_sessions.duration_seconds)) as avg_duration
            ')
            ->groupBy('visitors.city', 'visitors.region')
            ->orderByDesc('sessions')
            ->limit(20)
            ->get();

        // State/Region summary
        $regions = DB::table('analytics_sessions')
            ->join('visitors', 'analytics_sessions.visitor_id', '=', 'visitors.id')
            ->whereBetween('analytics_sessions.started_at', [$start, $end])
            ->whereNotNull('visitors.region')
            ->where('visitors.region', '!=', '')
            ->selectRaw('
                visitors.region,
                COUNT(analytics_sessions.id) as sessions,
                COUNT(DISTINCT analytics_sessions.visitor_id) as unique_visitors,
                SUM(CASE WHEN analytics_sessions.reached_purchase = 1 THEN 1 ELSE 0 END) as purchases,
                SUM(analytics_sessions.revenue) as revenue
            ')
            ->groupBy('visitors.region')
            ->orderByDesc('sessions')
            ->limit(15)
            ->get();

        return [
            'cities' => $cities,
            'regions' => $regions,
        ];
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
        return AnalyticsEvent::with(['visitor:id,country,city', 'product:id,name', 'session:id,source'])
            ->latest('created_at')
            ->limit(15)
            ->get();
    }

    public function getLiveVisitorPulse(): array
    {
        // 5 minute window, must have executed JS (event_count > 0) to filter bots
        $sessions = AnalyticsSession::with('visitor')
            ->where('ended_at', '>=', now()->subMinutes(5))
            ->where('event_count', '>', 0)
            ->get();

        $pulse = [
            'total' => $sessions->count(),
            'sources' => [],
            'campaigns' => [],
            'intents' => [
                'cold_browsers' => 0,
                'product_evaluators' => 0,
                'high_intent' => 0,
                'customers' => 0,
            ],
            'geography' => [],
        ];

        foreach ($sessions as $session) {
            $visitor = $session->visitor;

            // Sources
            $source = ucfirst($session->source ?: 'Direct');
            $pulse['sources'][$source] = ($pulse['sources'][$source] ?? 0) + 1;

            // Campaigns
            if ($session->campaign) {
                $pulse['campaigns'][$session->campaign] = ($pulse['campaigns'][$session->campaign] ?? 0) + 1;
            }

            // Geography
            $city = $visitor->city ?? 'Unknown';
            if ($city !== 'Unknown') {
                $pulse['geography'][$city] = ($pulse['geography'][$city] ?? 0) + 1;
            }

            // Intents
            if ($visitor && $visitor->total_orders > 0) {
                $pulse['intents']['customers']++;
            } elseif ($session->reached_checkout || $session->reached_cart) {
                $pulse['intents']['high_intent']++;
            } elseif ($session->reached_product) {
                $pulse['intents']['product_evaluators']++;
            } else {
                $pulse['intents']['cold_browsers']++;
            }
        }

        // Sort arrays desc
        arsort($pulse['sources']);
        arsort($pulse['campaigns']);
        arsort($pulse['geography']);

        return $pulse;
    }

    public function getLiveProductInterest(): array
    {
        // Last 15 minutes for product interest
        $events = AnalyticsEvent::with('product:id,name')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->whereNotNull('product_id')
            ->whereIn('event_name', ['product_view', 'add_to_cart'])
            ->get();

        $products = [];
        foreach ($events as $event) {
            $pid = $event->product_id;
            if (!isset($products[$pid])) {
                $products[$pid] = [
                    'name' => $event->product->name ?? 'Unknown Product',
                    'views' => 0,
                    'atc' => 0,
                ];
            }
            if ($event->event_name === 'add_to_cart') {
                $products[$pid]['atc']++;
            } else {
                $products[$pid]['views']++;
            }
        }

        usort($products, fn($a, $b) => ($b['views'] + $b['atc']) <=> ($a['views'] + $a['atc']));
        return array_slice($products, 0, 5);
    }

    public function getCaptureAnalytics(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Orders that converted after being captured
        $recoveredOrders = \App\Models\Order::whereBetween('placed_at', [$start, $end])
            ->where('recovered_from_cart', true)
            ->where('payment_status', '!=', \App\Models\Order::PAYMENT_STATUS_FAILED)
            ->where('order_status', '!=', \App\Models\Order::ORDER_STATUS_CANCELLED)
            ->selectRaw('
                COUNT(id) as total_recovered,
                SUM(grand_total) as recovered_revenue
            ')->first();

        // A/B Lift metrics
        $abStats = \App\Models\Order::whereBetween('placed_at', [$start, $end])
            ->whereNotNull('lead_source')
            ->where('payment_status', '!=', \App\Models\Order::PAYMENT_STATUS_FAILED)
            ->where('order_status', '!=', \App\Models\Order::ORDER_STATUS_CANCELLED)
            ->selectRaw('
                lead_source,
                COUNT(id) as total_orders,
                SUM(grand_total) as revenue
            ')
            ->groupBy('lead_source')
            ->get()
            ->keyBy('lead_source');

        $variantA = $abStats->get('variant_a');
        $control = $abStats->get('control');

        // We also need total carts in each cohort to calculate true conversion rate
        $cartCohorts = \App\Models\Cart::whereBetween('created_at', [$start, $end])
            ->whereNotNull('lead_source')
            ->selectRaw('lead_source, COUNT(id) as total_carts')
            ->groupBy('lead_source')
            ->get()
            ->keyBy('lead_source');

        $variantACarts = $cartCohorts->get('variant_a')?->total_carts ?? 1;
        $controlCarts = $cartCohorts->get('control')?->total_carts ?? 1;

        // Analytics (Impressions/Submits/Skips) - for now we just use the Carts as the total "submits/qualifiers"
        // since full impression tracking requires a separate table or Redis.

        $variantAConv = $variantA ? ($variantA->total_orders / $variantACarts) * 100 : 0;
        $controlConv = $control ? ($control->total_orders / $controlCarts) * 100 : 0;

        $liftPct = $controlConv > 0 ? (($variantAConv - $controlConv) / $controlConv) * 100 : 0;

        $impressions = \App\Models\AnalyticsEvent::whereBetween('created_at', [$start, $end])
            ->where('event_name', 'capture_impression')->count();
        $submits = \App\Models\AnalyticsEvent::whereBetween('created_at', [$start, $end])
            ->where('event_name', 'capture_submit')->count();
        $skips = \App\Models\AnalyticsEvent::whereBetween('created_at', [$start, $end])
            ->where('event_name', 'capture_skip')->count();

        $submitPct = $impressions > 0 ? ($submits / $impressions) * 100 : 0;
        $skipPct = $impressions > 0 ? ($skips / $impressions) * 100 : 0;

        return [
            'recovered_revenue' => (float) ($recoveredOrders->recovered_revenue ?? 0),
            'recovered_count' => (int) ($recoveredOrders->total_recovered ?? 0),
            'variant_a_orders' => (int) ($variantA->total_orders ?? 0),
            'control_orders' => (int) ($control->total_orders ?? 0),
            'variant_a_conv' => round($variantAConv, 2),
            'control_conv' => round($controlConv, 2),
            'lift_pct' => round($liftPct, 2),
            'impressions' => $impressions,
            'submits' => $submits,
            'skips' => $skips,
            'submit_pct' => round($submitPct, 1),
            'skip_pct' => round($skipPct, 1),
        ];
    }
}
