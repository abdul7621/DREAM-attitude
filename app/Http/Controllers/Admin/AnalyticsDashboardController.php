<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsDashboardController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics)
    {
        $range = $request->get('range', '7d');
        
        $endDate = now();
        $startDate = match ($range) {
            'today' => now(),
            'yesterday' => now()->subDay(),
            '7d' => now()->subDays(6),
            '14d' => now()->subDays(13),
            '30d' => now()->subDays(29),
            '90d' => now()->subDays(89),
            default => now()->subDays(6),
        };

        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $overview = $analytics->getTrafficOverview($startStr, $endStr);
        $funnel = $analytics->getFunnel($startStr, $endStr);
        $sources = $analytics->getTrafficSources($startStr, $endStr);
        $pages = $analytics->getTopLandingPages($startStr, $endStr);
        $products = $analytics->getProductIntelligence($startStr, $endStr);
        $liveEvents = $analytics->getLiveFeed();
        $livePulse = $analytics->getLiveVisitorPulse();
        $liveProducts = $analytics->getLiveProductInterest();
        
        $abandonment = $analytics->getAbandonmentIntelligence($startStr, $endStr);
        $search = $analytics->getSearchIntelligence($startStr, $endStr);
        $flags = $analytics->getDecisionFlags($startStr, $endStr);
        $geography = $analytics->getGeographyReport($startStr, $endStr);
        $captureStats = $analytics->getCaptureAnalytics($startStr, $endStr);

        return view('admin.analytics.index', compact(
            'overview', 'funnel', 'sources', 'pages', 'products', 'liveEvents', 'livePulse', 'liveProducts', 
            'abandonment', 'search', 'flags', 'geography', 'captureStats', 'range', 'startDate', 'endDate'
        ));
    }
}
