@extends('layouts.storefront')

@section('title', 'Cart')

@section('content')
<section class="sf-section">
    <div class="sf-container">
        <h1 class="sf-section-title" style="margin-bottom: 32px; font-size: 24px;">Shopping Cart</h1>
        @if ($lines->isEmpty())
            <div style="text-align: center; padding: 60px 0;">
                <p style="color: var(--color-text-muted); margin-bottom: 24px;">Your cart is empty.</p>
                <a href="{{ route('home') }}" class="sf-hero-cta" style="display: inline-block;">Continue shopping</a>
            </div>
        @else
            <div class="sf-cart-layout">
                <div>
                    @foreach ($lines as $row)
                        @php
                            $item = $row['item'];
                            $variant = $row['variant'];
                            $product = $row['product'];
                        @endphp
                        <div class="sf-cart-row">
                            @if($product->primaryImage())
                                <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" class="sf-cart-thumb">
                            @else
                                <div class="sf-cart-thumb" style="background: var(--color-bg-elevated);"></div>
                            @endif
                            <div style="flex: 1;">
                                <div class="sf-cart-name">{{ $product->name }}</div>
                                <div class="sf-cart-variant">{{ $variant->title }} @if($variant->sku) · {{ $variant->sku }} @endif</div>
                                <div class="sf-cart-price" style="margin-top: 4px;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $row['unit_price'], 0) }}</div>
                            </div>
                            <div class="sf-qty">
                                <form action="{{ route('cart.items.update', $item) }}" method="post" style="display: flex;">
                                    @csrf
                                    @method('PUT')
                                    <button type="button" onclick="const inpv=this.nextElementSibling; if(inpv.value>1) {inpv.value--; this.form.submit();}"><i class="bi bi-dash"></i></button>
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1">
                                    <button type="button" onclick="const inpv=this.previousElementSibling; inpv.value++; this.form.submit();"><i class="bi bi-plus"></i></button>
                                </form>
                            </div>
                            <div class="sf-cart-price" style="font-weight: 600; width: 60px; text-align: right;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $row['line_total'], 0) }}</div>
                            <form method="POST" action="{{ route('cart.items.destroy', $item->id) }}" style="margin-left: 12px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sf-cart-remove"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
                
                <div>
                    <div class="sf-cart-summary">
                        <h2 style="color: white; font-size: 16px; margin-bottom: 24px; text-transform: uppercase;">Order Summary</h2>
                        
                        <div style="margin-bottom: 24px;">
                            @if ($errors->has('coupon'))
                                <div style="color: var(--color-error); font-size: 12px; margin-bottom: 8px;">{{ $errors->first('coupon') }}</div>
                            @endif
                            @if ($totals['coupon'])
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="color: var(--color-success); font-size: 12px;"><i class="bi bi-tag-fill"></i> Code applied: {{ $totals['coupon']->code }}</span>
                                    <form action="{{ route('cart.coupon.remove') }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--color-text-muted); font-size: 11px; text-decoration: underline;">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('cart.coupon.apply') }}" method="post" style="display: flex; gap: 8px;">
                                    @csrf
                                    <input type="text" name="code" value="{{ old('code') }}" class="sf-input" placeholder="Coupon code" style="flex:1;">
                                    <button type="submit" style="background: var(--color-bg-elevated); border: 1px solid var(--color-gold); color: var(--color-gold); border-radius: var(--radius-md); padding: 0 16px; font-size: 11px; text-transform: uppercase; font-weight: 600; cursor: pointer;">Apply</button>
                                </form>
                            @endif
                        </div>

                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--color-text-secondary); margin-bottom: 8px;">
                            <span>Subtotal</span>
                            <span>{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['subtotal'], 0) }}</span>
                        </div>
                        @if ((float) $totals['discount'] > 0)
                            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--color-success); margin-bottom: 8px;">
                                <span>Discount</span>
                                <span>−{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['discount'], 0) }}</span>
                            </div>
                        @endif
                        <p style="font-size: 11px; color: var(--color-text-muted); margin-bottom: 16px;">Shipping is calculated at checkout.</p>
                        
                        <div class="sf-cart-total">
                            <span>Estimated Total</span>
                            <span>{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['grand'], 0) }}</span>
                        </div>

                        <a href="{{ route('checkout.create') }}" class="btn-checkout" style="display: flex; align-items: center; justify-content: center;">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@if (session('analytics_add_to_cart'))
@push('scripts')
@php $a = session('analytics_add_to_cart'); @endphp
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });
dataLayer.push({
    event: 'add_to_cart',
    ecommerce: {
        currency: @json($a['currency']),
        value: {{ $a['value'] }},
        items: @json($a['items'])
    }
});
@if (config('commerce.meta.pixel_id'))
if (typeof fbq === 'function') {
    fbq('track', 'AddToCart', {
        value: {{ $a['value'] }},
        currency: @json($a['currency']),
        content_ids: [@json($a['items'][0]['item_id'] ?? '')],
        content_type: 'product'
    });
}
@endif
</script>
@endpush
@endif
