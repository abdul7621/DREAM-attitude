<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Cached KPIs (5 min TTL) ─────────────────────────────
        $kpi = Cache::remember('dashboard_kpi', 300, function () {
            $totalRevenue = (float) Order::query()
                ->where('payment_status', Order::PAYMENT_STATUS_PAID)
                ->sum('grand_total');

            $totalPaidOrders = Order::where('payment_status', Order::PAYMENT_STATUS_PAID)->count();

            return [
                'todayOrders' => Order::query()->whereDate('placed_at', today())->count(),
                'todayRevenue' => (float) Order::query()
                    ->whereDate('placed_at', today())
                    ->where('payment_status', Order::PAYMENT_STATUS_PAID)
                    ->sum('grand_total'),
                'totalRevenue' => $totalRevenue,
                'pendingOrders' => Order::query()->where('order_status', Order::ORDER_STATUS_PLACED)->count(),
                'aov' => $totalPaidOrders > 0 ? round($totalRevenue / $totalPaidOrders, 2) : 0,
                'codOrders' => Order::where('payment_method', 'cod')
                    ->whereNotIn('order_status', ['cancelled', 'refunded'])->count(),
                'prepaidOrders' => Order::where('payment_method', '!=', 'cod')
                    ->whereNotIn('order_status', ['cancelled', 'refunded'])->count(),
                'highRiskCod' => Order::query()
                    ->where('payment_method', 'cod')
                    ->where('order_status', Order::ORDER_STATUS_PLACED)
                    ->where('grand_total', '>=', 5000)->count(),
                'pendingReviewsCount' => Review::where('is_approved', false)->count(),
            ];
        });

        extract($kpi);

        // ── Revenue last 7 days (always real-time for chart) ─────
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $rev = (float) Order::query()
                ->whereDate('placed_at', $date)
                ->where('payment_status', Order::PAYMENT_STATUS_PAID)
                ->sum('grand_total');
            $revenueChart[] = [
                'date' => $date->format('d M'),
                'revenue' => $rev,
            ];
        }

        // ── Top 5 products by sales ──────────────────────────
        $topProducts = DB::table('order_items')
            ->select('product_name_snapshot', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(line_total) as total_revenue'))
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // ── Low Stock (real-time) ────────────────────────────
        $lowStockVariants = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '<=', 5)
            ->where('is_active', true)
            ->with('product')
            ->get();

        // ── Recent Orders (real-time) ────────────────────────
        $recentOrders = Order::query()
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Recent Reviews (real-time) ───────────────────────
        $recentReviews = Review::query()
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();

        // ── Data Quality Warning: Empty Categories with matching products (5 min TTL) ──────
        $dataQualityWarnings = Cache::remember('dashboard_data_quality', 300, function () {
            $warnings = [];
            $categories = \App\Models\Category::where('is_active', true)->get();
            
            foreach ($categories as $cat) {
                // Count direct products
                $directCount = Product::where('category_id', $cat->id)->count();
                if ($directCount === 0) {
                    $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', $cat->name)));
                    if (!empty($words)) {
                        $searchQuery = Product::query()->where('status', Product::STATUS_ACTIVE);
                        $searchQuery->where(function ($qBuilder) use ($words): void {
                            foreach ($words as $word) {
                                $wordLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $word).'%';
                                $qBuilder->where(function ($subBuilder) use ($wordLike): void {
                                    $subBuilder->where('name', 'like', $wordLike)
                                        ->orWhere('sku', 'like', $wordLike)
                                        ->orWhere('short_description', 'like', $wordLike);
                                });
                            }
                        });
                        
                        $matchCount = $searchQuery->count();
                        if ($matchCount > 0) {
                            $warnings[] = [
                                'category_id' => $cat->id,
                                'category_name' => $cat->name,
                                'match_count' => $matchCount,
                            ];
                        }
                    }
                }
            }
            return $warnings;
        });

        return view('admin.dashboard', compact(
            'todayOrders', 'todayRevenue', 'totalRevenue', 'pendingOrders',
            'aov', 'codOrders', 'prepaidOrders',
            'revenueChart', 'topProducts',
            'lowStockVariants', 'recentOrders', 'recentReviews', 'pendingReturns',
            'highRiskCod', 'pendingReviewsCount', 'dataQualityWarnings'
        ));
    }
}

