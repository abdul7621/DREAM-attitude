@extends('layouts.admin')

@section('title', $order->order_number)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Order {{ $order->order_number }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-sm btn-outline-dark">Invoice PDF</a>
            <a href="{{ route('admin.orders.packing', $order) }}" class="btn btn-sm btn-outline-secondary">Packing slip</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">Back</a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-3">
                <h2 class="h6">Customer</h2>
                <p class="mb-0">{{ $order->customer_name }}<br>{{ $order->phone }}<br>{{ $order->email }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white rounded shadow-sm p-3">
                <h2 class="h6">Address</h2>
                <p class="mb-0 small">{{ $order->address_line1 }}@if ($order->address_line2)<br>{{ $order->address_line2 }}@endif<br>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded shadow-sm p-3 mt-3">
        <h2 class="h6">Items</h2>
        <table class="table table-sm mb-0">
            <thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Line</th></tr></thead>
            <tbody>
                @foreach ($order->orderItems as $oi)
                    <tr>
                        <td>{{ $oi->product_name_snapshot }} @if ($oi->variant_title_snapshot) — {{ $oi->variant_title_snapshot }} @endif</td>
                        <td class="text-end">{{ $oi->qty }}</td>
                        <td class="text-end">₹{{ number_format((float) $oi->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-end mb-0 mt-2"><strong>Grand total:</strong> ₹{{ number_format((float) $order->grand_total, 2) }}</p>
    </div>
    @if ($order->shipments->isNotEmpty())
        <div class="bg-white rounded shadow-sm p-3 mt-3">
            <h2 class="h6">Shipments</h2>
            <ul class="mb-0 small">
                @foreach ($order->shipments as $s)
                    <li>{{ $s->carrier }} — AWB: {{ $s->awb ?? '—' }} — {{ $s->status }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
