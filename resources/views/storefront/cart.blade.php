@extends('layouts.storefront')

@section('title', 'Shopping Cart')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush
@section('content')
<section class="sf-section">
    <div class="sf-container">
        <h1 class="sf-section-title" style="margin-bottom: 32px; font-size: 24px;">Shopping Cart</h1>
        @if ($lines->isEmpty())
            <div style="text-align: center; padding: 60px 0;">
                <p style="color: var(--color-text-muted); margin-bottom: 24px;">Your cart is empty.</p>
                <a href="{{ route('home') }}" class="sf-hero-cta" style="display: inline-block;">Continue shopping</a>
            </div>
            
            {{-- Empty Cart: Best Collections --}}
            @php
                $emptyCartUpsells = \App\Models\Product::where('status', \App\Models\Product::STATUS_ACTIVE)->where('is_featured', true)->take(4)->get();
            @endphp
            @if($emptyCartUpsells->isNotEmpty())
                <div style="margin-top: 60px;">
                    <h3 style="font-size: 18px; margin-bottom: 24px; text-transform: uppercase;">Discover Our Bestsellers</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;">
                        @foreach($emptyCartUpsells as $upsell)
                            <a href="{{ route('product.show', $upsell->slug) }}" style="display: block; text-decoration: none; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px; background: var(--color-bg-surface);">
                                <img src="{{ $upsell->primaryImage() ? asset('storage/'.$upsell->primaryImage()->path) : 'https://placehold.co/150' }}" style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 12px;">
                                <div style="color: var(--color-text-primary); font-size: 13px; font-weight: 600;">{{ Str::limit($upsell->name, 35) }}</div>
                                <div style="color: var(--color-gold); font-size: 13px; font-weight: 500; margin-top: 4px;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format($upsell->price ?? 0, 0) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            @php
                $threshold = (float) app(\App\Services\SettingsService::class)->get('shipping.free_threshold', 499);
                $subtotal = (float) $totals['subtotal'];
                $percentage = $threshold > 0 ? min(100, ($subtotal / $threshold) * 100) : 100;
                $remaining = max(0, $threshold - $subtotal);
            @endphp
            <div style="background: var(--color-bg-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 24px;">
                @if($remaining > 0)
                    <div style="font-weight: 600; font-size: 14px; color: var(--color-text-primary); margin-bottom: 12px; text-align: center;">
                        You are <span style="color: var(--color-gold);">{{ config('commerce.currency_symbol', '₹') }}{{ number_format($remaining, 0) }}</span> away from <strong style="color: var(--color-success);">FREE Shipping</strong>!
                    </div>
                @else
                    <div style="font-weight: 600; font-size: 14px; color: var(--color-success); margin-bottom: 12px; text-align: center;">
                        <i class="bi bi-truck"></i> Congratulations! You unlocked <strong>FREE Shipping</strong> 🎉
                    </div>
                @endif
                <div style="background: var(--color-bg-elevated); height: 8px; border-radius: 4px; overflow: hidden; width: 100%;">
                    <div style="height: 100%; background: var(--color-success); width: {{ $percentage }}%; transition: width 0.5s ease-in-out;"></div>
                </div>
            </div>

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
                    
                    {{-- Frequently Bought Together (Same Category Logic) --}}
                    @php
                        $cartProductIds = $lines->pluck('product.id')->toArray();
                        $categoryIds = $lines->pluck('product.category_id')->filter()->unique()->toArray();
                        $upsells = collect();
                        if (!empty($categoryIds)) {
                            $upsells = \App\Models\Product::whereIn('category_id', $categoryIds)
                                                          ->whereNotIn('id', $cartProductIds)
                                                          ->where('status', \App\Models\Product::STATUS_ACTIVE)
                                                          ->inRandomOrder()
                                                          ->take(3)
                                                          ->get();
                        }
                    @endphp
                    @if($upsells->isNotEmpty())
                        <div style="margin-top: 40px; padding-top: 32px; border-top: 1px solid var(--color-border);">
                            <h3 style="font-size: 16px; margin-bottom: 20px; text-transform: uppercase;">Frequently Bought Together</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px;">
                                @foreach($upsells as $upsell)
                                    <div style="border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: 12px; background: var(--color-bg-surface); display: flex; flex-direction: column;">
                                        <a href="{{ route('product.show', $upsell->slug) }}" style="display: block; text-decoration: none; flex: 1;">
                                            <img src="{{ $upsell->primaryImage() ? asset('storage/'.$upsell->primaryImage()->path) : 'https://placehold.co/150' }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 8px;">
                                            <div style="color: var(--color-text-primary); font-size: 12px; font-weight: 500; line-height: 1.4;">{{ Str::limit($upsell->name, 30) }}</div>
                                        </a>
                                        <form action="{{ route('cart.items.store') }}" method="post" style="margin-top: 12px;">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $upsell->id }}">
                                            @php $defaultVariant = $upsell->variants()->first(); @endphp
                                            @if($defaultVariant)
                                                <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">
                                                <input type="hidden" name="qty" value="1">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="color: var(--color-gold); font-size: 13px; font-weight: 600;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float)$defaultVariant->price, 0) }}</span>
                                                    <button type="submit" style="background: none; border: none; color: var(--color-gold); font-size: 18px; padding: 0; cursor: pointer;"><i class="bi bi-plus-circle-fill"></i></button>
                                                </div>
                                            @endif
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <div>
                    <div class="sf-cart-summary">
                        <h2 style="color: var(--color-text-primary); font-size: 16px; margin-bottom: 24px; text-transform: uppercase;">Order Summary</h2>
                        
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
if (typeof fbq === 'function') {
    fbq('track', 'AddToCart', {
        value: {{ $a['value'] }},
        currency: @json($a['currency']),
        content_ids: [@json($a['items'][0]['item_id'] ?? '')],
        content_type: 'product'
    });
}
</script>
@endpush
@endif
