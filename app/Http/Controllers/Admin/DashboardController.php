<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayOrders = Order::query()
            ->whereDate('placed_at', today())
            ->count();

        $todayRevenue = Order::query()
            ->whereDate('placed_at', today())
            ->where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->sum('grand_total');

        $totalRevenue = Order::query()
            ->where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->sum('grand_total');

        $pendingOrders = Order::query()
            ->where('order_status', Order::ORDER_STATUS_PLACED)
            ->count();

        $lowStockVariants = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '<=', 5)
            ->where('is_active', true)
            ->with('product')
            ->get();

        $recentOrders = Order::query()
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'todayOrders',
            'todayRevenue',
            'totalRevenue',
            'pendingOrders',
            'lowStockVariants',
            'recentOrders'
        ));
    }
}
