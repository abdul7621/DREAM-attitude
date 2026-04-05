@props(['product'])
@php
    $variant = $product->variants->firstWhere('is_active', true) ?? $product->variants->first();
    $img = $product->primaryImage();
    $compare = $variant?->compare_at_price;
    $price = $variant?->price_retail;
    $discount = ($compare && $price && $compare > $price) ? round((($compare - $price) / $compare) * 100) : 0;
@endphp
<div class="sf-product-card">
    <a href="{{ route('product.show', $product) }}">
        <div class="card-img-wrap">
            @if ($img)
                <img src="{{ asset('storage/'.$img->path) }}" alt="{{ $img->alt_text ?? $product->name }}" loading="lazy">
            @else
                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted"><i class="bi bi-image" style="font-size:2rem;"></i></div>
            @endif
            @if ($product->is_bestseller)
                <span class="badge-featured">Bestseller</span>
            @elseif ($product->is_featured)
                <span class="badge-featured" style="background:#2563eb;">Featured</span>
            @endif
        </div>
    </a>
    <div class="card-body d-flex flex-column">
        <a href="{{ route('product.show', $product) }}" class="product-title">{{ $product->name }}</a>
        @if ($variant)
            <div class="product-price mt-auto">
                ₹{{ number_format($price, 0) }}
                @if ($compare && $compare > $price)
                    <span class="compare">₹{{ number_format($compare, 0) }}</span>
                    <span class="discount-tag">{{ $discount }}% OFF</span>
                @endif
            </div>
        @endif
    </div>
</div>
