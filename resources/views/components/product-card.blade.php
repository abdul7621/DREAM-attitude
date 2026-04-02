@props(['product'])
@php
    $variant = $product->variants->firstWhere('is_active', true) ?? $product->variants->first();
    $img = $product->primaryImage();
@endphp
<div class="card h-100 shadow-sm">
    @if ($img)
        <a href="{{ route('product.show', $product) }}"><img src="{{ asset('storage/'.$img->path) }}" class="card-img-top" alt="{{ $img->alt_text ?? $product->name }}" style="object-fit:cover;height:220px;"></a>
    @else
        <div class="bg-secondary-subtle d-flex align-items-center justify-content-center" style="height:220px;">No image</div>
    @endif
    <div class="card-body d-flex flex-column">
        <h2 class="h6 card-title"><a class="text-decoration-none text-dark" href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h2>
        @if ($variant)
            <p class="mb-0 mt-auto">
                <span class="fw-semibold">₹{{ number_format($variant->price_retail, 2) }}</span>
                @if ($variant->compare_at_price)
                    <span class="text-muted text-decoration-line-through ms-1">₹{{ number_format($variant->compare_at_price, 2) }}</span>
                @endif
            </p>
        @endif
    </div>
</div>
