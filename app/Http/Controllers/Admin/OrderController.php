<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->withCount('orderItems')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['orderItems', 'shipments', 'coupon']);

        return view('admin.orders.show', compact('order'));
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
