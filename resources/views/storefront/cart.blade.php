@extends('layouts.storefront')

@section('title', 'Shopping Cart')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush
@section('content')
<section class="sf-section">
    <div class="sf-container">
        <h1 class="sf-cart-page-title">Shopping Cart</h1>
        @if ($lines->isEmpty())
            <div class="sf-empty-cart-wrap">
                <p class="sf-empty-cart-msg">Your cart is empty.</p>
                <a href="{{ route('home') }}" class="sf-hero-cta">Continue shopping</a>
            </div>
            
            {{-- Empty Cart: Best Collections --}}
            @php
                $emptyCartUpsells = \App\Models\Product::where('status', \App\Models\Product::STATUS_ACTIVE)->where('is_featured', true)->take(4)->get();
            @endphp
            @if($emptyCartUpsells->isNotEmpty())
                <div class="sf-empty-cart-upsells-wrap">
                    <h3 class="sf-empty-cart-upsells-title">Discover Our Bestsellers</h3>
                    <div class="sf-cart-upsell-grid">
                        @foreach($emptyCartUpsells as $upsell)
                            <a href="{{ route('product.show', $upsell->slug) }}" class="sf-cart-upsell-card">
                                <img src="{{ $upsell->primaryImage() ? asset('storage/'.$upsell->primaryImage()->path) : 'https://placehold.co/150' }}" class="sf-cart-upsell-img">
                                <div class="sf-cart-upsell-name">{{ Str::limit($upsell->name, 35) }}</div>
                                <div class="sf-cart-upsell-price">{{ config('commerce.currency_symbol', '₹') }}{{ number_format($upsell->price ?? 0, 0) }}</div>
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
            <div class="sf-free-shipping-banner">
                @if($remaining > 0)
                    <div class="sf-fs-msg sf-fs-msg-pending">
                        Add <span class="sf-fs-amount">{{ config('commerce.currency_symbol', '₹') }}{{ number_format($remaining, 0) }}</span> more to unlock <strong class="sf-fs-strong">FREE Shipping!</strong>
                    </div>
                @else
                    <div class="sf-fs-msg sf-fs-msg-success">
                        <i class="bi bi-truck"></i> You've unlocked <strong>FREE Shipping!</strong> 🎉
                    </div>
                @endif
                <div class="sf-fs-track-bg">
                    <div class="sf-fs-track-fill" style="width: {{ $percentage }}%;"></div>
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
                        <div class="sf-cart-row sf-cart-row-inner">
                            @if($product->primaryImage())
                                <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" class="sf-cart-thumb sf-cart-thumb-img">
                            @else
                                <div class="sf-cart-thumb sf-cart-thumb-ph"></div>
                            @endif
                            <div class="sf-cart-details-col">
                                <div class="sf-cart-row-header">
                                    <div style="padding-right: 8px;">
                                        <div class="sf-cart-item-name">{{ $product->name }}</div>
                                        <div class="sf-cart-item-meta">{{ $variant->title }} @if($variant->sku) · {{ $variant->sku }} @endif</div>
                                        <div class="sf-cart-item-price-wrap">
                                            @php
                                                $compareAt = $row['variant']->compare_at_price;
                                                $unitPrice = (float) $row['unit_price'];
                                                $showMrp = $compareAt && (float) $compareAt > $unitPrice;
                                            @endphp
                                            {{ config('commerce.currency_symbol', '₹') }}{{ number_format($unitPrice, 0) }}
                                            @if($showMrp)
                                                <span class="sf-cart-item-mrp">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $compareAt, 0) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('cart.items.destroy', $item->id) }}" class="sf-cart-item-remove-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sf-cart-item-remove-btn"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                                <div class="sf-cart-qty-row">
                                    <form action="{{ route('cart.items.update', $item) }}" method="post" class="sf-cart-qty-form">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" onclick="const inpv=this.nextElementSibling; if(inpv.value>1) {inpv.value--; this.form.submit();}" class="sf-cart-qty-btn"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="qty" value="{{ $item->qty }}" min="1" readonly class="sf-cart-qty-input">
                                        <button type="button" onclick="const inpv=this.previousElementSibling; inpv.value++; this.form.submit();" class="sf-cart-qty-btn"><i class="bi bi-plus"></i></button>
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
                        <div class="sf-cart-fbt-wrap">
                            <h3 class="sf-cart-fbt-title">Frequently Bought Together</h3>
                            <div class="sf-cart-fbt-grid">
                                @foreach($upsells as $upsell)
                                    <div class="sf-cart-fbt-card">
                                        <a href="{{ route('product.show', $upsell->slug) }}" class="sf-cart-fbt-link">
                                            <img src="{{ $upsell->primaryImage() ? asset('storage/'.$upsell->primaryImage()->path) : 'https://placehold.co/150' }}" class="sf-cart-fbt-img">
                                            <div class="sf-cart-fbt-name">{{ Str::limit($upsell->name, 30) }}</div>
                                        </a>
                                        <form action="{{ route('cart.items.store') }}" method="post" class="sf-cart-fbt-form">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $upsell->id }}">
                                            @php $defaultVariant = $upsell->variants()->first(); @endphp
                                            @if($defaultVariant)
                                                <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">
                                                <input type="hidden" name="qty" value="1">
                                                <div class="sf-cart-fbt-action">
                                                    <span class="sf-cart-fbt-price">{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float)$defaultVariant->price_retail, 0) }}</span>
                                                    <button type="submit" class="sf-cart-fbt-add-btn"><i class="bi bi-plus-circle-fill"></i></button>
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
                        <h2 class="sf-cart-summary-title">Order Summary</h2>
                        
                        <div class="sf-cart-coupon-wrap">
                            @if ($errors->has('coupon'))
                                <div class="sf-cart-coupon-err">{{ $errors->first('coupon') }}</div>
                            @endif
                            @if ($totals['coupon'])
                                <div class="sf-cart-coupon-applied">
                                    <span class="sf-cart-coupon-success-text"><i class="bi bi-tag-fill"></i> Code applied: {{ $totals['coupon']->code }}</span>
                                    <form action="{{ route('cart.coupon.remove') }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sf-cart-coupon-remove-btn">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('cart.coupon.apply') }}" method="post" class="sf-cart-coupon-form">
                                    @csrf
                                    <input type="text" name="code" value="{{ old('code') }}" class="sf-input sf-cart-coupon-input" placeholder="Coupon code">
                                    <button type="submit" class="sf-cart-coupon-btn">Apply</button>
                                </form>
                            @endif
                        </div>

                        <div class="sf-cart-summary-row">
                            <span>Subtotal</span>
                            <span>{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['subtotal'], 0) }}</span>
                        </div>
                        @if ((float) $totals['discount'] > 0)
                            <div class="sf-cart-summary-discount">
                                <span>Discount</span>
                                <span>−{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['discount'], 0) }}</span>
                            </div>
                        @endif
                        <p class="sf-cart-summary-note">Shipping is calculated at checkout.</p>
                        
                        <div class="sf-cart-total">
                            <span>Estimated Total</span>
                            <span>{{ config('commerce.currency_symbol', '₹') }}{{ number_format((float) $totals['grand'], 0) }}</span>
                        </div>

                        <a href="{{ route('checkout.create') }}" class="btn-checkout sf-cart-checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Premium Sticky Checkout CTA --}}
            <div class="d-md-none sf-mobile-checkout-sticky">
                <a href="{{ route('checkout.create') }}" class="sf-mobile-checkout-btn">
                    <span class="sf-cart-item-price-wrap" style="margin: 0;">₹{{ number_format((float) $totals['grand'], 0) }}</span>
                    <span style="display: flex; align-items: center; gap: 4px;"><span class="btn-text">Proceed</span> <i class="bi bi-chevron-right"></i></span>
                </a>
            </div>

        @endif
    </div>
</section>

<style>
/* Hide Bottom Nav on Cart for zero-distraction & fix sticky overlap */
.sf-bottom-nav { display: none !important; }


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
