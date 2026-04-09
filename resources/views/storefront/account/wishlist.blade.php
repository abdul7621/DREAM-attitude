@extends('layouts.account')
@section('title', 'Wishlist')
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-heart me-2"></i>Wishlist</h1>

@if ($wishlists->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-heart fs-1 d-block mb-2"></i>
            Your wishlist is empty. <a href="{{ route('search') }}" class="fw-semibold">Browse products →</a>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($wishlists as $wish)
            @if ($wish->product)
                @php
                    $product = $wish->product;
                    $variant = $product->variants->firstWhere('is_active', true) ?? $product->variants->first();
                    $img = $product->primaryImage();
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <a href="{{ route('product.show', $product) }}">
                            @if ($img)
                                <img src="{{ asset('storage/'.$img->path) }}" class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;"><i class="bi bi-image fs-1 text-muted"></i></div>
                            @endif
                        </a>
                        <div class="card-body p-2">
                            <a href="{{ route('product.show', $product) }}" class="text-dark fw-semibold small text-decoration-none d-block mb-1">{{ Str::limit($product->name, 40) }}</a>
                            @if ($variant)
                                <span class="fw-bold text-dark">₹{{ number_format($variant->price_retail, 0) }}</span>
                                @if ($variant->compare_at_price && $variant->compare_at_price > $variant->price_retail)
                                    <small class="text-muted text-decoration-line-through ms-1">₹{{ number_format($variant->compare_at_price, 0) }}</small>
                                @endif
                            @endif
                        </div>
                        <div class="card-footer bg-transparent border-0 p-2 pt-0">
                            <form action="{{ route('account.wishlist.destroy', $wish) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
