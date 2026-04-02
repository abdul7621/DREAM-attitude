@extends('layouts.storefront')

@section('title', 'Order placed')

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
    <div class="text-center py-4">
        <h1 class="h3 mb-3">Thank you</h1>
        <p class="lead">Your order number is <strong>{{ $order->order_number }}</strong></p>
        <p class="text-muted">Payment: {{ strtoupper($order->payment_method) }} · Status: {{ $order->payment_status }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-3">Continue shopping</a>
    </div>
    @if ($order->orderItems->isNotEmpty())
        <div class="bg-white rounded shadow-sm p-3 mt-4">
            <h2 class="h6 mb-3">Items</h2>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead>
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
            <div class="text-end fw-semibold mt-2">Grand total: ₹{{ number_format((float) $order->grand_total, 2) }}</div>
        </div>
    @endif
@endsection
