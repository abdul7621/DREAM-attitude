<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()->withCount('orderItems')->orderByDesc('id');

        // ── Filters ──────────────────────────────────────────
        if ($status = $request->get('status')) {
            $query->where('order_status', $status);
        }
        if ($payment = $request->get('payment')) {
            $query->where('payment_method', $payment);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(30)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['orderItems', 'shipments', 'coupon', 'user', 'returnRequests', 'statusLogs']);

        $previousOrders = collect();
        if ($order->user_id) {
            $previousOrders = Order::where('user_id', $order->user_id)->where('id', '!=', $order->id)->latest()->get();
        } elseif ($order->phone) {
            $previousOrders = Order::where('phone', $order->phone)->where('id', '!=', $order->id)->latest()->get();
        }

        return view('admin.orders.show', compact('order', 'previousOrders'));
    }

    /**
     * Update order status + optional shipment tracking.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'order_status' => 'required|string',
            'admin_notes'  => 'nullable|string|max:1000',
            'awb'          => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
            'carrier'      => 'nullable|string|max:50',
        ]);

        $newStatus = $data['order_status'];
        $oldStatus = $order->order_status;

        // Validate the requested transition
        if (! $order->canTransitionTo($newStatus)) {
            return redirect()->back()->with('error', "Cannot change status from \"{$order->statusLabel()}\" to \"{$newStatus}\".");
        }

        $order->update([
            'order_status' => $newStatus,
            'notes'        => $data['admin_notes'] ?? $order->notes,
        ]);

        // Auto-create shipment when shipping
        if ($newStatus === 'shipped' && ($data['awb'] ?? null)) {
            Shipment::create([
                'order_id'     => $order->id,
                'carrier'      => $data['carrier'] ?? 'manual',
                'awb'          => $data['awb'],
                'tracking_url' => $data['tracking_url'] ?? null,
                'status'       => 'shipped',
            ]);
        }

        // Handle refund → also update payment status
        if ($newStatus === 'refunded') {
            $order->update(['payment_status' => Order::PAYMENT_STATUS_REFUNDED]);
        }

        event(new \App\Events\OrderStatusChanged($order, $oldStatus, $newStatus, $data['admin_notes'] ?? null));

        if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
            event(new \App\Events\OrderShipped($order));
        }

        return redirect()->route('admin.orders.show', $order)->with('success', "Order status updated to \"{$newStatus}\".");
    }

    public function invoicePdf(Order $order): Response
    {
        $order->load('orderItems');

        return Pdf::loadView('pdf.invoice', ['order' => $order])
            ->download('invoice-'.$order->order_number.'.pdf');
    }

    public function packingPdf(Order $order): Response
    {
        $order->load('orderItems');

        return Pdf::loadView('pdf.packing-slip', ['order' => $order])
            ->download('packing-'.$order->order_number.'.pdf');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'action' => 'required|in:confirmed,packed',
        ]);
        
        $orders = Order::whereIn('id', $data['order_ids'])->get();
        $count = 0;
        foreach ($orders as $order) {
            if ($order->canTransitionTo($data['action'])) {
                $oldStatus = $order->order_status;
                $order->update(['order_status' => $data['action']]);
                event(new \App\Events\OrderStatusChanged($order, $oldStatus, $data['action'], 'Bulk action'));
                $count++;
            }
        }
        return back()->with('success', "{$count} orders updated.");
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $orders = Order::query()->orderByDesc('id')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=orders-" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Order Number', 'Date', 'Customer Name', 'Email', 'Phone', 'Status', 'Payment Method', 'Payment Status', 'Total'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $row['Order Number']  = $order->order_number;
                $row['Date']          = $order->placed_at ? $order->placed_at->format('Y-m-d H:i') : '';
                $row['Customer Name'] = $order->customer_name;
                $row['Email']         = $order->email;
                $row['Phone']         = $order->phone;
                $row['Status']        = $order->order_status;
                $row['Payment Method']= $order->payment_method;
                $row['Payment Status']= $order->payment_status;
                $row['Total']         = $order->grand_total;

                fputcsv($file, array($row['Order Number'], $row['Date'], $row['Customer Name'], $row['Email'], $row['Phone'], $row['Status'], $row['Payment Method'], $row['Payment Status'], $row['Total']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function resendNotification(Order $order): RedirectResponse
    {
        event(new \App\Events\OrderStatusChanged($order, $order->order_status, $order->order_status, 'Notification resent by admin'));
        return back()->with('success', 'Notification resent.');
    }
}
