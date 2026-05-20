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
            {{-- Sleek Free Shipping Banner --}}
            <div style="background: rgba(40, 167, 69, 0.05); border: 1px solid rgba(40, 167, 69, 0.2); border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; position: relative; overflow: hidden;">
                @if($remaining > 0)
                    <div style="font-weight: 600; font-size: 13px; color: var(--color-text-primary); margin-bottom: 8px; text-align: center;">
                        Add <span style="color: var(--color-gold);">{{ config('commerce.currency_symbol', '₹') }}{{ number_format($remaining, 0) }}</span> more to unlock <strong style="color: var(--color-success);">FREE Shipping!</strong>
                    </div>
                @else
                    <div style="font-weight: 600; font-size: 13px; color: var(--color-success); margin-bottom: 8px; text-align: center;">
                        <i class="bi bi-truck"></i> You've unlocked <strong>FREE Shipping!</strong> 🎉
                    </div>
                @endif
                <div style="background: rgba(40, 167, 69, 0.15); height: 4px; border-radius: 4px; overflow: hidden; width: 100%;">
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
                        <div class="sf-cart-row" style="align-items: flex-start; padding: 16px; background: #fff; border-radius: 12px; margin-bottom: 12px; border: 1px solid var(--color-border); box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                            @if($product->primaryImage())
                                <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" class="sf-cart-thumb" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            @else
                                <div class="sf-cart-thumb" style="width: 80px; height: 80px; background: var(--color-bg-elevated); border-radius: 8px;"></div>
                            @endif
                            <div style="flex: 1; margin-left: 12px; display: flex; flex-direction: column;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div style="padding-right: 8px;">
                                        <div style="font-weight: 600; font-size: 14px; color: var(--color-text-primary); line-height: 1.3;">{{ $product->name }}</div>
                                        <div style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">{{ $variant->title }} @if($variant->sku) · {{ $variant->sku }} @endif</div>
                                        <div style="font-weight: 700; font-size: 15px; margin-top: 6px; color: var(--color-gold);">
                                            @php
                                                $compareAt = $row['variant']->compare_at_price;
                                                $unitPrice = (float) $row['unit_price'];
                                                $showMrp = $compareAt && (float) $compareAt > $unitPrice;
                                            @endphp
                                            {{ config('commerce.currency_symbol', '₹') }}{{ number_format($unitPrice, 0) }}
                                            @if($showMrp)
                                                <span style="text-decoration: line-through; color: #a0a0a0; font-weight: 400; font-size: 12px; margin-left: 4px;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $compareAt, 0) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('cart.items.destroy', $item->id) }}" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; padding: 4px; color: #ff4d4f; font-size: 18px; line-height: 1; cursor: pointer;"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                                <div style="display: flex; justify-content: flex-start; align-items: center; margin-top: 16px;">
                                    <form action="{{ route('cart.items.update', $item) }}" method="post" style="display: flex; border: 1px solid var(--color-border); border-radius: 50px; overflow: hidden; height: 32px; width: 100px;">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" onclick="const inpv=this.nextElementSibling; if(inpv.value>1) {inpv.value--; this.form.submit();}" style="flex: 1; background: var(--color-bg-elevated); border: none; font-size: 18px; line-height: 1; color: var(--color-text-primary);"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="qty" value="{{ $item->qty }}" min="1" readonly style="flex: 1; width: 100%; border: none; text-align: center; font-size: 14px; font-weight: 600; background: #fff; outline: none; -moz-appearance: textfield; padding: 0;">
                                        <button type="button" onclick="const inpv=this.previousElementSibling; inpv.value++; this.form.submit();" style="flex: 1; background: var(--color-bg-elevated); border: none; font-size: 16px; line-height: 1; color: var(--color-text-primary);"><i class="bi bi-plus"></i></button>
                                    </form>
                                </div>
                            </div>
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
                                                    <span style="color: var(--color-gold); font-size: 13px; font-weight: 600;">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float)$defaultVariant->price_retail, 0) }}</span>
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
                </div>
            </div>

            {{-- Mobile Premium Sticky Checkout CTA --}}
            <div class="d-md-none sf-mobile-checkout-sticky">
                <a href="{{ route('checkout.create') }}" class="sf-mobile-checkout-btn" style="text-decoration: none;">
                    <span style="font-size: 16px;">₹{{ number_format((float) $totals['grand'], 0) }}</span>
                    <span style="display: flex; align-items: center; gap: 4px;"><span class="btn-text">Proceed</span> <i class="bi bi-chevron-right"></i></span>
                </a>
            </div>

        @endif
    </div>
</section>

<style>
/* Hide Bottom Nav on Cart for zero-distraction & fix sticky overlap */
.sf-bottom-nav { display: none !important; }

/* Mobile Premium Sticky Button Styling (Shared with Checkout) */
.sf-mobile-checkout-sticky {
    position: fixed; 
    bottom: 0; 
    left: 0; 
    right: 0; 
    background: #ffffff; 
    padding: 12px 16px;
    padding-bottom: max(16px, env(safe-area-inset-bottom));
    border-top: 1px solid rgba(0,0,0,0.05); 
    z-index: 1040; 
    box-shadow: 0 -10px 30px rgba(0,0,0,0.08);
}
.sf-mobile-checkout-btn {
    position: relative;
    width: 100%; 
    height: 54px; 
    background: linear-gradient(135deg, #f7d570 0%, #c9a84c 100%);
    color: #000000; 
    border: none; 
    border-radius: 12px; 
    font-weight: 800; 
    text-transform: uppercase; 
    letter-spacing: 0.5px; 
    font-size: 14px;
    box-shadow: 0 8px 20px rgba(201,168,76,0.4);
    overflow: hidden;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    transition: transform 0.1s;
}
.sf-mobile-checkout-btn:active { transform: scale(0.98); }
.sf-mobile-checkout-btn::after {
    content: '';
    position: absolute;
    top: 0; left: -100%; width: 50%; height: 100%;
    background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    transform: skewX(-25deg);
    animation: btnShimmer 3s infinite;
}
@keyframes btnShimmer {
    0% { left: -100%; }
    20% { left: 200%; }
    100% { left: 200%; }
}

/* Responsive Utility Fallbacks */
@media (min-width: 768px) {
    .d-md-none { display: none !important; }
}
@media (max-width: 767px) {
    .sf-container { padding-bottom: 80px; }
}
</style>
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

@push('scripts')
<script>
    if (window.Store) {
        Store.track('cart_view');
    }
</script>
@endpush
