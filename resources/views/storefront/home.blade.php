@extends('layouts.storefront')

@section('title', config('app.name') . ' — ' . app(\App\Services\SettingsService::class)->get('theme.hero_title', 'Premium Quality Products'))

@section('content')
@php
    $ss = app(\App\Services\SettingsService::class);
    $sections = json_decode($ss->get('theme.home_sections', '[]'), true) ?: ['hero', 'categories', 'featured', 'bestsellers', 'trust', 'reviews'];
@endphp

@foreach($sections as $section)
    @php
        $sectionKey = is_array($section) ? ($section['key'] ?? '') : $section;
        $isEnabled = is_array($section) ? ($section['enabled'] ?? true) : true;
    @endphp
    @if (!$isEnabled) @continue @endif

    @if ($sectionKey === 'hero')
        {{-- ── Hero Banner ───────────────────────────────────────── --}}
        <section class="sf-hero" style="
            @if($ss->get('theme.hero_image')) background-image: url('{{ asset('storage/' . $ss->get('theme.hero_image')) }}'); background-size: cover; background-position: center; @endif
            padding: 80px 0; background-color: var(--brand-primary); color: #fff;
        ">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" style="background: rgba(0,0,0,0.4); padding: 2rem; border-radius: 12px; backdrop-filter: blur(4px);">
                        <h1 class="fw-bold">{{ $ss->get('theme.hero_title', 'Discover Premium Quality Products') }}</h1>
                        <p class="mt-3 fs-5">{{ $ss->get('theme.hero_subtitle', 'Handpicked collection of authentic products. Fast delivery across India with easy returns.') }}</p>
                        <a href="{{ $ss->get('theme.hero_cta_link', '/search') }}" class="btn btn-light btn-lg mt-3 fw-bold text-dark px-4 round shadow-sm">
                            {{ $ss->get('theme.hero_cta_text', 'Shop Now') }} <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'trust')
        {{-- ── Trust Bar ─────────────────────────────────────────── --}}
        <section class="sf-trust-bar py-4 bg-light border-bottom">
            <div class="container">
                <div class="row g-3 text-center">
                    @php
                        $trustText = explode('|', $ss->get('theme.trust_text', 'Authentic Products | Secure Checkout | Easy Returns | Fast Shipping'));
                    @endphp
                    @foreach($trustText as $text)
                        <div class="col-6 col-md-3">
                            <div class="d-flex flex-column align-items-center p-3 bg-white shadow-sm rounded h-100">
                                <i class="bi bi-patch-check-fill text-primary" style="font-size: 2rem;"></i>
                                <span class="mt-2 fw-medium">{{ trim($text) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'categories' && isset($categories) && $categories->isNotEmpty())
        {{-- ── Categories ────────────────────────────────────────── --}}
        <section class="sf-section py-5">
            <div class="container">
                <h2 class="sf-section-title fw-bold">Shop by Category</h2>
                <p class="sf-section-subtitle text-muted mb-4">Browse our curated collections</p>
                <div class="row g-3">
                    @foreach ($categories as $cat)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('category.show', $cat) }}" class="sf-cat-card d-block position-relative rounded overflow-hidden shadow-sm" style="height: 140px;">
                                @if ($cat->image_path)
                                    <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 bg-secondary"></div>
                                @endif
                                <div class="position-absolute bottom-0 w-100 bg-dark bg-opacity-75 text-white text-center py-2" style="backdrop-filter: blur(2px);">
                                    <span class="cat-name fw-medium">{{ $cat->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'featured' && isset($featured) && $featured->isNotEmpty())
        {{-- ── Featured Products ─────────────────────────────────── --}}
        <section class="sf-section py-5" style="background:var(--sf-bg-alt);">
            <div class="container">
                <h2 class="sf-section-title fw-bold">Featured Products</h2>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mt-2">
                    @foreach ($featured as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'bestsellers' && isset($bestsellers) && $bestsellers->isNotEmpty())
        {{-- ── Bestsellers ───────────────────────────────────────── --}}
        <section class="sf-section py-5">
            <div class="container">
                <h2 class="sf-section-title fw-bold">🔥 Bestsellers</h2>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mt-2">
                    @foreach ($bestsellers as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'latest' && isset($latest) && $latest->isNotEmpty())
        {{-- ── New Arrivals ───────────────────────────────────────── --}}
        <section class="sf-section py-5 bg-white">
            <div class="container">
                <h2 class="sf-section-title fw-bold">🆕 New Arrivals</h2>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mt-2">
                    @foreach ($latest as $product)
                        <div class="col">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'offers_banner')
        {{-- ── Offers Banner ─────────────────────────────────────────── --}}
        @if($ss->get('theme.offers_banner_text') || $ss->get('theme.offers_banner_image'))
        <section class="sf-offers-banner my-4">
            <div class="container">
                <a href="{{ $ss->get('theme.offers_banner_link', '#') }}" class="d-block rounded overflow-hidden shadow-sm text-center" style="background-color: var(--brand-primary); color: #fff; text-decoration: none;">
                    @if($ss->get('theme.offers_banner_image'))
                        <img src="{{ asset('storage/' . $ss->get('theme.offers_banner_image')) }}" alt="Offers" class="w-100 object-fit-cover" style="max-height: 250px;">
                    @else
                        <div class="p-4 p-md-5">
                            <h3 class="fw-bold mb-0">{{ $ss->get('theme.offers_banner_text') }}</h3>
                        </div>
                    @endif
                </a>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'reviews' && isset($topReviews) && $topReviews->isNotEmpty())
        {{-- ── Customer Reviews ───────────────────── --}}
        <section class="sf-section py-5 bg-light">
            <div class="container">
                <h2 class="sf-section-title fw-bold">Customer Stories</h2>
                <div class="row g-4 mt-2">
                    @foreach ($topReviews as $review)
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 shadow-sm border-0 p-3">
                                <div class="mb-2 text-warning">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <p class="review-body fst-italic text-secondary small">"{{ \Illuminate\Support\Str::limit($review->body, 120) }}"</p>
                                <div class="d-flex align-items-center mt-auto">
                                    <div class="fw-bold small">{{ $review->reviewer_name }}</div>
                                    @if ($review->verified_purchase)
                                        <span class="ms-auto text-success small"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endforeach

@endsection
