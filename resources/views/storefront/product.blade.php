@extends('layouts.storefront')

@section('title', $product->seo_title ?: $product->name)
@section('og_type', 'product')

@push('meta')
    <link rel="canonical" href="{{ route('product.show', $product, true) }}">
    @php
        $desc = $product->seo_description ?: \Illuminate\Support\Str::limit(strip_tags(html_entity_decode((string) ($product->short_description ?: $product->description))), 160);
    @endphp
    @if ($desc)
        <meta name="description" content="{{ $desc }}">
    @endif
    @php
        $pv = $product->variants->where('is_active', true)->sortBy('price_retail')->first();
        $imgUrl = $product->primaryImage() ? url($product->primaryImage()->url()) : null;
        $offerPrice = $pv ? number_format((float) ($variantPrices[$pv->id]['display'] ?? $pv->price_retail), 2, '.', '') : null;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $pv?->sku ?? $product->sku,
            'description' => strip_tags((string) ($product->short_description ?: $product->description)),
        ];
        if ($imgUrl) {
            $schema['image'] = [$imgUrl];
        }
        if ($offerPrice) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => route('product.show', $product, true),
                'priceCurrency' => config('commerce.currency', 'INR'),
                'price' => $offerPrice,
                'availability' => ($pv && (! $pv->track_inventory || $pv->stock_qty > 0))
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ];
        }
        if ($reviewCount > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1),
                'reviewCount' => $reviewCount,
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="sf-section">
    <div class="sf-container">
        {{-- Breadcrumb --}}
        <nav style="margin-bottom: 32px; font-size: 13px; color: var(--color-text-muted);">
            <a href="{{ route('home') }}" style="color: var(--color-text-secondary);">Home</a> 
            @if ($product->category)
                <span style="margin: 0 8px;">/</span> 
                <a href="{{ route('category.show', $product->category) }}" style="color: var(--color-text-secondary);">{{ $product->category->name }}</a>
            @endif
            <span style="margin: 0 8px;">/</span> 
            <span style="color: var(--color-gold);">{{ $product->name }}</span>
        </nav>

        <div class="sf-pdp">
            {{-- ── Left Column: Media Gallery ───────────────────── --}}
            <div>
                @include('storefront.product.sections.gallery', [
                    'product' => $product,
                    'selectedVariant' => $selectedVariant
                ])
            </div>

            {{-- ── Right Column: Product Information ────────────── --}}
            <div>
                <form method="post" action="{{ route('cart.items.store') }}" id="productForm">
                    @csrf
                    <input type="hidden" name="redirect" id="redirectInput" value="">
                    
                    @foreach($layoutSections as $section)
                        @if($section['enabled'] && in_array($section['key'], ['title_price', 'variants', 'buy_buttons', 'trust_badges', 'description', 'specs', 'faq']))
                            @include('storefront.product.sections.' . $section['key'], [
                                'product' => $product,
                                'selectedVariant' => $selectedVariant
                            ])
                        @endif
                    @endforeach
                </form>
            </div>
        </div>

        {{-- ── Full Width Bottom Sections ───────────────────────── --}}
        <div class="mt-5">
            @foreach($layoutSections as $section)
                @if($section['enabled'] && in_array($section['key'], ['reviews', 'recently_viewed', 'frequently_bought', 'related']))
                    @include('storefront.product.sections.' . $section['key'], [
                        'product' => $product,
                        'selectedVariant' => $selectedVariant
                    ])
                @endif
            @endforeach
        </div>
    </div>
</section>

{{-- ── Sticky Mobile Cart Bar ─────────────────────────────── --}}
<div class="sf-mobile-sticky">
    @php
        $defVarSticky = $product->variants->where('is_active', true)->first();
        $stickyPrice = $defVarSticky ? ((float) ($variantPrices[$defVarSticky->id]['display'] ?? $defVarSticky->price_retail)) : 0;
        $stickyCompare = $defVarSticky ? ((float) ($variantPrices[$defVarSticky->id]['compare'] ?? $defVarSticky->compare_at_price)) : 0;
    @endphp
    <div style="flex: 1; display: flex; align-items: center; gap: 12px;">
        @if($product->primaryImage())
            <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
        @endif
        <div style="display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; align-items: baseline; gap: 8px;">
                <span style="color: var(--color-gold); font-weight: 600; font-size: 14px;" id="stickyPrice">₹{{ number_format($stickyPrice) }}</span>
                <span style="color: var(--color-text-muted); text-decoration: line-through; font-size: 11px;" id="stickyCompare" style="display:{{ $stickyCompare > $stickyPrice ? 'inline' : 'none' }};">₹{{ number_format($stickyCompare) }}</span>
            </div>
            <div style="color: var(--color-text-secondary); font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $product->name }} <span id="stickyVariant"></span></div>
        </div>
    </div>
    <button onclick="document.getElementById('redirectInput').value='checkout'; document.getElementById('productForm').submit();" style="background: var(--color-gold); color: #0a0a0a; border: none; height: 40px; padding: 0 20px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
        Buy Now
    </button>
    {{-- Fix #10: Add to Cart icon button in sticky bar --}}
    <button id="stickyAddToCartBtn" onclick="document.getElementById('redirectInput').value=''; document.getElementById('productForm').submit();" style="background: transparent; border: 1px solid var(--color-gold); color: var(--color-gold); width: 40px; height: 40px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;" title="Add to Cart">
        <i class="bi bi-bag-plus" style="font-size: 16px;"></i>
    </button>
</div>

{{-- ── Social Proof Toast ──────────────────────────────── --}}
@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy', []);
    $spEnabled = $product->meta['show_social_proof'] ?? ($copy['social_proof_enabled'] ?? true);
    $spInterval = $product->meta['social_proof_interval'] ?? ($copy['social_proof_interval'] ?? 8000);
@endphp
@if($spEnabled)
<div id="sfSocialProof" style="position: fixed; bottom: 80px; left: 16px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; display: flex; align-items: center; gap: 12px; transform: translateY(100px); opacity: 0; transition: all 0.3s ease; z-index: 1050; box-shadow: 0 4px 12px rgba(0,0,0,0.5);">
    <i class="bi bi-check-circle-fill" style="color: var(--color-success); font-size: 20px;"></i>
    <div>
        <div style="color: var(--color-text-primary); font-size: 13px; font-weight: 500;" id="spText">Someone just bought this!</div>
        <div style="color: var(--color-text-muted); font-size: 11px;"><span id="spTime">2</span> minutes ago</div>
    </div>
</div>
<style>
#sfSocialProof.show { transform: translateY(0) !important; opacity: 1 !important; }
</style>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var variantBtns = document.querySelectorAll('.sf-variant-btn');
    var hiddenInput = document.getElementById('hidden_variant_id');
    var priceLabel = document.getElementById('priceLabel');
    var compareLabel = document.getElementById('compareLabel');
    var savingsBadge = document.getElementById('savingsBadge');
    var savingsAmount = document.getElementById('savingsAmount');
    var stickyPrice = document.getElementById('stickyPrice');
    var addToCartBtn = document.getElementById('addToCartBtn');
    var buyNowBtn = document.getElementById('buyNowBtn');

    function fmt(n) { return '\u20b9' + parseFloat(n).toLocaleString('en-IN', {minimumFractionDigits: 0}); }

    function refreshUi(btn) {
        if (!btn) return;
        
        for (var i = 0; i < variantBtns.length; i++) { variantBtns[i].classList.remove('active'); }
        btn.classList.add('active');
        hiddenInput.value = btn.dataset.id;
        
        var p = parseFloat(btn.dataset.price);
        var c = parseFloat(btn.dataset.compare) || 0;
        var stock = parseInt(btn.dataset.stock);
        var track = btn.dataset.track === '1';
        var vname = btn.dataset.name || '';
        var vimg = btn.dataset.img || '';

        if (vimg) {
            var mainImg = document.querySelector('.main-img');
            if (mainImg) {
                mainImg.src = vimg;
                var thumbs = document.querySelectorAll('.sf-pdp-thumbs img');
                for (var ti = 0; ti < thumbs.length; ti++) { thumbs[ti].classList.remove('active'); }
                // Find matching thumb manually (no Array.from/find)
                for (var tj = 0; tj < thumbs.length; tj++) {
                    if (thumbs[tj].src === vimg) { thumbs[tj].classList.add('active'); break; }
                }
            }
        }

        priceLabel.textContent = fmt(p);
        if (stickyPrice) stickyPrice.textContent = fmt(p);
        
        var stickyVariant = document.getElementById('stickyVariant');
        if (stickyVariant && vname) stickyVariant.textContent = '- ' + vname;

        var urgencyLabel = document.getElementById('urgencyLabel');
        if(urgencyLabel) {
            var msg = urgencyLabel.innerHTML;
            urgencyLabel.innerHTML = msg.replace(/\d+/, Math.min(stock, {{ $product->meta['stock_display_cap'] ?? 80 }}));
        }

        var stickyCompare = document.getElementById('stickyCompare');

        if (c > p) {
            compareLabel.style.display = 'inline';
            compareLabel.textContent = fmt(c);
            savingsBadge.style.display = 'inline';
            savingsAmount.textContent = fmt(c - p);
            // Fix #8: Update percentage discount badge on variant change
            var percentOff = Math.round((1 - p / c) * 100);
            var percentBadge = document.getElementById('discountPercentBadge');
            var percentValue = document.getElementById('discountPercentValue');
            if (percentBadge && percentValue) {
                percentBadge.style.display = 'inline-block';
                percentValue.textContent = percentOff;
            }
            if(stickyCompare) {
                stickyCompare.style.display = 'inline';
                stickyCompare.textContent = fmt(c);
            }
        } else {
            compareLabel.style.display = 'none';
            savingsBadge.style.display = 'none';
            var percentBadge = document.getElementById('discountPercentBadge');
            if (percentBadge) percentBadge.style.display = 'none';
            if(stickyCompare) stickyCompare.style.display = 'none';
        }

        if (track && stock <= 0) {
            addToCartBtn.disabled = true;
            buyNowBtn.disabled = true;
            addToCartBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Sold Out';
            buyNowBtn.innerHTML = 'Out of Stock';
        } else {
            addToCartBtn.disabled = false;
            buyNowBtn.disabled = false;
            addToCartBtn.innerHTML = '<i class="bi bi-bag-plus me-1"></i> Add to Cart';
            buyNowBtn.innerHTML = 'Buy Now';
        }

        // Emit global event
        Store.emit('variant:changed', {
            productId: {{ $product->id }},
            variantId: btn.dataset.id,
            price: p,
            comparePrice: c > p ? c : null,
            stock: stock
        });
    }

    for (var vi = 0; vi < variantBtns.length; vi++) {
        (function(b) { b.addEventListener('click', function() { refreshUi(b); }); })(variantBtns[vi]);
    }

    // Initial state setup
    var activeBtn = document.querySelector('.sf-variant-btn.active') || variantBtns[0];
    if (activeBtn) refreshUi(activeBtn);

    // Form submit listener for analytics
    var pForm = document.getElementById('productForm');
    if (pForm) {
        pForm.addEventListener('submit', function() {
            Store.emit('analytics', { 
                event: 'add_to_cart', 
                productId: {{ $product->id }}, 
                variantId: hiddenInput.value 
            });
        });
    }
})();

// Call view_item analytics
@php
    $v0 = $product->variants->first();
    $vid = $v0 ? $v0->id : null;
    $vprice = $vid && isset($variantPrices[$vid]) ? (float) $variantPrices[$vid]['display'] : 0;
@endphp

Store.emit('analytics', {
    event: 'product_viewed',
    productId: {{ $product->id }},
    category: "{{ $product->category?->name }}"
});

window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });
dataLayer.push({
    event: 'view_item',
    ecommerce: {
        currency: '{{ config('commerce.currency', 'INR') }}',
        value: {{ json_encode($vprice) }},
        items: [{
            item_id: {!! json_encode($v0?->sku ?: 'p'.$product->id) !!},
            item_name: {!! json_encode($product->name) !!},
            price: {!! json_encode($vprice) !!},
            quantity: 1
        }]
    }
});
if (typeof fbq === 'function') {
    fbq('track', 'ViewContent', {
        content_ids: [{!! json_encode($v0?->sku ?: 'p'.$product->id) !!}],
        content_type: 'product',
        value: {!! json_encode($vprice) !!},
        currency: '{{ config('commerce.currency', 'INR') }}'
    });
}

@if($spEnabled)
(function() {
    const names = ['Rahul', 'Priya', 'Amit', 'Sneha', 'Arjun', 'Meera', 'Rohit', 'Ananya', 'Kavita', 'Sanjay', 'Pooja', 'Vikram'];
    const cities = ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Surat', 'Pune', 'Jaipur', 'Ahmedabad', 'Chennai', 'Kolkata'];
    const spToast = document.getElementById('sfSocialProof');
    const spText = document.getElementById('spText');
    const spTime = document.getElementById('spTime');
    
    function showSocialProof() {
        if (!spToast) return;
        const name = names[Math.floor(Math.random() * names.length)];
        const city = cities[Math.floor(Math.random() * cities.length)];
        const mins = Math.floor(Math.random() * 40) + 2;
        
        spText.textContent = `${name} from ${city} just bought this!`;
        spTime.textContent = mins;
        
        spToast.classList.add('show');
        setTimeout(() => {
            spToast.classList.remove('show');
        }, 4000);
    }
    
    setTimeout(() => {
        showSocialProof();
        setInterval(showSocialProof, {{ (int) $spInterval }});
    }, 2000);
})();
@endif
</script>
@endpush
