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
