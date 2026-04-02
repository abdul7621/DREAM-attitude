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
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category) }}">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-md-6">
            @if ($product->images->isNotEmpty())
                <div id="pCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded border">
                        @foreach ($product->images as $i => $image)
                            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $image->alt_text ?? $product->name }}" style="max-height:420px;object-fit:contain;">
                            </div>
                        @endforeach
                    </div>
                    @if ($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#pCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                        <button class="carousel-control-next" type="button" data-bs-target="#pCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                    @endif
                </div>
            @else
                <div class="bg-secondary-subtle rounded d-flex align-items-center justify-content-center" style="min-height:320px;">No image</div>
            @endif
        </div>
        <div class="col-md-6">
            <h1 class="h3">{{ $product->name }}</h1>
            @if ($product->is_bestseller)
                <span class="badge text-bg-warning mb-2">Bestseller</span>
            @endif
            @if ($product->short_description)
                <p class="text-muted">{{ $product->short_description }}</p>
            @endif
            <form method="post" action="{{ route('cart.items.store') }}" class="border rounded p-3 bg-white">
                @csrf
                <label class="form-label">Variant</label>
                <select class="form-select mb-3" name="variant_id" id="variant_id" required>
                    @foreach ($product->variants as $v)
                        @php $p = $variantPrices[$v->id]; @endphp
                        <option value="{{ $v->id }}" data-price="{{ $p['display'] }}" data-compare="{{ $p['compare'] ?? '' }}">
                            {{ $v->title }} — ₹{{ number_format($p['display'], 2) }}
                            @if ($v->track_inventory)
                                (Stock: {{ $v->stock_qty }})
                            @else
                                (In stock)
                            @endif
                        </option>
                    @endforeach
                </select>
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-6 col-md-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="qty" value="1" min="1" max="9999" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="mb-1">
                            <span class="fs-4 fw-semibold" id="priceLabel">₹0</span>
                            <span class="text-muted text-decoration-line-through ms-2" id="compareLabel" style="display:none;"></span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 w-md-auto">Add to cart</button>
                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary ms-md-2 mt-2 mt-md-0">View cart</a>
            </form>
            @if ($product->description)
                <div class="mt-4">{!! $product->description !!}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('variant_id');
    const priceLabel = document.getElementById('priceLabel');
    const compareLabel = document.getElementById('compareLabel');
    function refresh() {
        const opt = sel.selectedOptions[0];
        const p = opt.dataset.price;
        const c = opt.dataset.compare;
        priceLabel.textContent = '₹' + parseFloat(p).toLocaleString('en-IN', {minimumFractionDigits: 2});
        if (c) {
            compareLabel.style.display = 'inline';
            compareLabel.textContent = '₹' + parseFloat(c).toLocaleString('en-IN', {minimumFractionDigits: 2});
        } else {
            compareLabel.style.display = 'none';
        }
    }
    sel.addEventListener('change', refresh);
    refresh();
})();
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
