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
<section class="sf-section" style="padding:60px 0;background:var(--color-bg-primary);">
    <div class="sf-container" style="max-width:720px;">

        {{-- Success Header --}}
        <div style="text-align:center;margin-bottom:32px;">
            <div style="width:80px;height:80px;border-radius:50%;background:rgba(39,103,73,0.15);display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:var(--color-success);"></i>
            </div>
            <h1 style="color:var(--color-text-primary);font-size:28px;font-weight:500;margin-bottom:8px;text-transform:uppercase;letter-spacing:2px;">Order Confirmed!</h1>
            <p style="color:var(--color-text-secondary);font-size:14px;margin-bottom:4px;">Thank you for your order, <strong style="color:var(--color-text-primary);">{{ $order->customer_name }}</strong></p>
            <p style="color:var(--color-gold);font-size:14px;letter-spacing:1px;">Order <strong>#{{ $order->order_number }}</strong></p>
        </div>

        @if(session('account_created_email'))
            <div style="background: rgba(39,103,73,0.1); border: 1px solid var(--color-success); border-radius: var(--radius-md); padding: 16px; margin-bottom: 32px; text-align: center;">
                <i class="bi bi-person-check" style="color: var(--color-success); font-size: 20px; margin-bottom: 8px; display: block;"></i>
                <h4 style="color: var(--color-success); font-size: 16px; margin-bottom: 4px;">Account Created Successfully!</h4>
                <p style="color: var(--color-text-secondary); font-size: 13px; margin: 0;">We've sent a password reset link to <strong>{{ session('account_created_email') }}</strong> so you can track your orders easily.</p>
            </div>
        @elseif(session('account_created_phone'))
            <div style="background: rgba(201,168,76,0.1); border: 1px solid var(--color-gold); border-radius: var(--radius-md); padding: 16px; margin-bottom: 32px; text-align: center;">
                <i class="bi bi-person-check" style="color: var(--color-gold); font-size: 20px; margin-bottom: 8px; display: block;"></i>
                <h4 style="color: var(--color-text-primary); font-size: 16px; margin-bottom: 4px;">Account Created Successfully!</h4>
                <p style="color: var(--color-text-secondary); font-size: 13px; margin: 0;">An account was created for you using <strong>{{ session('account_created_phone') }}</strong>. You can use it to track your orders!</p>
            </div>
        @endif

        {{-- Trust / Reassurance Bar --}}
        <div style="display:flex;justify-content:center;gap:32px;margin-bottom:32px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-shield-check" style="color:var(--color-gold);font-size:18px;"></i>
                <span style="color:var(--color-text-secondary);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Secure Order</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-truck" style="color:var(--color-gold);font-size:18px;"></i>
                <span style="color:var(--color-text-secondary);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Fast Shipping</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="bi bi-arrow-return-left" style="color:var(--color-gold);font-size:18px;"></i>
                <span style="color:var(--color-text-secondary);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Easy Returns</span>
            </div>
        </div>

        {{-- Payment Info --}}
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:16px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;">
                <div style="padding:16px;text-align:center;border-right:1px solid var(--color-border);">
                    <div style="color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Payment Method</div>
                    <div style="color:var(--color-text-primary);font-weight:600;font-size:14px;">{{ strtoupper($order->payment_method) }}</div>
                </div>
                <div style="padding:16px;text-align:center;">
                    <div style="color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Payment Status</div>
                    <span class="sf-badge {{ $order->payment_status === 'paid' ? 'delivered' : 'processing' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        @if ($order->orderItems->isNotEmpty())
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:16px;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-bag" style="color:var(--color-gold);"></i>
                <span style="color:var(--color-text-primary);font-weight:600;font-size:14px;">Items Ordered</span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Product</th>
                            <th style="padding:10px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Qty</th>
                            <th style="padding:10px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->orderItems as $oi)
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td style="padding:12px 20px;color:var(--color-text-primary);font-size:13px;">
                                {{ $oi->product_name_snapshot }}
                                @if ($oi->variant_title_snapshot && !in_array(strtolower(trim($oi->variant_title_snapshot)), ['default', 'default title', '']))
                                    <span style="color:var(--color-text-muted);"> — {{ $oi->variant_title_snapshot }}</span>
                                @endif
                            </td>
                            <td style="padding:12px 20px;text-align:right;color:var(--color-text-secondary);font-size:13px;">{{ $oi->qty }}</td>
                            <td style="padding:12px 20px;text-align:right;color:var(--color-text-primary);font-size:13px;font-weight:500;">₹{{ number_format((float) $oi->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px;border-top:1px solid var(--color-border);">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--color-text-secondary);margin-bottom:6px;">
                    <span>Subtotal</span>
                    <span style="color:var(--color-text-primary);">₹{{ number_format((float) $order->subtotal, 2) }}</span>
                </div>
                @if((float)$order->shipping_total > 0)
                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--color-text-secondary);margin-bottom:6px;">
                    <span>Shipping</span>
                    <span style="color:var(--color-text-primary);">₹{{ number_format((float) $order->shipping_total, 2) }}</span>
                </div>
                @endif
                @if((float)$order->discount_total > 0)
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                    <span style="color:var(--color-success);">Discount</span>
                    <span style="color:var(--color-success);">-₹{{ number_format((float) $order->discount_total, 2) }}</span>
                </div>
                @endif
                <div style="border-top:1px solid var(--color-border-gold);padding-top:10px;margin-top:10px;display:flex;justify-content:space-between;">
                    <span style="color:var(--color-gold);font-weight:600;font-size:15px;">Grand Total</span>
                    <span style="color:var(--color-gold);font-weight:600;font-size:15px;">₹{{ number_format((float) $order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Shipping Address --}}
        <div class="sf-account-card" style="padding:0;overflow:hidden;margin-bottom:32px;">
            <div style="padding:14px 20px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-geo-alt" style="color:var(--color-gold);"></i>
                <span style="color:var(--color-text-primary);font-weight:600;font-size:14px;">Shipping Address</span>
            </div>
            <div style="padding:16px 20px;font-size:13px;line-height:1.8;">
                <strong style="color:var(--color-text-primary);">{{ $order->customer_name }}</strong><br>
                <span style="color:var(--color-text-secondary);">{{ $order->address_line1 }}<br>
                @if($order->address_line2){{ $order->address_line2 }}<br>@endif
                {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                {{ $order->country ?? 'India' }}<br>
                <i class="bi bi-telephone" style="color:var(--color-gold);font-size:11px;"></i> {{ $order->phone }}</span>
            </div>
        </div>

        {{-- CTA Buttons --}}
        <div style="text-align:center;">
            <a href="{{ route('home') }}" class="sf-hero-cta" style="display:inline-block;text-decoration:none;margin-bottom:12px;">
                <i class="bi bi-bag" style="margin-right:6px;"></i> Continue Shopping
            </a>
            @auth
            <div style="margin-top:12px;">
                <a href="{{ route('account.orders.show', $order) }}" style="color:var(--color-gold);font-size:12px;text-transform:uppercase;letter-spacing:1px;text-decoration:none;border:1px solid var(--color-gold);padding:10px 24px;border-radius:var(--radius-sm);display:inline-block;transition:var(--transition);">
                    <i class="bi bi-receipt" style="margin-right:4px;"></i> Track Your Order
                </a>
            </div>
            @endauth
        </div>

    </div>
</section>
@endsection
