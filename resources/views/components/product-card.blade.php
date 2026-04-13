@props(['product'])
@php
    $variant = $product->variants->firstWhere('is_active', true) ?? $product->variants->first();
    $img = $product->primaryImage();
    $compare = $variant?->compare_at_price;
    $price = $variant?->price_retail;
    $discount = ($compare && $price && $compare > $price) ? round((($compare - $price) / $compare) * 100) : 0;

    $realVariants = $product->variants
        ->where('is_active', true)
        ->filter(fn($v) =>
            !in_array(strtolower(trim($v->title)),
            ['default title', 'default', ''])
        );
    $hasMultipleVariants = $realVariants->count() > 1;
    $isOutOfStock = $variant && $variant->track_inventory && $variant->stock_qty <= 0;
@endphp
<div class="sf-product-card">
    <div class="img-wrap">
        <a href="{{ route('product.show', $product) }}">
            @if ($img)
                <img src="{{ asset('storage/'.$img->path) }}" alt="{{ $img->alt_text ?? $product->name }}" loading="lazy">
            @else
                <div style="background:var(--color-bg-elevated);width:100%;aspect-ratio:1/1;"></div>
            @endif
        </a>
        <button type="button"
            class="wishlist-heart"
            data-product-id="{{ $product->id }}"
            title="Wishlist">
            <i class="bi bi-heart"></i>
        </button>
        @if ($product->is_bestseller)
            <span class="badge">Bestseller</span>
        @elseif ($product->is_featured)
            <span class="badge" style="background:#2563eb;color:white;">Featured</span>
        @endif
    </div>
    
    <div class="card-body">
        <a href="{{ route('product.show', $product) }}" class="product-name">{{ $product->name }}</a>
        @if ($variant)
            <div class="price-row">
                <span class="sale-price">₹{{ number_format($price, 0) }}</span>
                @if ($compare && $compare > $price)
                    <span class="mrp">₹{{ number_format($compare, 0) }}</span>
                    <span class="discount">{{ $discount }}% OFF</span>
                @endif
            </div>
            
            @if ($hasMultipleVariants)
                <a href="{{ route('product.show', $product) }}" class="btn-add" style="display:block;text-align:center;text-decoration:none;line-height:36px;">Select Options →</a>
            @elseif ($isOutOfStock)
                <button type="button" class="btn-add" disabled style="opacity:0.5;cursor:not-allowed;">Out of Stock</button>
            @else
                <form action="{{ route('cart.items.store') }}" method="POST" class="form-add-to-cart">
                    @csrf
                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn-add">Add to Cart</button>
                </form>
            @endif
        @endif
    </div>
</div>
