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
        @section('meta_description', $desc)
    @endif
    @php
        $pv = $product->variants->where('is_active', true)->sortBy('price_retail')->first();
        $imgUrl = $product->primaryImage() ? url($product->primaryImage()->url()) : null;
        $offerPrice = $pv ? number_format((float) ($variantPrices[$pv->id]['display'] ?? $pv->price_retail), 2, '.', '') : null;
    @endphp
    @if ($imgUrl)
        @section('og_image', $imgUrl)
        <link rel="preload" as="image" href="{{ $imgUrl }}">
    @endif
    @php
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

        // Breadcrumb Schema
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home', [], true)
                ]
            ]
        ];
        $pos = 2;
        if ($product->category) {
            $breadcrumbSchema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $product->category->name,
                'item' => route('category.show', $product->category, true)
            ];
        }
        $breadcrumbSchema['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => $product->name,
            'item' => route('product.show', $product, true)
        ];

        // FAQ Schema
        $faqSchema = null;
        if (!empty($product->meta['faq']) && is_array($product->meta['faq'])) {
            $faqItems = [];
            foreach ($product->meta['faq'] as $faq) {
                if (!empty($faq['q']) && !empty($faq['a'])) {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => $faq['q'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags((string)$faq['a'])
                        ]
                    ];
                }
            }
            if (count($faqItems) > 0) {
                $faqSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqItems
                ];
            }
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @if($faqSchema)
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush

@section('content')
<section class="sf-section">
    <div class="sf-container">
        {{-- Breadcrumb --}}
        <nav class="pdp-breadcrumb">
            <a href="{{ route('home') }}">Home</a> 
            @if ($product->category)
                <span class="sep">/</span> 
                <a href="{{ route('category.show', $product->category) }}">{{ $product->category->name }}</a>
            @endif
            <span class="sep">/</span> 
            <span class="curr">{{ $product->name }}</span>
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
                        @if($section['enabled'] && in_array($section['key'], ['title_price', 'variants', 'buy_buttons', 'trust_badges']))
                            @include('storefront.product.sections.' . $section['key'], [
                                'product' => $product,
                                'selectedVariant' => $selectedVariant
                            ])

                            {{-- CRO Boost: Hook & Promise (After Title/Price) --}}
                            @if($section['key'] === 'title_price')
                                @if(!empty($product->meta['problem_hook']) || !empty($product->meta['result_promise']))
                                    <div class="pdp-hook-block">
                                        @if(!empty($product->meta['problem_hook']))
                                            <p class="pdp-hook-problem">{{ $product->meta['problem_hook'] }}</p>
                                        @endif
                                        @if(!empty($product->meta['result_promise']))
                                            <p class="pdp-hook-promise"><i class="bi bi-magic pdp-hook-icon"></i> {{ $product->meta['result_promise'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- CRO Boost: Trust Proof (After Buy Buttons) --}}
                            @if($section['key'] === 'buy_buttons')
                                @if(!empty($product->meta['trust_proof']))
                                    <div class="pdp-trust-block">
                                        <div class="pdp-trust-title"><i class="bi bi-shield-check me-1"></i> Trust Proof</div>
                                        <p class="pdp-trust-desc">{!! nl2br(e($product->meta['trust_proof'])) !!}</p>
                                    </div>
                                @endif
                            @endif
                        @endif
                    @endforeach
                </form>
            </div>
        </div>

        {{-- ── Full Width Bottom Sections ───────────────────────── --}}
        <div class="mt-5">
            @foreach($layoutSections as $section)
                @if($section['enabled'] && in_array($section['key'], ['description', 'specs', 'faq', 'reviews', 'recently_viewed', 'frequently_bought', 'related']))
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
<div class="sf-mobile-sticky sf-sticky-cart-bar">
    @php
        $defVarSticky = $product->variants->where('is_active', true)->first();
        $stickyPrice = $defVarSticky ? ((float) ($variantPrices[$defVarSticky->id]['display'] ?? $defVarSticky->price_retail)) : 0;
        $stickyCompare = $defVarSticky ? ((float) ($variantPrices[$defVarSticky->id]['compare'] ?? $defVarSticky->compare_at_price)) : 0;
    @endphp
    
    <div class="sf-sticky-cart-price-col">
        <div class="sf-sticky-cart-label">Total Price</div>
        <div class="sf-sticky-cart-prices">
            <span class="sf-sticky-cart-price" id="stickyPrice">₹{{ number_format($stickyPrice) }}</span>
            <span class="sf-sticky-cart-compare" id="stickyCompare" style="display:{{ $stickyCompare > $stickyPrice ? 'inline' : 'none' }};">₹{{ number_format($stickyCompare) }}</span>
        </div>
    </div>
    
    <button onclick="document.getElementById('redirectInput').value='checkout'; document.getElementById('productForm').submit();" class="sf-sticky-cart-buy-btn">
        Buy Now
    </button>
    
    <button id="stickyAddToCartBtn" onclick="document.getElementById('redirectInput').value=''; const form = document.getElementById('productForm'); if (form.requestSubmit) form.requestSubmit(); else form.submit();" class="sf-sticky-cart-add-btn" title="Add to Cart">
        <i class="bi bi-cart-plus fs-5"></i>
    </button>
</div>

{{-- ── Social Proof Toast ────────────────────── --}}
@php
    $spEnabled = !empty($socialProofData);
    $spInterval = $product->meta['social_proof_interval'] ?? app(\App\Services\SettingsService::class)->get('conversion_copy.social_proof_interval', 8000);
@endphp
@if($spEnabled)
<div id="sfSocialProof" class="sf-toast-social-proof">
    <i class="bi bi-check-circle-fill text-success fs-5"></i>
    <div>
        <div class="sf-toast-sp-title" id="spText">Someone just bought this!</div>
        <div class="sf-toast-sp-time" id="spTime">recently</div>
    </div>
</div>
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

    // Form submit listener for analytics & AJAX side cart is handled globally in store.js
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
if (window.Store) {
    Store.track('product_view', {
        product_id: {{ $product->id }},
        variant_id: {{ $v0?->id ?? 'null' }},
        value: {{ (float) $vprice }}
    });
}

@if($spEnabled)
(function() {
    // Real buyer data from server (product + category orders)
    var spData = @json($socialProofData);
    var spToast = document.getElementById('sfSocialProof');
    var spText = document.getElementById('spText');
    var spTime = document.getElementById('spTime');
    var spIdx = 0;
    
    if (!spToast || !spData || spData.length === 0) return;
    
    function showSocialProof() {
        var entry = spData[spIdx % spData.length];
        spIdx++;
        spText.textContent = entry.name + ' just bought this!';
        spTime.textContent = entry.time_ago;
        
        spToast.classList.add('show');
        setTimeout(function() {
            spToast.classList.remove('show');
        }, 4000);
    }
    
    // Randomize starting position so repeat visitors see fresh entries
    spIdx = Math.floor(Math.random() * spData.length);
    
    setTimeout(function() {
        showSocialProof();
        setInterval(showSocialProof, {{ (int) $spInterval }});
    }, 3000);
})();
@endif
</script>
@endpush
