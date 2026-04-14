@extends('layouts.account')
@section('title', 'Wishlist')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-heart" style="color:var(--color-gold);"></i>Wishlist
</h1>

@if ($wishlists->isEmpty())
    <div class="sf-account-card" style="text-align:center;padding:48px 20px;color:var(--color-text-muted);">
        <i class="bi bi-heart" style="font-size:32px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
        Your wishlist is empty. <a href="{{ route('search') }}" style="text-decoration:none;color:var(--color-gold);font-weight:600;">Browse products →</a>
    </div>
@else
    <div class="sf-product-grid">
        @foreach ($wishlists as $wish)
            @if ($wish->product)
                @php
                    $product = $wish->product;
                    $variant = $product->variants->firstWhere('is_active', true) ?? $product->variants->first();
                    $img = $product->primaryImage();
                @endphp
                <div class="sf-product-card">
                    <div class="img-wrap">
                        <a href="{{ route('product.show', $product) }}">
                            @if ($img)
                                <img src="{{ asset('storage/'.$img->path) }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <div style="background:var(--color-bg-elevated);width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);font-size:24px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </a>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('product.show', $product) }}" class="product-name">{{ Str::limit($product->name, 40) }}</a>
                        @if ($variant)
                            <div class="price-row">
                                <span class="sale-price">₹{{ number_format($variant->price_retail, 0) }}</span>
                                @if ($variant->compare_at_price && $variant->compare_at_price > $variant->price_retail)
                                    <span class="mrp">₹{{ number_format($variant->compare_at_price, 0) }}</span>
                                @endif
                            </div>
                        @endif
                        <form action="{{ route('account.wishlist.destroy', $wish) }}" method="post" style="margin-top:auto;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="width:100%;margin-top:10px;background:transparent;border:1px solid var(--color-error);color:var(--color-error);padding:8px 0;font-size:10px;text-transform:uppercase;letter-spacing:1px;border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);">
                                <i class="bi bi-trash" style="margin-right:4px;"></i>Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
