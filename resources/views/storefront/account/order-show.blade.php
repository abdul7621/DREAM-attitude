@extends('layouts.account')
@section('title', 'Order '.$order->order_number)
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Order {{ $order->order_number }}</h1>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Order Items</div>
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
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Shipment</div>
            <div class="card-body">
                @php $ship = $order->shipments->first(); @endphp
                <p>Status: <strong>{{ $ship->status }}</strong></p>
                @if ($ship->awb) <p>AWB: <strong>{{ $ship->awb }}</strong></p> @endif
                @if ($ship->tracking_url)
                    <a href="{{ $ship->tracking_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Track Package →</a>
                @endif
            </div>
        </div>
        @endif

        {{-- Return request --}}
        @if ($order->order_status === 'delivered' && $order->returnRequests->isEmpty())
        <div class="card border-0 shadow-sm border-warning mb-3">
            <div class="card-header bg-white text-warning fw-semibold">Request a Return</div>
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
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Summary</div>
            <div class="card-body">
                <p>Status: <span class="badge bg-{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['color'] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></p>
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

        {{-- Reorder button --}}
        <form action="{{ route('account.orders.reorder', $order) }}" method="post">
            @csrf
            <button type="submit" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-arrow-repeat me-1"></i>Reorder This</button>
        </form>

        <a href="{{ route('account.orders') }}" class="btn btn-outline-secondary btn-sm">← All Orders</a>
    </div>
</div>
@endsection
