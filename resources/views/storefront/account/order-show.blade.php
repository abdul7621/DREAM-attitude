@extends('layouts.storefront')
@section('title', 'Order '.$order->order_number)
@section('content')
<h1 class="h4 mb-3">Order {{ $order->order_number }}</h1>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Order Items</div>
            <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light"><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th></tr></thead>
                <tbody>
                @foreach ($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product_name_snapshot }}</td>
                        <td>{{ $item->variant_title_snapshot }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>

        @if ($order->shipments->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">Shipment</div>
            <div class="card-body">
                @php $ship = $order->shipments->first(); @endphp
                <p>Status: <strong>{{ $ship->status }}</strong></p>
                @if ($ship->awb)
                    <p>AWB: <strong>{{ $ship->awb }}</strong></p>
                @endif
                @if ($ship->tracking_url)
                    <p><a href="{{ $ship->tracking_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Track Package →</a></p>
                @endif
            </div>
        </div>
        @endif

        {{-- Return request --}}
        @if ($order->order_status === 'delivered' && $order->returnRequests->isEmpty())
        <div class="card border-warning mb-3">
            <div class="card-header text-warning fw-semibold">Request a Return</div>
            <div class="card-body">
                <form action="{{ route('account.orders.return.store', $order) }}" method="post">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Reason for return</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Please describe the issue..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning btn-sm">Submit Return Request</button>
                </form>
            </div>
        </div>
        @elseif ($order->returnRequests->isNotEmpty())
            @php $ret = $order->returnRequests->first(); @endphp
            <div class="alert alert-info">Return request submitted — Status: <strong>{{ $ret->status }}</strong></div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Summary</div>
            <div class="card-body">
                <p>Status: <span class="badge bg-secondary">{{ $order->order_status }}</span></p>
                <p>Payment: <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ $order->payment_status }}</span></p>
                <hr>
                <div class="d-flex justify-content-between"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                @if ($order->discount_total > 0)
                <div class="d-flex justify-content-between text-success"><span>Discount</span><span>−₹{{ number_format($order->discount_total, 2) }}</span></div>
                @endif
                <div class="d-flex justify-content-between"><span>Shipping</span><span>₹{{ number_format($order->shipping_total, 2) }}</span></div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span>₹{{ number_format($order->grand_total, 2) }}</span></div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('account.orders') }}" class="btn btn-outline-secondary btn-sm">← All Orders</a>
        </div>
    </div>
</div>
@endsection
