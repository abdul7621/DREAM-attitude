<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    // ── 1. Sales Report ──────────────────────────────────────────────
    public function sales(Request $request): View
    {
        $startDate = $request->input('start_date', now()->subDays(6)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Base query for valid orders in range
        $ordersQuery = Order::query()
            ->whereBetween('placed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]); // exclude cancelled

        $totalOrders = (clone $ordersQuery)->count();
        $grossRevenue = (float) (clone $ordersQuery)->sum('grand_total');

        // Refund calculation
        // Refunds can be tracked if order_status == refunded or payment_status == refunded
        $refundAmount = (float) Order::query()
            ->whereBetween('placed_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('order_status', Order::ORDER_STATUS_REFUNDED)
            ->sum('grand_total');
            
        $netRevenue = $grossRevenue - $refundAmount;
        $aov = $totalOrders > 0 ? round($grossRevenue / $totalOrders, 2) : 0;

        // COD vs Prepaid split
        $codOrders = (clone $ordersQuery)->where('payment_method', Order::PAYMENT_COD)->count();
        $prepaidOrders = $totalOrders - $codOrders;

        // Line chart data (Revenue per day in range)
        $chartDataObj = (clone $ordersQuery)
            ->select(DB::raw('DATE(placed_at) as date'), DB::raw('SUM(grand_total) as revenue'))
            ->groupBy(DB::raw('DATE(placed_at)'))
            ->orderBy(DB::raw('DATE(placed_at)'))
            ->get()
            ->keyBy('date');

        $chartData = [];
        $current = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($current <= $end) {
            $d = $current->toDateString();
            $chartData[] = [
                'date' => $current->format('d M'),
                'revenue' => isset($chartDataObj[$d]) ? (float) $chartDataObj[$d]->revenue : 0,
            ];
            $current->addDay();
        }

        return view('admin.reports.sales', compact(
            'startDate', 'endDate', 'grossRevenue', 'netRevenue', 
            'totalOrders', 'aov', 'codOrders', 'prepaidOrders', 
            'refundAmount', 'chartData'
        ));
    }

    // ── 2. Product Report ────────────────────────────────────────────
    public function products(Request $request): View
    {
        // Top selling products by revenue & qty
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.order_status', [Order::ORDER_STATUS_CANCELLED])
            ->select('order_items.product_id', 'order_items.product_name_snapshot', 
                DB::raw('SUM(order_items.qty) as total_qty'), 
                DB::raw('SUM(order_items.line_total) as total_revenue'))
            ->groupBy('order_items.product_id', 'order_items.product_name_snapshot')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();

        // Dead products (no sales in last 30 days)
        $recentSalesProductIds = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.placed_at', '>=', now()->subDays(30))
            ->whereNotIn('orders.order_status', [Order::ORDER_STATUS_CANCELLED])
            ->pluck('order_items.product_id')
            ->unique();

        $deadProducts = Product::query()
            ->whereNotIn('id', $recentSalesProductIds)
            ->where('status', Product::STATUS_ACTIVE)
            ->select('id', 'name', 'created_at')
            ->orderByDesc('id')
            ->paginate(15);

        // Revenue per product across time (Paginated)
        $productRevenues = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.order_status', [Order::ORDER_STATUS_CANCELLED])
            ->select('order_items.product_name_snapshot', 
                DB::raw('SUM(order_items.qty) as total_qty'), 
                DB::raw('SUM(order_items.line_total) as total_revenue'))
            ->groupBy('order_items.product_name_snapshot')
            ->orderByDesc('total_revenue')
            ->paginate(20, ['*'], 'rev_page');

        return view('admin.reports.products', compact('topProducts', 'deadProducts', 'productRevenues'));
    }

    // ── 3. Customer Report ───────────────────────────────────────────
    public function customers(): View
    {
        $totalCustomers = User::where('is_admin', false)->count();

        // Repeat vs New -> new is exactly 1 order, repeat > 1
        $customerOrderCounts = DB::table('orders')
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(id) as order_count'))
            ->groupBy('user_id')
            ->get();
            
        $repeatCustomers = $customerOrderCounts->where('order_count', '>', 1)->count();
        $newCustomers = $customerOrderCounts->where('order_count', 1)->count();
        // The remaining to totalCustomers are customers with 0 orders.

        // Top customers by LTV (Lifetime Value)
        $topCustomers = User::where('is_admin', false)
            ->withCount(['orders' => function($q) {
                $q->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]);
            }])
            ->withSum(['orders as lifetime_value' => function($q) {
                $q->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]);
            }], 'grand_total')
            ->having('lifetime_value', '>', 0)
            ->orderByDesc('lifetime_value')
            ->limit(20)
            ->get();

        // COD-heavy risk flag (Users whose > 80% orders are COD and have returned/cancelled)
        // Simplified query to find high COD count users
        $codHeavyCustomers = User::where('is_admin', false)
            ->withCount(['orders as total_orders'])
            ->withCount(['orders as cod_orders' => function($q) {
                $q->where('payment_method', Order::PAYMENT_COD);
            }])
            ->withCount(['orders as failed_orders' => function($q) {
                $q->whereIn('order_status', [Order::ORDER_STATUS_CANCELLED, Order::ORDER_STATUS_REFUNDED]);
            }])
            ->having('cod_orders', '>', 2) // At least 3 COD orders
            ->having(DB::raw('cod_orders / NULLIF(total_orders, 0)'), '>=', 0.8) // 80% COD
            ->orderByDesc('failed_orders')
            ->limit(20)
            ->get();

        return view('admin.reports.customers', compact(
            'totalCustomers', 'repeatCustomers', 'newCustomers', 
            'topCustomers', 'codHeavyCustomers'
        ));
    }

    // ── 4. Coupon Report ─────────────────────────────────────────────
    public function coupons(): View
    {
        $coupons = Coupon::query()
            ->withCount(['orders as usage_count' => function($q) {
                $q->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]);
            }])
            ->withSum(['orders as revenue_generated' => function($q) {
                $q->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]);
            }], 'grand_total')
            ->withSum(['orders as total_discount_given' => function($q) {
                $q->whereNotIn('order_status', [Order::ORDER_STATUS_CANCELLED]);
            }], 'discount_total')
            ->orderByDesc('usage_count')
            ->paginate(20);

        return view('admin.reports.coupons', compact('coupons'));
    }

    // ── 5. Inventory Report ──────────────────────────────────────────
    public function inventory(): View
    {
        $threshold = config('commerce.pricing.low_stock_threshold', 5);

        // Variants with track_inventory
        $inStock = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '>', $threshold)
            ->with('product')
            ->paginate(20, ['*'], 'stock_page');

        $lowStock = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '<=', $threshold)
            ->where('stock_qty', '>', 0)
            ->with('product')
            ->paginate(20, ['*'], 'low_page');

        $outOfStock = ProductVariant::query()
            ->where('track_inventory', true)
            ->where('stock_qty', '<=', 0)
            ->with('product')
            ->paginate(20, ['*'], 'oos_page');

        return view('admin.reports.inventory', compact('inStock', 'lowStock', 'outOfStock', 'threshold'));
    }
}
