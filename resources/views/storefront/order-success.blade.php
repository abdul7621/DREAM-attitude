@extends('layouts.storefront')

@section('title', 'Order Confirmed — ' . $order->order_number)

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush
@push('scripts')
@if($isFirstVisit)
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
if (typeof fbq === 'function') {
    fbq('track', 'Purchase', {
        value: {{ (float) $order->grand_total }},
        currency: @json($order->currency),
        contents: [
            @foreach ($order->orderItems as $oi)
            { id: @json($oi->sku_snapshot ?: 'v'.$oi->product_variant_id), quantity: {{ (int) $oi->qty }} }@if(! $loop->last),@endif
            @endforeach
        ],
        content_type: 'product'
    }, {
        eventID: 'purchase-{{ $order->order_number }}'
    });
}

if (window.Store) {
    Store.track('purchase', {
        order_id: @json($order->order_number),
        revenue: {{ (float) $order->grand_total }}
    });
}
</script>
@endif
@endpush

@section('content')
<section class="sf-section sf-os-section">
    <div class="sf-container sf-os-container">

        {{-- Success Header --}}
        <div class="sf-os-header">
            <div class="sf-os-icon-wrap">
                <i class="bi bi-check-circle-fill sf-os-icon"></i>
            </div>
            <h1 class="sf-os-title">Order Confirmed!</h1>
            <p class="sf-os-subtitle">Thank you for your order, <strong style="color:var(--color-text-primary);">{{ $order->customer_name }}</strong></p>
            <p class="sf-os-order-num">Order <strong>#{{ $order->order_number }}</strong></p>
        </div>

        @if(session('account_created_email'))
            <div class="sf-os-alert-success">
                <i class="bi bi-person-check sf-os-alert-success-icon"></i>
                <h4 class="sf-os-alert-success-title">Account Created Successfully!</h4>
                <p class="sf-os-alert-success-text">We've sent a password reset link to <strong>{{ session('account_created_email') }}</strong> so you can track your orders easily.</p>
            </div>
        @elseif(session('account_created_phone'))
            <div class="sf-os-alert-gold">
                <i class="bi bi-person-check sf-os-alert-gold-icon"></i>
                <h4 class="sf-os-alert-gold-title">Account Created Successfully!</h4>
                <p class="sf-os-alert-gold-text">An account was created for you using <strong>{{ session('account_created_phone') }}</strong>. You can use it to track your orders!</p>
            </div>
        @endif

        {{-- Trust / Reassurance Bar --}}
        <div class="sf-os-trust-bar">
            <div class="sf-os-trust-item">
                <i class="bi bi-shield-check sf-os-trust-icon"></i>
                <span class="sf-os-trust-text">Secure Order</span>
            </div>
            <div class="sf-os-trust-item">
                <i class="bi bi-truck sf-os-trust-icon"></i>
                <span class="sf-os-trust-text">Fast Shipping</span>
            </div>
            <div class="sf-os-trust-item">
                <i class="bi bi-arrow-return-left sf-os-trust-icon"></i>
                <span class="sf-os-trust-text">Easy Returns</span>
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:16px;">
            <div class="sf-os-card-grid">
                <div class="sf-os-card-col-left">
                    <div class="sf-os-card-label">Payment Method</div>
                    <div class="sf-os-card-val">{{ strtoupper($order->payment_method) }}</div>
                </div>
                <div class="sf-os-card-col-right">
                    <div class="sf-os-card-label">Payment Status</div>
                    <span class="sf-badge {{ $order->payment_status === 'paid' ? 'delivered' : 'processing' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        @if ($order->orderItems->isNotEmpty())
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:16px;">
            <div class="sf-os-items-header">
                <i class="bi bi-bag" style="color:var(--color-gold);"></i>
                <span class="sf-os-card-val">Items Ordered</span>
            </div>
            <div class="sf-os-items-table-wrap">
                <table class="sf-os-items-table">
                    <thead>
                        <tr class="sf-os-items-tr">
                            <th class="sf-os-items-th-left">Product</th>
                            <th class="sf-os-items-th-right">Qty</th>
                            <th class="sf-os-items-th-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->orderItems as $oi)
                        <tr class="sf-os-items-tr">
                            <td class="sf-os-items-td-left">
                                {{ $oi->product_name_snapshot }}
                                @if ($oi->variant_title_snapshot && !in_array(strtolower(trim($oi->variant_title_snapshot)), ['default', 'default title', '']))
                                    <span style="color:var(--color-text-muted);"> — {{ $oi->variant_title_snapshot }}</span>
                                @endif
                            </td>
                            <td class="sf-os-items-td-right">{{ $oi->qty }}</td>
                            <td class="sf-os-items-td-total">₹{{ number_format((float) $oi->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="sf-os-totals-wrap">
                <div class="sf-os-totals-row">
                    <span>Subtotal</span>
                    <span class="sf-os-totals-val">₹{{ number_format((float) $order->subtotal, 2) }}</span>
                </div>
                @if((float)$order->shipping_total > 0)
                <div class="sf-os-totals-row">
                    <span>Shipping</span>
                    <span class="sf-os-totals-val">₹{{ number_format((float) $order->shipping_total, 2) }}</span>
                </div>
                @endif
                @if((float)$order->discount_total > 0)
                <div class="sf-os-totals-row-discount">
                    <span>Discount</span>
                    <span>-₹{{ number_format((float) $order->discount_total, 2) }}</span>
                </div>
                @endif
                <div class="sf-os-grand-wrap">
                    <span class="sf-os-grand-val">Grand Total</span>
                    <span class="sf-os-grand-val">₹{{ number_format((float) $order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Shipping Address --}}
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:32px;">
            <div class="sf-os-items-header">
                <i class="bi bi-geo-alt" style="color:var(--color-gold);"></i>
                <span class="sf-os-card-val">Shipping Address</span>
            </div>
            <div class="sf-os-addr-wrap">
                <strong class="sf-os-addr-name">{{ $order->customer_name }}</strong><br>
                <span class="sf-os-addr-details">{{ $order->address_line1 }}<br>
                @if($order->address_line2){{ $order->address_line2 }}<br>@endif
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->country ?? 'India' }}<br>
                <i class="bi bi-telephone" style="color:var(--color-gold);font-size:11px;"></i> {{ $order->phone }}</span>
            </div>
        </div>

        {{-- CTA Buttons --}}
        <div class="sf-os-actions">
            <a href="{{ route('home') }}" class="sf-hero-cta" style="display:inline-block;text-decoration:none;margin-bottom:12px;">
                <i class="bi bi-bag" style="margin-right:6px;"></i> Continue Shopping
            </a>
            @auth
            <div style="margin-top:12px;">
                <a href="{{ route('account.orders.show', $order) }}" class="sf-os-action-track">
                    <i class="bi bi-receipt" style="margin-right:4px;"></i> Track Your Order
                </a>
            </div>
            @endauth
        </div>

    </div>
</section>
@endsection
