@extends('layouts.storefront')

@section('title', 'Order Confirmed — ' . $order->order_number)

@push('scripts')
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });
dataLayer.push({
    event: 'purchase',
    ecommerce: {
        transaction_id: @json($order->order_number),
        value: {{ (float) $order->grand_total }},
        tax: {{ (float) $order->tax_total }},
        shipping: {{ (float) $order->shipping_total }},
        currency: @json($order->currency),
        coupon: @json($order->coupon_code_snapshot),
        items: [
            @foreach ($order->orderItems as $oi)
            {
                item_id: @json($oi->sku_snapshot ?: 'v'.$oi->product_variant_id),
                item_name: @json($oi->product_name_snapshot),
                price: {{ (float) $oi->unit_price }},
                quantity: {{ (int) $oi->qty }}
            }@if(! $loop->last),@endif
            @endforeach
        ]
    }
});
@if (config('commerce.meta.pixel_id'))
fbq('track', 'Purchase', {
    value: {{ (float) $order->grand_total }},
    currency: @json($order->currency),
    contents: [
        @foreach ($order->orderItems as $oi)
        { id: @json($oi->sku_snapshot ?: 'v'.$oi->product_variant_id), quantity: {{ (int) $oi->qty }} }@if(! $loop->last),@endif
        @endforeach
    ],
    content_type: 'product'
});
@endif
</script>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Success Header --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 mb-3" style="width:80px;height:80px;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:2.5rem;"></i>
                </div>
                <h1 class="h3 fw-bold mb-2">Order Confirmed!</h1>
                <p class="text-muted mb-1">Thank you for your order, <strong>{{ $order->customer_name }}</strong></p>
                <p class="lead mb-0">Order <strong>#{{ $order->order_number }}</strong></p>
            </div>

            {{-- Reassurance Bar --}}
            <div class="row g-2 mb-4 text-center">
                <div class="col-4">
                    <div class="bg-light rounded p-3 h-100">
                        <i class="bi bi-shield-check text-success d-block mb-1" style="font-size:1.3rem;"></i>
                        <small class="fw-medium">Secure Order</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light rounded p-3 h-100">
                        <i class="bi bi-truck text-primary d-block mb-1" style="font-size:1.3rem;"></i>
                        <small class="fw-medium">Fast Shipping</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-light rounded p-3 h-100">
                        <i class="bi bi-arrow-return-left text-info d-block mb-1" style="font-size:1.3rem;"></i>
                        <small class="fw-medium">Easy Returns</small>
                    </div>
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Payment Method</small>
                            <strong>{{ strtoupper($order->payment_method) }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Payment Status</small>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            @if ($order->orderItems->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-bag me-1"></i> Items Ordered</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead>
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
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Subtotal</span>
                        <span>₹{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    @if((float)$order->shipping_total > 0)
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Shipping</span>
                        <span>₹{{ number_format((float) $order->shipping_total, 2) }}</span>
                    </div>
                    @endif
                    @if((float)$order->discount_total > 0)
                    <div class="d-flex justify-content-between small text-success">
                        <span>Discount</span>
                        <span>-₹{{ number_format((float) $order->discount_total, 2) }}</span>
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Grand Total</span>
                        <span>₹{{ number_format((float) $order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Shipping Address --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-geo-alt me-1"></i> Shipping Address</div>
                <div class="card-body small">
                    <strong>{{ $order->customer_name }}</strong><br>
                    {{ $order->address_line1 }}<br>
                    @if($order->address_line2){{ $order->address_line2 }}<br>@endif
                    {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                    {{ $order->country ?? 'India' }}<br>
                    <i class="bi bi-telephone"></i> {{ $order->phone }}
                </div>
            </div>

            {{-- CTA --}}
            <div class="text-center">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-bag me-1"></i> Continue Shopping
                </a>
                @auth
                <div class="mt-2">
                    <a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-receipt me-1"></i> Track Your Order
                    </a>
                </div>
                @endauth
            </div>

        </div>
    </div>
</div>
@endsection
