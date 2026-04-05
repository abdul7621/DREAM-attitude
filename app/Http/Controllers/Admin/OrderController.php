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
        $order->load(['orderItems', 'shipments', 'coupon', 'user', 'returnRequests']);

        return view('admin.orders.show', compact('order'));
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
}
