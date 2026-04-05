<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Core KPIs ────────────────────────────────────────
        $todayOrders = Order::query()
            ->whereDate('placed_at', today())
            ->count();

        $todayRevenue = (float) Order::query()
            ->whereDate('placed_at', today())
            ->where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->sum('grand_total');

        $totalRevenue = (float) Order::query()
            ->where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->sum('grand_total');

        $pendingOrders = Order::query()
            ->where('order_status', Order::ORDER_STATUS_PLACED)
            ->count();

        // ── AOV (Average Order Value) ────────────────────────
        $totalPaidOrders = Order::where('payment_status', Order::PAYMENT_STATUS_PAID)->count();
        $aov = $totalPaidOrders > 0 ? round($totalRevenue / $totalPaidOrders, 2) : 0;

        // ── COD vs Prepaid ───────────────────────────────────
        $codOrders = Order::where('payment_method', Order::PAYMENT_COD)
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->count();
        $prepaidOrders = Order::where('payment_method', '!=', Order::PAYMENT_COD)
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->count();

        // ── Revenue last 7 days (for chart) ──────────────────
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

        // ── Low Stock ────────────────────────────────────────
        $lowStockVariants = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '<=', 5)
            ->where('is_active', true)
            ->with('product')
            ->get();

        // ── Recent Orders ────────────────────────────────────
        $recentOrders = Order::query()
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Recent Reviews ───────────────────────────────────
        $recentReviews = Review::query()
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();

        // ── Pending Returns ──────────────────────────────────
        $pendingReturns = ReturnRequest::where('status', 'requested')->count();

        return view('admin.dashboard', compact(
            'todayOrders', 'todayRevenue', 'totalRevenue', 'pendingOrders',
            'aov', 'codOrders', 'prepaidOrders',
            'revenueChart', 'topProducts',
            'lowStockVariants', 'recentOrders', 'recentReviews', 'pendingReturns'
        ));
    }
}
