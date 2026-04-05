@extends('layouts.storefront')

@section('title', $product->seo_title ?: $product->name)

@push('meta')
    <link rel="canonical" href="{{ route('product.show', $product, true) }}">
    @php
        $desc = $product->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($product->short_description ?: $product->description)), 160);
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
<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- ── Image Gallery ──────────────────────────────────── --}}
        <div class="col-md-6">
            <div class="sf-product-gallery">
                @if ($product->images->isNotEmpty())
                    <div id="pCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner rounded">
                            @foreach ($product->images as $i => $image)
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $image->alt_text ?? $product->name }}">
                                </div>
                            @endforeach
                        </div>
                        @if ($product->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#pCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#pCarousel" data-bs-slide="next"><span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span></button>
                        @endif
                    </div>
                    {{-- Thumbnails --}}
                    @if ($product->images->count() > 1)
                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            @foreach ($product->images as $i => $image)
                                <img src="{{ asset('storage/'.$image->path) }}" alt="thumb"
                                     class="rounded border {{ $i === 0 ? 'border-dark' : '' }}"
                                     style="width:60px;height:60px;object-fit:cover;cursor:pointer;opacity:{{ $i === 0 ? '1' : '.6' }};"
                                     onclick="document.querySelector('#pCarousel').querySelector('[data-bs-slide-to]') || bootstrap.Carousel.getOrCreateInstance(document.getElementById('pCarousel')).to({{ $i }}); document.querySelectorAll('.sf-product-gallery .d-flex img').forEach(t=>{t.style.opacity='.6';t.classList.remove('border-dark')}); this.style.opacity='1'; this.classList.add('border-dark');">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="min-height:400px;"><i class="bi bi-image text-muted" style="font-size:3rem;"></i></div>
                @endif
            </div>
        </div>

        {{-- ── Product Info ───────────────────────────────────── --}}
        <div class="col-md-6 sf-product-info">
            @if ($product->is_bestseller)
                <span class="badge bg-warning text-dark mb-2"><i class="bi bi-fire"></i> Bestseller</span>
            @endif

            <h1 class="product-name">{{ $product->name }}</h1>

            {{-- Rating summary --}}
            @if ($reviewCount > 0)
                <div class="d-flex align-items-center gap-2 mt-2">
                    <div class="text-warning">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}" style="font-size:.9rem;"></i>
                        @endfor
                    </div>
                    <span class="small text-muted">({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                </div>
            @endif

            @if ($product->short_description)
                <p class="text-muted mt-2">{{ $product->short_description }}</p>
            @endif

            {{-- Price --}}
            <div class="product-price-lg mt-3">
                <span id="priceLabel">₹0</span>
                <span class="compare" id="compareLabel" style="display:none;"></span>
            </div>

            {{-- Urgency --}}
            @php $firstVariant = $product->variants->first(); @endphp
            @if ($firstVariant && $firstVariant->track_inventory && $firstVariant->stock_qty <= config('commerce.pricing.low_stock_threshold', 5) && $firstVariant->stock_qty > 0)
                <div class="mt-2">
                    <span class="sf-urgency"><i class="bi bi-lightning-charge-fill"></i> Only {{ $firstVariant->stock_qty }} left in stock!</span>
                </div>
            @endif

            {{-- Add to Cart Form --}}
            <form method="post" action="{{ route('cart.items.store') }}" class="mt-4">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Variant</label>
                    <select class="form-select" name="variant_id" id="variant_id" required>
                        @foreach ($product->variants as $v)
                            @php $p = $variantPrices[$v->id]; @endphp
                            <option value="{{ $v->id }}" data-price="{{ $p['display'] }}" data-compare="{{ $p['compare'] ?? '' }}"
                                    data-stock="{{ $v->track_inventory ? $v->stock_qty : 999 }}" data-track="{{ $v->track_inventory ? '1' : '0' }}">
                                {{ $v->title }}
                                @if ($v->track_inventory && $v->stock_qty <= 0) — Sold Out @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label fw-semibold small">Quantity</label>
                        <input type="number" name="qty" value="1" min="1" max="9999" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="sf-btn-cart" id="addToCartBtn">
                    <i class="bi bi-bag-plus me-2"></i> Add to Cart
                </button>
            </form>

            {{-- Trust Badges --}}
            <div class="sf-trust-badges">
                <div class="sf-trust-badge"><i class="bi bi-check-circle-fill"></i> Genuine Product</div>
                <div class="sf-trust-badge"><i class="bi bi-check-circle-fill"></i> COD Available</div>
                <div class="sf-trust-badge"><i class="bi bi-check-circle-fill"></i> Easy Returns</div>
                <div class="sf-trust-badge"><i class="bi bi-check-circle-fill"></i> Secure Payment</div>
            </div>

            {{-- Description --}}
            @if ($product->description)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold"><i class="bi bi-card-text me-1"></i> Description</h6>
                    <div class="small text-muted">{!! $product->description !!}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Reviews Section ────────────────────────────────────── --}}
    <section class="sf-reviews">
        <h2 class="sf-section-title">Customer Reviews</h2>

        @if ($reviewCount > 0)
            <div class="sf-review-summary">
                <span class="avg-rating">{{ number_format($avgRating, 1) }}</span>
                <div>
                    <div class="stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                    <span class="count">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</span>
                </div>
            </div>

            @foreach ($reviews as $review)
                <div class="sf-review-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="review-stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="review-author">{{ $review->reviewer_name }}</span>
                            @if ($review->verified_purchase)
                                <span class="verified-badge ms-2"><i class="bi bi-patch-check-fill"></i> Verified Purchase</span>
                            @endif
                        </div>
                        <span class="review-date">{{ $review->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="review-body">{{ $review->body }}</p>
                </div>
            @endforeach
        @else
            <p class="text-muted">No reviews yet. Be the first to review!</p>
        @endif

        {{-- Review Form --}}
        <div class="card mt-4">
            <div class="card-header fw-semibold"><i class="bi bi-pencil-square me-1"></i> Write a Review</div>
            <div class="card-body">
                <form action="{{ route('reviews.store', $product) }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Your Name</label>
                            <input type="text" name="reviewer_name" class="form-control" required value="{{ auth()->user()?->name ?? old('reviewer_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" required value="{{ auth()->user()?->email ?? old('email') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Rating</label>
                            <select name="rating" class="form-select" required style="max-width:150px;">
                                <option value="5">★★★★★ (5)</option>
                                <option value="4">★★★★☆ (4)</option>
                                <option value="3">★★★☆☆ (3)</option>
                                <option value="2">★★☆☆☆ (2)</option>
                                <option value="1">★☆☆☆☆ (1)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Your Review</label>
                            <textarea name="body" class="form-control" rows="3" required placeholder="Share your experience…">{{ old('body') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark px-4"><i class="bi bi-send me-1"></i> Submit Review</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- ── Related Products ───────────────────────────────────── --}}
    @if ($relatedProducts->isNotEmpty())
        <section class="sf-section">
            <h2 class="sf-section-title">You May Also Like</h2>
            <div class="row row-cols-2 row-cols-md-4 g-3 mt-2">
                @foreach ($relatedProducts as $rp)
                    <div class="col">
                        <x-product-card :product="$rp" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

{{-- ── Sticky Mobile Cart Bar ─────────────────────────────── --}}
<div class="sf-sticky-cart">
    <div class="price" id="stickyPrice">₹0</div>
    <button onclick="document.getElementById('addToCartBtn').click();" class="btn bg-accent text-white fw-bold">
        <i class="bi bi-bag-plus"></i> Add to Cart
    </button>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('variant_id');
    const priceLabel = document.getElementById('priceLabel');
    const compareLabel = document.getElementById('compareLabel');
    const stickyPrice = document.getElementById('stickyPrice');
    const btn = document.getElementById('addToCartBtn');

    function fmt(n) { return '₹' + parseFloat(n).toLocaleString('en-IN', {minimumFractionDigits: 0}); }

    function refresh() {
        const opt = sel.selectedOptions[0];
        const p = opt.dataset.price;
        const c = opt.dataset.compare;
        const stock = parseInt(opt.dataset.stock);
        const track = opt.dataset.track === '1';

        priceLabel.textContent = fmt(p);
        if (stickyPrice) stickyPrice.textContent = fmt(p);

        if (c && parseFloat(c) > parseFloat(p)) {
            compareLabel.style.display = 'inline';
            compareLabel.textContent = fmt(c);
        } else {
            compareLabel.style.display = 'none';
        }

        if (track && stock <= 0) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Sold Out';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bag-plus me-2"></i> Add to Cart';
        }
    }
    sel.addEventListener('change', refresh);
    refresh();
})();

// GTM + Pixel tracking (preserved from original)
window.dataLayer = window.dataLayer || [];
@php
    $v0 = $product->variants->where('is_active', true)->first();
    $vid = $v0 ? $v0->id : null;
    $vprice = $vid && isset($variantPrices[$vid]) ? (float) $variantPrices[$vid]['display'] : 0;
@endphp
dataLayer.push({ ecommerce: null });
dataLayer.push({
    event: 'view_item',
    ecommerce: {
        currency: '{{ config('commerce.currency', 'INR') }}',
        value: {{ json_encode($vprice) }},
        items: [{
            item_id: {{ json_encode($v0?->sku ?: 'p'.$product->id) }},
            item_name: {{ json_encode($product->name) }},
            price: {{ json_encode($vprice) }},
            quantity: 1
        }]
    }
});
@if (config('commerce.meta.pixel_id'))
fbq('track', 'ViewContent', {
    content_ids: [{{ json_encode($v0?->sku ?: 'p'.$product->id) }}],
    content_type: 'product',
    value: {{ json_encode($vprice) }},
    currency: '{{ config('commerce.currency', 'INR') }}'
});
@endif
</script>
@endpush
