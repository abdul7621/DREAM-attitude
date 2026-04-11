@extends('layouts.storefront')

@section('title', config('app.name') . ' — ' . app(\App\Services\SettingsService::class)->get('theme.hero_title', 'Premium Quality Products'))

@section('content')
@php
    $ss = app(\App\Services\SettingsService::class);
    $sections = json_decode($ss->get('theme.home_sections', '[]'), true) ?: ['hero', 'trust', 'categories', 'bestsellers', 'offers_banner', 'featured', 'text_with_image', 'reviews'];
@endphp

@foreach($sections as $section)
    @php
        $sectionKey = is_array($section) ? ($section['key'] ?? '') : $section;
        $isEnabled = is_array($section) ? ($section['enabled'] ?? true) : true;
        $sTitle = is_array($section) ? ($section['title'] ?? null) : null;
        $sSubtitle = is_array($section) ? ($section['subtitle'] ?? null) : null;
    @endphp
    @if (!$isEnabled) @continue @endif

    @if ($sectionKey === 'hero')
        {{-- ── Hero Banner ───────────────────────────────────────── --}}
        <section class="sf-hero position-relative overflow-hidden">
            @if($ss->get('theme.hero_image'))
                <img src="{{ asset('storage/' . $ss->get('theme.hero_image')) }}" alt="{{ $ss->get('theme.hero_title', config('app.name')) }}" class="sf-hero-bg">
            @endif
            <div class="sf-hero-overlay"></div>
            <div class="container position-relative" style="z-index:2;">
                <div class="row align-items-center min-vh-50">
                    <div class="col-lg-7 col-xl-6">
                        <h1 class="sf-hero-title">{{ $ss->get('theme.hero_title', 'Discover Premium Quality Products') }}</h1>
                        <p class="sf-hero-subtitle mt-3">{{ $ss->get('theme.hero_subtitle', 'Handpicked collection of authentic products. Fast delivery across India with easy returns.') }}</p>
                        @if($ss->get('theme.hero_cta_text'))
                            <a href="{{ $ss->get('theme.hero_cta_link', '/search') }}" class="btn btn-hero mt-4">
                                {{ $ss->get('theme.hero_cta_text', 'Shop Now') }} <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'trust')
        {{-- ── Trust Strip ─────────────────────────────────────────── --}}
        <section class="sf-trust-bar">
            <div class="container">
                <div class="row g-2 text-center">
                    @php
                        $trustItems = [
                            ['icon' => 'bi-patch-check-fill', 'text' => ''],
                            ['icon' => 'bi-truck', 'text' => ''],
                            ['icon' => 'bi-shield-check', 'text' => ''],
                            ['icon' => 'bi-arrow-counterclockwise', 'text' => ''],
                        ];
                        $trustTexts = array_map('trim', explode('|', $ss->get('theme.trust_text', '100% Authentic | Free Delivery | Secure Checkout | Easy Returns')));
                        $trustIcons = ['bi-patch-check-fill', 'bi-truck', 'bi-shield-check', 'bi-arrow-counterclockwise'];
                    @endphp
                    @foreach($trustTexts as $i => $text)
                        <div class="col-6 col-md-{{ count($trustTexts) <= 4 ? '3' : '2' }}">
                            <div class="sf-trust-item">
                                <i class="bi {{ $trustIcons[$i % count($trustIcons)] }}"></i>
                                <span class="trust-label">{{ $text }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'categories' && isset($categories) && $categories->isNotEmpty())
        {{-- ── Category Showcase ────────────────────────────────────── --}}
        <section class="sf-section">
            <div class="sf-container">
                <div class="text-center mb-4">
                    <h2 class="sf-section-title">{{ $sTitle ?? 'Shop by Category' }}</h2>
                    <p class="sf-section-subtitle">{{ $sSubtitle ?? 'Browse our curated collections' }}</p>
                </div>
                @php
                    $catCount = $categories->count();
                    $cardHeight = match(true) {
                        $catCount <= 2 => '350px',
                        $catCount === 3 => '300px',
                        $catCount === 4 => '280px',
                        default => '260px',
                    };
                @endphp
                <div class="sf-category-grid">
                    @foreach ($categories as $cat)
                        <a href="{{ route('category.show', $cat) }}" class="sf-cat-card" style="height: {{ $cardHeight }};">
                            @if ($cat->image_path)
                                <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                            @else
                                <div class="sf-cat-fallback"></div>
                            @endif
                            <div class="cat-overlay">
                                <span class="cat-name">{{ $cat->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'bestsellers' && isset($bestsellers) && $bestsellers->isNotEmpty())
        {{-- ── Bestsellers ───────────────────────────────────────── --}}
        <section class="sf-section" style="background:var(--sf-bg-alt);">
            <div class="sf-container">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="sf-section-title mb-0">{{ $sTitle ?? '🔥 Bestsellers' }}</h2>
                        @if($sSubtitle)<p class="sf-section-subtitle mb-0 mt-1">{{ $sSubtitle }}</p>@endif
                    </div>
                    <a href="{{ route('search', ['sort' => 'bestseller']) }}" class="btn btn-sm btn-outline-dark rounded-pill">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="sf-product-grid">
                    @foreach ($bestsellers as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'offers_banner' || $sectionKey === 'image_banner')
        {{-- ── Image Banner (Dynamic) ─────────────────────────────── --}}
        @php
            $bannerImage = $ss->get('theme.offers_banner_image') ?: $ss->get('theme.image_banner_image');
            $bannerText = $ss->get('theme.offers_banner_text') ?: $ss->get('theme.image_banner_text');
            $bannerLink = $ss->get('theme.offers_banner_link') ?: $ss->get('theme.image_banner_link', '#');
        @endphp
        @if($bannerImage || $bannerText)
        <section class="sf-section py-4">
            <div class="container">
                <a href="{{ $bannerLink }}" class="sf-promo-banner d-block">
                    @if($bannerImage)
                        <img src="{{ asset('storage/' . $bannerImage) }}" alt="{{ $bannerText ?? 'Promotion' }}" class="w-100" loading="lazy">
                    @else
                        <div class="sf-promo-text-banner">
                            <h3 class="fw-bold mb-0">{{ $bannerText }}</h3>
                        </div>
                    @endif
                </a>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'featured' && isset($featured) && $featured->isNotEmpty())
        {{-- ── Featured Products ─────────────────────────────────── --}}
        <section class="sf-section">
            <div class="sf-container">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="sf-section-title mb-0">{{ $sTitle ?? '✨ Featured Products' }}</h2>
                        @if($sSubtitle)<p class="sf-section-subtitle mb-0 mt-1">{{ $sSubtitle }}</p>@endif
                    </div>
                    <a href="{{ route('search') }}" class="btn btn-sm btn-outline-dark rounded-pill">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="sf-product-grid">
                    @foreach ($featured as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'latest' && isset($latest) && $latest->isNotEmpty())
        {{-- ── New Arrivals ───────────────────────────────────────── --}}
        <section class="sf-section" style="background:var(--sf-bg-alt);">
            <div class="sf-container">
                <h2 class="sf-section-title">{{ $sTitle ?? '🆕 New Arrivals' }}</h2>
                <div class="sf-product-grid mt-2">
                    @foreach ($latest as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'text_with_image')
        {{-- ── Brand Story / Text + Image ──────────────────────────── --}}
        @php
            $storyTitle = $ss->get('theme.brand_story_title');
            $storyText = $ss->get('theme.brand_story_text');
            $storyImage = $ss->get('theme.brand_story_image');
        @endphp
        @if($storyTitle || $storyText)
        <section class="sf-section sf-brand-story">
            <div class="container">
                <div class="row align-items-center g-4">
                    @if($storyImage)
                    <div class="col-md-5">
                        <img src="{{ asset('storage/' . $storyImage) }}" alt="{{ $storyTitle ?? config('app.name') }}" class="img-fluid rounded-3 shadow" loading="lazy">
                    </div>
                    @endif
                    <div class="{{ $storyImage ? 'col-md-7' : 'col-12' }}">
                        @if($storyTitle)
                            <h2 class="sf-section-title">{{ $storyTitle }}</h2>
                        @endif
                        @if($storyText)
                            <p class="text-muted lh-lg">{{ $storyText }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'reviews' && isset($topReviews) && $topReviews->isNotEmpty())
        {{-- ── Customer Reviews ───────────────────── --}}
        <section class="sf-section" style="background:var(--sf-bg-alt);">
            <div class="container">
                <div class="text-center mb-4">
                    <h2 class="sf-section-title">{{ $sTitle ?? '💬 What Our Customers Say' }}</h2>
                    <p class="sf-section-subtitle">{{ $sSubtitle ?? 'Real reviews from verified buyers' }}</p>
                </div>
                <div class="row g-4">
                    @foreach ($topReviews as $review)
                        <div class="col-md-6 col-lg-3">
                            <div class="sf-review-card h-100">
                                <div class="mb-2 review-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <p class="review-body fst-italic">"{{ \Illuminate\Support\Str::limit($review->body, 120) }}"</p>
                                <div class="d-flex align-items-center mt-auto">
                                    <div class="review-author">{{ $review->reviewer_name }}</div>
                                    @if ($review->verified_purchase)
                                        <span class="verified-badge ms-auto"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                    @endif
                                </div>
                                @if($review->product)
                                    <div class="mt-2 small text-muted">on {{ $review->product->name }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endforeach

@endsection
