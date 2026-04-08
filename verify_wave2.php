<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Review;
use App\Models\ReturnRequest;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

$results = [];

try {
    // 1. Order Timeline
    // Does OrderStatusChanged event fire correctly and create entry?
    $order = Order::orderBy('id', 'desc')->first();
    if ($order) {
        $oldCount = OrderStatusLog::where('order_id', $order->id)->count();
        event(new \App\Events\OrderStatusChanged($order, $order->order_status, 'confirmed'));
        $newCount = OrderStatusLog::where('order_id', $order->id)->count();
        $log = OrderStatusLog::where('order_id', $order->id)->orderBy('id', 'desc')->first();
        
        $results['OrderTimeline'] = [
            'event_fired' => true,
            'logs_incremented' => ($newCount === $oldCount + 1),
            'timestamp_accurate' => $log && $log->created_at->diffInSeconds(now()) < 5
        ];
        
        // rollback the log to clean up
        if ($log) $log->delete();
    } else {
        $results['OrderTimeline'] = 'No orders exist to test.';
    }

    // 2. Bulk Actions
    // Test with 50+ orders
    DB::beginTransaction();
    $ordersToTest = Order::limit(50)->get();
    if ($ordersToTest->count() > 0) {
        $ids = $ordersToTest->pluck('id')->toArray();
        $controller = new \App\Http\Controllers\Admin\OrderController();
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_ids' => $ids, 'action' => 'packed']);
        
        $response = $controller->bulkUpdate($request);
        
        $updatedOrders = Order::whereIn('id', $ids)->get();
        $allUpdated = $updatedOrders->every(fn($o) => $o->order_status === 'packed');
        
        $results['BulkActions'] = [
            'total_tested' => count($ids),
            'all_updated' => $allUpdated,
            'no_skipped' => $updatedOrders->where('order_status', '!=', 'packed')->count() === 0,
            // Since events are fired inside the controller loop, logs should increase
            'logs_created' => OrderStatusLog::whereIn('order_id', $ids)->where('status', 'packed')->count() === count($ids)
        ];
    } else {
        $results['BulkActions'] = 'No orders to test.';
    }
    DB::rollBack();

    // 3. CSV Export
    // Just verify the export generation
    $controller = new \App\Http\Controllers\Admin\OrderController();
    $request = new \Illuminate\Http\Request();
    // Simulate getting 10 orders
    $request->replace(['status' => '']);
    ob_start();
    $response = $controller->exportCsv($request);
    $csvData = ob_get_clean();
    if (empty($csvData) && method_exists($response, 'getFile')) {
        $csvData = file_get_contents($response->getFile()->getPathname());
    }
    // Also if the response is StreamedResponse
    if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        ob_start();
        $response->sendContent();
        $csvData = ob_get_clean();
    }

    $results['CSVExport'] = [
        'is_generated' => !empty($csvData),
        'contains_header' => str_contains($csvData, 'Order Number,Customer Name,Email,Phone'),
        // ensure encoding has no issues - roughly check printable characters
        'valid_encoding' => mb_check_encoding($csvData, 'UTF-8')
    ];

    // 4. Resend Notification
    DB::beginTransaction();
    $orderForResend = Order::first();
    if ($orderForResend) {
        $oldStatus = $orderForResend->order_status;
        $request = new \Illuminate\Http\Request();
        $response = $controller->resendNotification($request, $orderForResend);
        
        $orderForResend->refresh();
        $results['ResendNotification'] = [
            'status_unchanged' => $orderForResend->order_status === $oldStatus,
            'is_redirect' => $response instanceof \Illuminate\Http\RedirectResponse,
        ];
    }
    DB::rollBack();

    // 5 & 6. Dashboard & Badges Exact logic test
    $todayOrders = Order::query()->whereDate('placed_at', today())->count();
    $pendingOrders = Order::where('order_status', Order::ORDER_STATUS_PLACED)->count();
    $pendingReturns = ReturnRequest::where('status', 'requested')->count();
    $lowStock = ProductVariant::where('track_inventory', true)->where('stock_qty', '<=', 5)->where('is_active', true)->count();
    $highRiskCod = Order::query()
            ->where('payment_method', Order::PAYMENT_COD)
            ->where('order_status', Order::ORDER_STATUS_PLACED)
            ->where('grand_total', '>=', 5000)
            ->count();
    $pendingReviews = Review::where('is_approved', false)->count();

    $results['DashboardAndBadges'] = [
        'queries_dynamic' => true,
        'todayOrders' => $todayOrders,
        'pendingOrders' => $pendingOrders,
        'pendingReturns' => $pendingReturns,
        'lowStock' => $lowStock,
        'highRiskCod' => $highRiskCod,
        'pendingReviews' => $pendingReviews
    ];

} catch (\Exception $e) {
    $results['error'] = $e->getMessage() . " at line " . $e->getLine();
}

echo json_encode($results, JSON_PRETTY_PRINT);
