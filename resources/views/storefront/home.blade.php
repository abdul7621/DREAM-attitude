@extends('layouts.storefront')

@section('title', config('app.name') . ' — Premium Quality Products')

@section('content')
    {{-- ── Hero Banner ───────────────────────────────────────── --}}
    <section class="sf-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Discover Premium Quality Products</h1>
                    <p class="mt-3">Handpicked collection of authentic products. Fast delivery across India with easy returns.</p>
                    <a href="{{ route('search') }}" class="btn btn-hero mt-3">Shop Now <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Trust Bar ─────────────────────────────────────────── --}}
    <section class="sf-trust-bar">
        <div class="container">
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <div class="sf-trust-item">
                        <i class="bi bi-truck"></i>
                        <div class="trust-label">Free Shipping</div>
                        <div class="trust-desc">On orders above ₹499</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sf-trust-item">
                        <i class="bi bi-cash-coin"></i>
                        <div class="trust-label">COD Available</div>
                        <div class="trust-desc">Cash on delivery</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sf-trust-item">
                        <i class="bi bi-arrow-return-left"></i>
                        <div class="trust-label">Easy Returns</div>
                        <div class="trust-desc">7-day return policy</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sf-trust-item">
                        <i class="bi bi-shield-check"></i>
                        <div class="trust-label">Secure Checkout</div>
                        <div class="trust-desc">100% secure payment</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Categories ────────────────────────────────────────── --}}
    @if ($categories->isNotEmpty())
        <section class="sf-section">
            <div class="container">
                <h2 class="sf-section-title">Shop by Category</h2>
                <p class="sf-section-subtitle">Browse our curated collections</p>
                <div class="row g-3">
                    @foreach ($categories as $cat)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('category.show', $cat) }}" class="sf-cat-card">
                                @if ($cat->image_path)
                                    <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}">
                                @else
                                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#16213e);"></div>
                                @endif
                                <div class="cat-overlay">
                                    <span class="cat-name">{{ $cat->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── Featured Products ─────────────────────────────────── --}}
    @if ($featured->isNotEmpty())
        <section class="sf-section" style="background:var(--sf-bg-alt);">
            <div class="container">
                <h2 class="sf-section-title">Featured Products</h2>
                <p class="sf-section-subtitle">Our top picks for you</p>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach ($featured as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── Bestsellers ───────────────────────────────────────── --}}
    @if ($bestsellers->isNotEmpty())
        <section class="sf-section">
            <div class="container">
                <h2 class="sf-section-title">🔥 Bestsellers</h2>
                <p class="sf-section-subtitle">Most loved by our customers</p>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach ($bestsellers as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ── Latest Products ───────────────────────────────────── --}}
    @if ($latest->isNotEmpty())
        <section class="sf-section" style="background:var(--sf-bg-alt);">
            <div class="container">
                <h2 class="sf-section-title">New Arrivals</h2>
                <p class="sf-section-subtitle">Fresh additions to our store</p>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach ($latest as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('search') }}" class="btn btn-outline-dark px-4 rounded-pill">View All Products <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </section>
    @endif

    {{-- ── Customer Reviews / Testimonials ───────────────────── --}}
    @if ($topReviews->isNotEmpty())
        <section class="sf-section">
            <div class="container">
                <h2 class="sf-section-title">What Our Customers Say</h2>
                <p class="sf-section-subtitle">Real reviews from real customers</p>
                <div class="row g-3">
                    @foreach ($topReviews as $review)
                        <div class="col-md-6 col-lg-3">
                            <div class="sf-review-card h-100">
                                <div class="review-stars mb-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <p class="review-body">{{ \Illuminate\Support\Str::limit($review->body, 120) }}</p>
                                <div class="d-flex align-items-center gap-2 mt-auto">
                                    <span class="review-author">{{ $review->reviewer_name }}</span>
                                    @if ($review->verified_purchase)
                                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                    @endif
                                </div>
                                @if ($review->product)
                                    <a href="{{ route('product.show', $review->product) }}" class="small text-muted text-decoration-none mt-1 d-block">{{ $review->product->name }}</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
