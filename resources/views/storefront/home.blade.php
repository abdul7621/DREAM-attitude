@extends('layouts.storefront')

@php
    $ss = app(\App\Services\SettingsService::class);
    $homeSeoTitle = $ss->get('theme.home_seo_title');
    $homeTitle = $homeSeoTitle ?: (config('app.name') . ' — ' . $ss->get('theme.hero_title', 'Premium Quality Products'));
    $homeSeoDesc = $ss->get('theme.home_seo_description');
@endphp
@section('title', $homeTitle)
@if($homeSeoDesc)
    @section('meta_description', $homeSeoDesc)
@endif
@section('content')
@php
    $ss = app(\App\Services\SettingsService::class);
    $sections = json_decode($ss->get('theme.home_sections', '[]'), true) ?: ['hero', 'trust_strip', 'categories', 'bestsellers', 'usp_strip', 'offers_banner', 'reviews', 'featured', 'instagram_follow', 'award_section'];
@endphp

@foreach($sections as $section)
    @php
        $sectionKey = is_array($section) ? ($section['key'] ?? '') : $section;
        $isEnabled = is_array($section) ? ($section['enabled'] ?? true) : true;
        $sTitle = is_array($section) ? ($section['title'] ?? null) : null;
        $sSubtitle = is_array($section) ? ($section['subtitle'] ?? null) : null;
    @endphp
    @if (!$isEnabled)
        @continue
    @endif

    @if ($sectionKey === 'hero')
        {{-- ══ HERO — Full-Width Image Slider (2560×1256, contain desktop, cover mobile) ═══ --}}
        @php
            $heroSlides = $ss->get('theme.hero_slides');
            $heroSlides = is_array($heroSlides) ? $heroSlides : [];
            if (empty($heroSlides) && $ss->get('theme.hero_image')) {
                $heroSlides = [['image' => $ss->get('theme.hero_image'), 'link' => $ss->get('theme.hero_cta_link', '/search'), 'alt' => $ss->get('theme.hero_title', '')]];
            }
            $heroSlides = array_slice($heroSlides, 0, 3);
            $slideCount = count($heroSlides);

            $heroOverlayEnabled = (bool) $ss->get('theme.hero_overlay_enabled', false);
            $heroTitle = $ss->get('theme.hero_title', '');
            $heroSubtitle = $ss->get('theme.hero_subtitle', '');
            $heroCta1Text = $ss->get('theme.hero_cta_text', '');
            $heroCta1Link = $ss->get('theme.hero_cta_link', '/search');
            $showOverlay = $heroOverlayEnabled && !empty($heroTitle);
        @endphp

        @if($slideCount > 0)
        <div class="sf-hero" id="heroSlider">
            <div class="sf-hero-track" id="heroTrack">
                @foreach($heroSlides as $idx => $slide)
                <div class="sf-hero-slide">
                    <a href="{{ $slide['link'] ?? $heroCta1Link }}" style="display:block;">
                        <div class="sf-hero-img-wrap">
                            <img src="{{ asset('storage/' . $slide['image']) }}"
                                 alt="{{ $slide['alt'] ?? $heroTitle }}"
                                 loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                                 class="sf-hero-img">
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            @if($showOverlay)
            <div class="sf-hero-overlay" style="background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.15) 50%, transparent 100%);"></div>
            <div class="sf-hero-content" style="text-align:center; left:50%; transform:translate(-50%,-50%); max-width:700px; width:90%;">
                <h1 class="sf-hero-title">{{ $heroTitle }}</h1>
                @if($heroSubtitle)
                <p class="sf-hero-sub" style="color:rgba(255,255,255,0.85); margin: 0 auto 24px;">{{ $heroSubtitle }}</p>
                @endif
                @if($heroCta1Text)
                <a href="{{ $heroCta1Link }}" class="sf-hero-cta">{{ $heroCta1Text }}</a>
                @endif
            </div>
            @endif

            @if($slideCount > 1)
            <div class="sf-hero-dots">
                @for($d = 0; $d < $slideCount; $d++)
                <button class="sf-hero-dot {{ $d === 0 ? 'active' : '' }}" data-slide="{{ $d }}"></button>
                @endfor
            </div>
            <button class="sf-hero-arrow sf-hero-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
            <button class="sf-hero-arrow sf-hero-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
            @endif
        </div>
        @else
        <div class="sf-hero" style="background: var(--color-dark); display:flex; align-items:center; justify-content:center;">
            <div class="sf-hero-img-wrap" style="display:flex; align-items:center; justify-content:center;">
                <div style="text-align:center; padding: 40px;">
                    <h1 class="sf-hero-title" style="color: var(--color-gold);">{{ $ss->get('theme.hero_title', config('app.name')) }}</h1>
                    <p style="color:rgba(255,255,255,0.6); font-size:15px;">{{ $ss->get('theme.hero_subtitle', 'Premium Beauty Products — Delivered to Your Door') }}</p>
                    <a href="{{ $heroCta1Link }}" class="sf-hero-cta" style="margin-top:20px;">{{ $ss->get('theme.hero_cta_text', 'Shop Now') }}</a>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if ($sectionKey === 'hero')
        @if ($ss->get('theme.home_seo_content'))
            <section class="sf-section py-4" style="background-color: var(--color-bg-subtle);">
                <div class="sf-container">
                    <div class="seo-content-block text-muted" style="font-size: 0.95rem; line-height: 1.6;">
                        {!! \Illuminate\Mail\Markdown::parse($ss->get('theme.home_seo_content')) !!}
                    </div>
                </div>
            </section>
        @endif
    @endif

    @if ($sectionKey === 'trust_strip')
        {{-- ══ TRUST STRIP ══════════════════════════════════════════════════════== --}}
        @php
            $trustRaw = $ss->get('theme.trust_strip_items', '');
            if (is_array($trustRaw)) {
                $trustItems = $trustRaw;
            } elseif (is_string($trustRaw) && !empty($trustRaw)) {
                $trustItems = json_decode($trustRaw, true) ?: [];
            } else {
                $trustItems = [];
            }
            if (empty($trustItems)) {
                $trustItems = [
                    ['val' => '400+', 'label' => 'Premium Products'],
                    ['val' => 'No Paraben · No SLS', 'label' => 'Clean Formulations'],
                    ['val' => 'Cruelty Free', 'label' => 'Ethically Crafted'],
                    ['val' => '60-Day Returns', 'label' => 'Hassle-Free Policy'],
                ];
            }
        @endphp
        <div class="sf-trust-strip">
            @foreach($trustItems as $item)
            <div class="sf-trust-item">
                <div class="sf-trust-val">{{ $item['val'] ?? '' }}</div>
                <div class="sf-trust-label">{{ $item['label'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    @endif

    @if ($sectionKey === 'benefits_strip')
        {{-- ══ BENEFITS STRIP (Outcome-based, Horizontal Scroll) ═══════════════ --}}
        @php
            $benefitsRaw = $ss->get('theme.benefits_items', '');
            if (is_array($benefitsRaw)) {
                $benefitsItems = $benefitsRaw;
            } elseif (is_string($benefitsRaw) && !empty($benefitsRaw)) {
                $benefitsItems = json_decode($benefitsRaw, true) ?: [];
            } else {
                $benefitsItems = [];
            }
            if (empty($benefitsItems)) {
                $benefitsItems = [
                    ['icon' => 'bi-droplet-half', 'label' => 'Hair Fall Control'],
                    ['icon' => 'bi-snow2', 'label' => 'Dandruff Reduction'],
                    ['icon' => 'bi-brilliance', 'label' => 'Salon Smooth Finish'],
                    ['icon' => 'bi-shield-check', 'label' => 'No Harsh Chemicals'],
                    ['icon' => 'bi-flower1', 'label' => 'Non-Alcoholic Fragrance'],
                ];
            }
        @endphp
        @if(!empty($benefitsItems))
        <div class="sf-benefits-strip">
            <div class="sf-benefits-scroll">
                @foreach($benefitsItems as $benefit)
                <div class="sf-benefit-item">
                    <div class="sf-benefit-circle">
                        @if(!empty($benefit['image']))
                            <img src="{{ asset('storage/' . $benefit['image']) }}" alt="{{ $benefit['label'] ?? '' }}" loading="lazy">
                        @else
                            <i class="bi {{ $benefit['icon'] ?? 'bi-check-circle' }}"></i>
                        @endif
                    </div>
                    <div class="sf-benefit-label">{{ $benefit['label'] ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    @if ($sectionKey === 'categories' && isset($categories) && $categories->isNotEmpty())
        {{-- ══ CATEGORIES — Circular Icons, Horizontal Scroll ═════════════════ --}}
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <div style="text-align:center; margin-bottom:32px;" class="sf-animate">
                    <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Explore' }}</p>
                    <h2 class="sf-section-title" style="margin:0 auto;">{{ $sTitle ?? 'Shop by Category' }}</h2>
                </div>
                <div class="sf-cat-circle-wrap">
                    @foreach ($categories as $cat)
                    <a href="{{ route('category.show', $cat) }}" class="sf-cat-circle-item">
                        <div class="sf-cat-circle-img">
                            @if ($cat->image_path)
                                <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                            @else
                                <i class="bi bi-grid"></i>
                            @endif
                        </div>
                        <span class="sf-cat-circle-name">{{ $cat->name }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'usp_strip')
        {{-- ══ WHY TRUST DREAM ATTITUDE — Brand Authority Block ═══════════════ --}}
        @php
            $uspRaw = $ss->get('theme.usp_strip_items', '');
            if (is_array($uspRaw)) {
                $uspItems = $uspRaw;
            } elseif (is_string($uspRaw) && !empty($uspRaw)) {
                $uspItems = json_decode($uspRaw, true) ?: [];
            } else {
                $uspItems = [];
            }
            if (empty($uspItems)) {
                $uspItems = [
                    ['icon' => 'bi-trophy', 'title' => '45+ Years Industry Experience', 'desc' => 'Powered by N.R. Beauty World — a legacy brand trusted since decades'],
                    ['icon' => 'bi-star-fill', 'title' => '17,000+ Google Reviews', 'desc' => '4.9★ rated — one of the highest rated beauty brands in India'],
                    ['icon' => 'bi-people-fill', 'title' => 'Trusted Across India', 'desc' => 'Thousands of salons and customers rely on Dream Attitude daily'],
                    ['icon' => 'bi-shop', 'title' => 'N.R. Beauty World Legacy', 'desc' => '45+ years of dominance in the professional beauty market'],
                ];
            }
        @endphp
        <div class="sf-usp-strip">
            <h2 class="sf-usp-section-title sf-animate">Why Trust Dream Attitude</h2>
            <p class="sf-usp-section-sub">Built on 45+ years of beauty industry expertise</p>
            <div class="sf-usp-grid">
                @foreach($uspItems as $usp)
                <div class="sf-usp-item">
                    <div class="sf-usp-icon-wrap"><i class="bi {{ $usp['icon'] ?? 'bi-stars' }}"></i></div>
                    <div>
                        <div class="sf-usp-title">{{ $usp['title'] ?? '' }}</div>
                        <div class="sf-usp-desc">{{ $usp['desc'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($sectionKey === 'bestsellers' && isset($bestsellers) && $bestsellers->isNotEmpty())
        {{-- ══ BESTSELLERS ════════════════════════════════════════════════════════ --}}
        <section class="sf-section sf-section-white">
            <div class="sf-container">
                <div class="sf-section-header-row sf-animate">
                    <div>
                        <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Top Picks' }}</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'Bestsellers' }}</h2>
                    </div>
                    <a class="sf-view-all" href="{{ route('search', ['sort' => 'bestseller']) }}">View All</a>
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
        {{-- ══ IMAGE BANNER ═══════════════════════════════════════════════════════ --}}
        @php
            $bannerImage = $ss->get('theme.offers_banner_image') ?: $ss->get('theme.image_banner_image');
            $bannerText = $ss->get('theme.offers_banner_text') ?: $ss->get('theme.image_banner_text');
            $bannerLink = $ss->get('theme.offers_banner_link') ?: $ss->get('theme.image_banner_link', '#');
        @endphp
        @if($bannerImage || $bannerText)
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <a href="{{ $bannerLink }}" style="display:block;position:relative;border-radius:var(--radius-md);overflow:hidden;">
                    @if($bannerImage)
                        <img src="{{ asset('storage/' . $bannerImage) }}" alt="{{ $bannerText ?? 'Promotion' }}" style="width:100%;border-radius:var(--radius-md);" loading="lazy">
                    @else
                        <div style="background:var(--color-plum);padding:48px;text-align:center;border-radius:var(--radius-md);">
                            <h3 style="font-size:24px;color:var(--color-gold);text-transform:uppercase;letter-spacing:2px;font-family:'Playfair Display',serif;">{{ $bannerText }}</h3>
                        </div>
                    @endif
                </a>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'reviews' && isset($topReviews) && $topReviews->isNotEmpty())
        {{-- ══ CUSTOMER REVIEWS — Card Slider (auto 5s, dots, swipe) ═══════════ --}}
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <div style="text-align:center;margin-bottom:36px;" class="sf-animate">
                    <p class="sf-section-eyebrow">Testimonials</p>
                    <h2 class="sf-section-title" style="margin:0 auto;">{{ $sTitle ?? 'What Our Customers Say' }}</h2>
                    <p style="color:var(--color-text-muted);font-size:13px;margin-top:8px;">{{ $sSubtitle ?? 'Real reviews from verified buyers across India' }}</p>
                </div>
                <div class="sf-review-slider" id="reviewSlider">
                    <div class="sf-review-track" id="reviewTrack">
                        @foreach ($topReviews as $review)
                        <div class="sf-review-slide">
                            <div class="sf-review-card">
                                <div class="sf-review-stars">
                                    @for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>@endfor
                                </div>
                                <p class="sf-review-text">"{{ \Illuminate\Support\Str::limit($review->body, 200) }}"</p>
                                <div class="sf-reviewer-row">
                                    <div class="sf-review-avatar">{{ strtoupper(mb_substr($review->reviewer_name, 0, 1)) }}</div>
                                    <div style="text-align:left;">
                                        <div class="sf-reviewer-name">{{ $review->reviewer_name }}</div>
                                        <div class="sf-reviewer-role">Beauty Enthusiast</div>
                                        @if($review->verified_purchase)
                                        <div class="sf-reviewer-verified"><i class="bi bi-patch-check-fill"></i> Verified Purchase</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($topReviews->count() > 1)
                    <div class="sf-review-dots">
                        @for($rd = 0; $rd < $topReviews->count(); $rd++)
                        <button class="sf-review-dot {{ $rd === 0 ? 'active' : '' }}" data-slide="{{ $rd }}"></button>
                        @endfor
                    </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'featured' && isset($featured) && $featured->isNotEmpty())
        {{-- ══ FEATURED PRODUCTS ══════════════════════════════════════════════════ --}}
        <section class="sf-section sf-section-white">
            <div class="sf-container">
                <div class="sf-section-header-row sf-animate">
                    <div>
                        <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Fresh In' }}</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'New Arrivals' }}</h2>
                    </div>
                </div>
                <div class="sf-product-grid">
                    @foreach ($featured as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div style="text-align:center;">
                    <a href="{{ route('search') }}" class="sf-view-all-btn">View All Products</a>
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'latest' && isset($latest) && $latest->isNotEmpty())
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <div class="sf-section-header-row sf-animate">
                    <div>
                        <p class="sf-section-eyebrow">Recently Added</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'More Products' }}</h2>
                    </div>
                </div>
                <div class="sf-product-grid">
                    @foreach ($latest as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div style="text-align:center;">
                    <a href="{{ route('search') }}" class="sf-view-all-btn">Explore More</a>
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'instagram_follow')
        {{-- ══ INSTAGRAM FOLLOW — Admin Controlled ═════════════════════════════ --}}
        @php
            $igHandle = $ss->get('theme.instagram_handle', 'dream_attitude_international');
        @endphp
        @if(!empty($igHandle))
        <section class="sf-instagram-section">
            <div class="sf-instagram-inner">
                <div class="sf-instagram-icon"><i class="bi bi-instagram"></i></div>
                <a href="https://www.instagram.com/{{ $igHandle }}/" target="_blank" rel="noopener" class="sf-instagram-cta">
                    <i class="bi bi-instagram"></i> Follow @{{ $igHandle }}
                </a>
                <p class="sf-instagram-proof">Join 10,000+ Happy Customers</p>
            </div>
        </section>
        @endif
    @endif

    @if ($sectionKey === 'award_section')
        {{-- ══ AWARD / BRAND STORY ════════════════════════════════════════════════ --}}
        @php
            $awardTitle  = $ss->get('theme.brand_story_title', "India's Most Promising Beauty Brand");
            $awardText   = $ss->get('theme.brand_story_text', 'Recognized at the Asian Excellence Awards 2021, Dream Attitude brings you a legacy of trust and innovation across hair care, skin care, fragrances, and professional salon essentials. Trusted by salons, wholesalers, and customers across India.');
            $awardLink   = $ss->get('theme.brand_story_link', '');

            $awardImagesRaw = $ss->get('theme.award_images', '');
            if (is_array($awardImagesRaw)) {
                $awardImages = $awardImagesRaw;
            } elseif (is_string($awardImagesRaw) && !empty($awardImagesRaw)) {
                $awardImages = json_decode($awardImagesRaw, true) ?: [];
            } else {
                $awardImages = [];
            }

            $awardStatsRaw = $ss->get('theme.award_stats', '');
            if (is_array($awardStatsRaw)) {
                $awardStats = $awardStatsRaw;
            } elseif (is_string($awardStatsRaw) && !empty($awardStatsRaw)) {
                $awardStats = json_decode($awardStatsRaw, true) ?: [];
            } else {
                $awardStats = [];
            }
            if (empty($awardStats)) {
                $awardStats = [
                    ['num' => '400+', 'label' => 'Products'],
                    ['num' => '2021', 'label' => 'Award Year'],
                    ['num' => 'PAN India', 'label' => 'Delivery'],
                ];
            }
        @endphp
        <div class="sf-award-section">
            <div class="sf-award-inner">
                <div class="sf-award-left sf-animate">
                    <p class="sf-award-eyebrow">Our Story</p>
                    <h2 class="sf-award-title">{{ $awardTitle }}</h2>
                    <p class="sf-award-desc">{{ $awardText }}</p>
                    @if($awardLink && $awardLink !== '#')
                    <a href="{{ $awardLink }}" class="sf-award-link">Read Our Story</a>
                    @endif
                    <div class="sf-award-stats">
                        @foreach(array_slice($awardStats, 0, 3) as $stat)
                        <div class="sf-award-stat">
                            <div class="sf-award-stat-num">{{ $stat['num'] }}</div>
                            <div class="sf-award-stat-label">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="sf-award-right">
                    @if(!empty($awardImages))
                        @foreach(array_slice($awardImages, 0, 4) as $aImg)
                        <div class="sf-award-img-block">
                            <img src="{{ asset('storage/' . $aImg) }}" alt="Dream Attitude" loading="lazy">
                        </div>
                        @endforeach
                    @elseif(isset($bestsellers) && $bestsellers->isNotEmpty())
                        @foreach($bestsellers->take(4) as $bp)
                            @if($bp->primaryImage())
                            <div class="sf-award-img-block">
                                <img src="{{ asset('storage/'.$bp->primaryImage()->path) }}" alt="{{ $bp->name }}" loading="lazy">
                            </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($sectionKey === 'text_with_image')
        {{-- ── Legacy brand story block (backward compat) ───────────────────────── --}}
        @php
            $storyTitle = $ss->get('theme.brand_story_title');
            $storyText  = $ss->get('theme.brand_story_text');
            $storyImage = $ss->get('theme.brand_story_image');
        @endphp
        @if($storyTitle || $storyText)
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                @php $gridCols = $storyImage ? 'repeat(auto-fit, minmax(300px, 1fr))' : '1fr'; @endphp
                <div style="display:grid;gap:32px;grid-template-columns:{{ $gridCols }};align-items:center;">
                    @if($storyImage)
                    <div><img src="{{ asset('storage/' . $storyImage) }}" alt="{{ $storyTitle ?? '' }}" style="width:100%;border-radius:var(--radius-md);" loading="lazy"></div>
                    @endif
                    <div>
                        @if($storyTitle)
                        <h2 class="sf-section-title" style="margin-bottom:16px;">{{ $storyTitle }}</h2>
                        @endif
                        @if($storyText)
                        <p style="color:var(--color-text-secondary);font-size:14px;line-height:1.7;">{{ $storyText }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @endif
    @endif

@endforeach

@endsection

@push('scripts')
{{-- Hero Slider JS (lightweight, no dependencies) --}}
<script>
(function() {
    var slider = document.getElementById('heroSlider');
    if (!slider) return;

    var track = document.getElementById('heroTrack');
    var slides = track.querySelectorAll('.sf-hero-slide');
    var dots = slider.querySelectorAll('.sf-hero-dot');
    var prevBtn = slider.querySelector('.sf-hero-prev');
    var nextBtn = slider.querySelector('.sf-hero-next');
    var current = 0;
    var total = slides.length;
    if (total <= 1) return;

    var autoInterval = 5000;
    var autoTimer = null;

    function goTo(idx) {
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        current = idx;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        for (var d = 0; d < dots.length; d++) {
            dots[d].classList.toggle('active', d === current);
        }
    }

    function startAuto() {
        stopAuto();
        autoTimer = setInterval(function() { goTo(current + 1); }, autoInterval);
    }
    function stopAuto() {
        if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
    }

    if (nextBtn) nextBtn.addEventListener('click', function() { goTo(current + 1); startAuto(); });
    if (prevBtn) prevBtn.addEventListener('click', function() { goTo(current - 1); startAuto(); });
    for (var i = 0; i < dots.length; i++) {
        dots[i].addEventListener('click', (function(idx) {
            return function() { goTo(idx); startAuto(); };
        })(i));
    }

    // Touch swipe support
    var startX = 0;
    var isDragging = false;
    slider.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopAuto();
    }, { passive: true });
    slider.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            goTo(diff > 0 ? current + 1 : current - 1);
        }
        startAuto();
    }, { passive: true });

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    startAuto();
})();
</script>

{{-- Review Slider JS (auto 5s, dots, pause on hover, swipe) --}}
<script>
(function() {
    var slider = document.getElementById('reviewSlider');
    if (!slider) return;

    var track = document.getElementById('reviewTrack');
    var slides = track.querySelectorAll('.sf-review-slide');
    var dots = slider.querySelectorAll('.sf-review-dot');
    var current = 0;
    var total = slides.length;
    if (total <= 1) return;

    var autoInterval = 5000;
    var autoTimer = null;

    function goTo(idx) {
        if (idx < 0) idx = total - 1;
        if (idx >= total) idx = 0;
        current = idx;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        for (var d = 0; d < dots.length; d++) {
            dots[d].classList.toggle('active', d === current);
        }
    }

    function startAuto() {
        stopAuto();
        autoTimer = setInterval(function() { goTo(current + 1); }, autoInterval);
    }
    function stopAuto() {
        if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
    }

    for (var i = 0; i < dots.length; i++) {
        dots[i].addEventListener('click', (function(idx) {
            return function() { goTo(idx); startAuto(); };
        })(i));
    }

    // Touch swipe
    var startX = 0;
    var isDragging = false;
    slider.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        isDragging = true;
        stopAuto();
    }, { passive: true });
    slider.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            goTo(diff > 0 ? current + 1 : current - 1);
        }
        startAuto();
    }, { passive: true });

    // Pause on hover
    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    startAuto();
})();
</script>

{{-- Scroll Reveal (section headers only) --}}
<script>
(function() {
    if (!('IntersectionObserver' in window)) {
        var els = document.querySelectorAll('.sf-animate');
        for (var i = 0; i < els.length; i++) els[i].classList.add('sf-visible');
        return;
    }
    var observer = new IntersectionObserver(function(entries) {
        for (var i = 0; i < entries.length; i++) {
            if (entries[i].isIntersecting) {
                entries[i].target.classList.add('sf-visible');
                observer.unobserve(entries[i].target);
            }
        }
    }, { threshold: 0.15 });
    var targets = document.querySelectorAll('.sf-animate');
    for (var j = 0; j < targets.length; j++) observer.observe(targets[j]);
})();
</script>
@endpush
