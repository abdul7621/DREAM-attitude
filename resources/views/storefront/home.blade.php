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
        <section class="sf-hero">
            <div class="sf-hero-img-wrap">
            @if($ss->get('theme.hero_image'))
                <img src="{{ asset('storage/' . $ss->get('theme.hero_image')) }}" alt="{{ $ss->get('theme.hero_title', config('app.name')) }}" class="sf-hero-img">
            @endif
            </div>
            <div class="sf-hero-overlay"></div>
            <div class="sf-hero-content">
                <div class="sf-hero-tag">Welcome</div>
                <h1 class="sf-hero-title">{{ $ss->get('theme.hero_title', 'Discover Premium Quality Products') }}</h1>
                <p class="sf-hero-sub">{{ $ss->get('theme.hero_subtitle', 'Handpicked collection of authentic products. Fast delivery across India with easy returns.') }}</p>
                @if($ss->get('theme.hero_cta_text'))
                    <a href="{{ $ss->get('theme.hero_cta_link', '/search') }}" class="sf-hero-cta">
                        {{ $ss->get('theme.hero_cta_text', 'Shop Now') }}
                    </a>
                @endif
            </div>
        </section>
    @endif

    @if ($sectionKey === 'trust')
        {{-- ── Trust Strip ─────────────────────────────────────────── --}}
        <section class="sf-section" style="padding: 24px 0; background: var(--color-bg-surface); border-bottom: 1px solid var(--color-border-gold);">
            <div class="sf-container">
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 32px;">
                    @php
                        $trustTexts = array_map('trim', explode('|', $ss->get('theme.trust_text', '100% Authentic | Free Delivery | Secure Checkout | Easy Returns')));
                        $trustIcons = ['bi-patch-check-fill', 'bi-truck', 'bi-shield-check', 'bi-arrow-counterclockwise'];
                    @endphp
                    @foreach($trustTexts as $i => $text)
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi {{ $trustIcons[$i % count($trustIcons)] }}" style="color: var(--color-gold); font-size: 24px;"></i>
                            <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ $text }}</span>
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
                <div style="text-align: center; margin-bottom: 32px;">
                    <h2 class="sf-section-title">{{ $sTitle ?? 'Shop by Category' }}</h2>
                    @if($sSubtitle)<p style="color: var(--color-text-muted); font-size: 13px; margin-top: 8px;">{{ $sSubtitle }}</p>@endif
                </div>
                <div class="sf-category-grid">
                    @foreach ($categories as $cat)
                        <a href="{{ route('category.show', $cat) }}" class="sf-cat-card">
                            @if ($cat->image_path)
                                <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                            @else
                                <div style="position:absolute;inset:0;background:var(--color-bg-elevated);"></div>
                            @endif
                            <div class="cat-overlay"></div>
                            <label>{{ $cat->name }}</label>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'bestsellers' && isset($bestsellers) && $bestsellers->isNotEmpty())
        {{-- ── Bestsellers ───────────────────────────────────────── --}}
        <section class="sf-section" style="background:var(--color-bg-surface);">
            <div class="sf-container">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <div>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'Bestsellers' }}</h2>
                        @if($sSubtitle)<p style="color: var(--color-text-muted); font-size: 13px; margin-top: 8px;">{{ $sSubtitle }}</p>@endif
                    </div>
                    <a href="{{ route('search', ['sort' => 'bestseller']) }}" style="color: var(--color-gold); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">View All</a>
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
        <section class="sf-section">
            <div class="sf-container">
                <a href="{{ $bannerLink }}" style="display: block; position: relative; border-radius: var(--radius-md); overflow: hidden;">
                    @if($bannerImage)
                        <img src="{{ asset('storage/' . $bannerImage) }}" alt="{{ $bannerText ?? 'Promotion' }}" style="width:100%; border-radius: var(--radius-md);" loading="lazy">
                    @else
                        <div style="background: var(--color-bg-elevated); border: 1px solid var(--color-border); padding: 48px; text-align: center; border-radius: var(--radius-md);">
                            <h3 style="font-size: 24px; color: var(--color-gold); text-transform: uppercase; letter-spacing: 2px;">{{ $bannerText }}</h3>
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
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <div>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'Featured Products' }}</h2>
                        @if($sSubtitle)<p style="color: var(--color-text-muted); font-size: 13px; margin-top: 8px;">{{ $sSubtitle }}</p>@endif
                    </div>
                    <a href="{{ route('search') }}" style="color: var(--color-gold); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">View All</a>
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
        <section class="sf-section" style="background:var(--color-bg-surface);">
            <div class="sf-container">
                <h2 class="sf-section-title" style="margin-bottom: 24px;">{{ $sTitle ?? 'New Arrivals' }}</h2>
                <div class="sf-product-grid">
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
        <section class="sf-section">
            <div class="sf-container">
                <div style="display: grid; gap: 32px; grid-template-columns: @if($storyImage) repeat(auto-fit, minmax(300px, 1fr)) @else 1fr @endif; align-items: center;">
                    @if($storyImage)
                    <div>
                        <img src="{{ asset('storage/' . $storyImage) }}" alt="{{ $storyTitle ?? config('app.name') }}" style="width: 100%; border-radius: var(--radius-md);" loading="lazy">
                    </div>
                    @endif
                    <div>
                        @if($storyTitle)
                            <h2 class="sf-section-title" style="margin-bottom: 16px;">{{ $storyTitle }}</h2>
                        @endif
                        @if($storyText)
                            <p style="color: var(--color-text-secondary); font-size: 14px; line-height: 1.7;">{{ $storyText }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'reviews' && isset($topReviews) && $topReviews->isNotEmpty())
        {{-- ── Customer Reviews ───────────────────── --}}
        <section class="sf-section" style="background:var(--color-bg-surface);">
            <div class="sf-container">
                <div style="text-align: center; margin-bottom: 32px;">
                    <h2 class="sf-section-title">{{ $sTitle ?? 'What Our Customers Say' }}</h2>
                    <p style="color: var(--color-text-muted); font-size: 13px; margin-top: 8px;">{{ $sSubtitle ?? 'Real reviews from verified buyers' }}</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    @foreach ($topReviews as $review)
                        <div style="background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 20px; display: flex; flex-direction: column;">
                            <div style="color: var(--color-gold); font-size: 14px; margin-bottom: 12px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <p style="color: var(--color-text-secondary); font-size: 13px; line-height: 1.6; font-style: italic; flex: 1;">"{{ \Illuminate\Support\Str::limit($review->body, 120) }}"</p>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                                <div style="font-size: 13px; font-weight: 600; color: white;">{{ $review->reviewer_name }}</div>
                                @if ($review->verified_purchase)
                                    <span style="color: var(--color-success); font-size: 11px; font-weight: 600;"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                @endif
                            </div>
                            @if($review->product)
                                <div style="color: var(--color-text-muted); font-size: 11px; margin-top: 8px;">on {{ $review->product->name }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endforeach

@endsection
