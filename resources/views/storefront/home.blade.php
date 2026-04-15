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
        {{-- ── Hero Slider ──────────────────────────────────────── --}}
        @php
            $heroSlides = $ss->get('theme.hero_slides');
            $heroSlides = is_array($heroSlides) ? $heroSlides : [];
            // Backward compatibility: fallback to single hero_image
            if (empty($heroSlides) && $ss->get('theme.hero_image')) {
                $heroSlides = [['image' => $ss->get('theme.hero_image'), 'link' => $ss->get('theme.hero_cta_link', '/search'), 'alt' => $ss->get('theme.hero_title', config('app.name'))]];
            }
            $slideCount = count($heroSlides);
        @endphp
        @if($slideCount > 0)
        <section class="sf-hero" id="heroSlider">
            <div class="sf-hero-track">
                @foreach($heroSlides as $idx => $slide)
                <div class="sf-hero-slide" data-link="{{ $slide['link'] ?? '' }}">
                    @if(!empty($slide['link']))
                    <a href="{{ $slide['link'] }}" class="sf-hero-img-wrap" style="display:block; width:100%; height:100%;">
                    @else
                    <div class="sf-hero-img-wrap">
                    @endif
                        <img src="{{ asset('storage/' . $slide['image']) }}"
                             alt="{{ $slide['alt'] ?? '' }}"
                             class="sf-hero-img"
                             loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                    @if(!empty($slide['link']))
                    </a>
                    @else
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @if($slideCount > 1)
            <div class="sf-hero-dots">
                @for($i = 0; $i < $slideCount; $i++)
                <button type="button" class="sf-hero-dot{{ $i === 0 ? ' active' : '' }}" data-slide="{{ $i }}"></button>
                @endfor
            </div>
            <button type="button" class="sf-hero-arrow sf-hero-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
            <button type="button" class="sf-hero-arrow sf-hero-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
            @endif
            @if($ss->get('theme.hero_title'))
            <div class="sf-hero-overlay" style="pointer-events: none;"></div>
            <div class="sf-hero-content" style="pointer-events: none;">
                <div class="sf-hero-tag">Welcome</div>
                <h1 class="sf-hero-title">{{ $ss->get('theme.hero_title', '') }}</h1>
                @if($ss->get('theme.hero_subtitle'))
                <p class="sf-hero-sub">{{ $ss->get('theme.hero_subtitle') }}</p>
                @endif
                @if($ss->get('theme.hero_cta_text'))
                <a href="{{ $heroSlides[0]['link'] ?? $ss->get('theme.hero_cta_link', '/search') }}" class="sf-hero-cta" id="heroCtaBtn" style="pointer-events: auto;">{{ $ss->get('theme.hero_cta_text') }}</a>
                @endif
            </div>
            @endif
        </section>
        @endif
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
                                <div style="position:absolute;top:0;right:0;bottom:0;left:0;background:var(--color-bg-elevated);"></div>
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
                            <p style="color: var(--color-text-secondary); font-size: 13px; line-height: 1.6; font-style: italic; flex: 1; margin-bottom: 0;">
                                "{{ \Illuminate\Support\Str::limit($review->body, 120, '') }}"@if(strlen($review->body) > 120)...
                                <br>
                                @if($review->product)
                                    <a href="{{ route('product.show', $review->product->slug) }}#reviews" style="color: var(--color-gold); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-style: normal; display: inline-block; margin-top: 6px; text-decoration: none;">Read more <i class="bi bi-chevron-right" style="font-size: 9px;"></i></a>
                                @endif
                                @endif
                            </p>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--color-text-primary);">{{ $review->reviewer_name }}</div>
                                @if ($review->verified_purchase)
                                    <span style="color: var(--color-success); font-size: 11px; font-weight: 600;"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                @endif
                            </div>
                            @if($review->product)
                                <div style="color: var(--color-text-muted); font-size: 11px; margin-top: 8px;">
                                    on <a href="{{ route('product.show', $review->product->slug) }}#reviews" style="color: inherit; text-decoration: underline; transition: color 0.2s;" onmouseover="this.style.color='var(--color-gold)'" onmouseout="this.style.color='inherit'">{{ $review->product->name }}</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endforeach

@endsection

@push('scripts')
<script>
(function() {
    var slider = document.getElementById('heroSlider');
    if (!slider) return;

    var track = slider.querySelector('.sf-hero-track');
    var slides = slider.querySelectorAll('.sf-hero-slide');
    var dots = slider.querySelectorAll('.sf-hero-dot');
    var ctaBtn = document.getElementById('heroCtaBtn');
    var total = slides.length;

    // Sync CTA button href with current slide's link
    function syncCta(index) {
        if (!ctaBtn) return;
        var link = slides[index] ? slides[index].dataset.link : '';
        if (link) {
            ctaBtn.href = link;
            ctaBtn.style.display = '';
        } else {
            ctaBtn.style.display = 'none';
        }
    }

    if (total <= 1) {
        // Single slide — just make it clickable
        if (total === 1 && slides[0].dataset.link) {
            slides[0].style.cursor = 'pointer';
            slides[0].addEventListener('click', function() {
                if (this.dataset.link) window.location.href = this.dataset.link;
            });
        }
        syncCta(0);
        return;
    }

    var current = 0;
    var interval = null;
    var DELAY = 4000;

    function goTo(i) {
        current = ((i % total) + total) % total;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        for (var d = 0; d < dots.length; d++) {
            if (d === current) { dots[d].classList.add('active'); }
            else { dots[d].classList.remove('active'); }
        }
        syncCta(current);
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAuto() { stopAuto(); interval = setInterval(next, DELAY); }
    function stopAuto() { if (interval) { clearInterval(interval); interval = null; } }

    // Arrows
    var prevBtn = slider.querySelector('.sf-hero-prev');
    var nextBtn = slider.querySelector('.sf-hero-next');
    if (prevBtn) prevBtn.addEventListener('click', function(e) { e.stopPropagation(); prev(); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function(e) { e.stopPropagation(); next(); startAuto(); });

    // Dots
    for (var d = 0; d < dots.length; d++) {
        (function(idx) {
            dots[idx].addEventListener('click', function() { goTo(idx); startAuto(); });
        })(d);
    }

    // Slide click → navigate
    for (var s = 0; s < slides.length; s++) {
        (function(slide) {
            slide.style.cursor = slide.dataset.link ? 'pointer' : 'default';
            slide.addEventListener('click', function() {
                if (this.dataset.link) window.location.href = this.dataset.link;
            });
        })(slides[s]);
    }

    // Pause on hover
    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    // Touch swipe
    var startX = 0, startY = 0;
    slider.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        stopAuto();
    }, { passive: true });
    slider.addEventListener('touchend', function(e) {
        var dx = startX - e.changedTouches[0].clientX;
        var dy = startY - e.changedTouches[0].clientY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
            if (dx > 0) next(); else prev();
        }
        startAuto();
    }, { passive: true });

    // Initial CTA sync
    syncCta(0);

    // Start autoplay
    startAuto();
})();
</script>
@endpush
