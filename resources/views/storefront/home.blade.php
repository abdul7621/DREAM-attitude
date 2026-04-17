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
    $sections = json_decode($ss->get('theme.home_sections', '[]'), true) ?: ['hero', 'trust_strip', 'categories', 'usp_strip', 'bestsellers', 'offers_banner', 'featured', 'award_section', 'reviews'];
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
        {{-- ══ HERO SPLIT — Premium DNA ═══════════════════════════════════════════ --}}
        @php
            $heroSlides = $ss->get('theme.hero_slides');
            $heroSlides = is_array($heroSlides) ? $heroSlides : [];
            if (empty($heroSlides) && $ss->get('theme.hero_image')) {
                $heroSlides = [['image' => $ss->get('theme.hero_image'), 'link' => $ss->get('theme.hero_cta_link', '/search'), 'alt' => $ss->get('theme.hero_title', '')]];
            }
            $heroTitle = $ss->get('theme.hero_title', 'Salon Quality');
            $heroSubtitle = $ss->get('theme.hero_subtitle', '400+ premium beauty products — hair care, skin care, fragrances, and professional salon essentials. No parabens. No SLS. Always cruelty-free.');
            $heroCta1Text = $ss->get('theme.hero_cta_text', 'Shop Now');
            $heroCta1Link = $ss->get('theme.hero_cta_link', '/search');
            $heroCta2Text = $ss->get('theme.hero_cta2_text', '');
            $heroCta2Link = $ss->get('theme.hero_cta2_link', '');
            $heroAward = $ss->get('theme.hero_award_text', '★ India\'s Most Promising Brand 2021');
            $heroEyebrow = $ss->get('theme.hero_eyebrow', 'Professional Beauty, Delivered');
            $slideCount = count($heroSlides);
        @endphp

        {{-- Desktop: split layout | Mobile: text above, images below --}}
        <div class="sf-hero-split">
            {{-- Left: Content --}}
            <div class="sf-hero-split-left">
                @if($heroAward)
                <div class="sf-hero-award-badge">{{ $heroAward }}</div>
                @endif
                <p class="sf-hero-eyebrow">{{ $heroEyebrow }}</p>
                <h1 class="sf-hero-split-title">
                    {{ $heroTitle }}@if($ss->get('theme.hero_title_suffix'))<br><em>{{ $ss->get('theme.hero_title_suffix') }}</em>@endif
                </h1>
                <p class="sf-hero-split-sub">{{ $heroSubtitle }}</p>
                <div class="sf-hero-split-ctas">
                    @if($heroCta1Text)
                    <a href="{{ $heroCta1Link }}" class="sf-hero-split-btn-primary">{{ $heroCta1Text }}</a>
                    @endif
                    @if($heroCta2Text)
                    <a href="{{ $heroCta2Link }}" class="sf-hero-split-btn-outline">{{ $heroCta2Text }}</a>
                    @endif
                </div>
                {{-- Conversion micro-trust under CTA --}}
                <div class="sf-hero-micro-trust">
                    @php $freeThreshold = $ss->get('shipping.free_threshold', 499); @endphp
                    <span><i class="bi bi-truck"></i> Free Delivery above ₹{{ number_format($freeThreshold) }}</span>
                    <span><i class="bi bi-cash-coin"></i> Cash on Delivery Available</span>
                    <span><i class="bi bi-shield-check"></i> Trusted by Salons Across India</span>
                </div>
            </div>

            {{-- Right: Image Grid (admin managed via hero_slides, up to 4 images) --}}
            <div class="sf-hero-split-right">
                @if($slideCount > 0)
                    @foreach(array_slice($heroSlides, 0, 4) as $idx => $slide)
                    <div class="sf-hero-grid-img">
                        <a href="{{ $slide['link'] ?? $heroCta1Link }}" style="display:block;width:100%;height:100%;">
                            <img src="{{ asset('storage/' . $slide['image']) }}"
                                 alt="{{ $slide['alt'] ?? '' }}"
                                 loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        </a>
                        @if(!empty($slide['alt']))
                        <div class="sf-hero-grid-tag">{{ $slide['alt'] }}</div>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
    
    @if ($sectionKey === 'hero')
        {{-- SEO Content Block (Below Hero) --}}
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

    @if ($sectionKey === 'categories' && isset($categories) && $categories->isNotEmpty())
        {{-- ══ CATEGORIES ════════════════════════════════════════════════════════ --}}
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <div class="sf-section-header-row">
                    <div>
                        <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Explore' }}</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'Shop by Category' }}</h2>
                    </div>
                </div>
                <div class="sf-category-grid">
                    @foreach ($categories as $cat)
                    <a href="{{ route('category.show', $cat) }}" class="sf-cat-card">
                        @if ($cat->image_path)
                            <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                        @endif
                        <div class="cat-overlay"></div>
                        <label>{{ $cat->name }}</label>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'usp_strip')
        {{-- ══ USP STRIP ═════════════════════════════════════════════════════════ --}}
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
                    ['icon' => 'bi-stars', 'title' => 'No Paraben, No SLS, No Silicones', 'desc' => 'Clean formulas you can trust on your skin and hair daily'],
                    ['icon' => 'bi-heart', 'title' => 'Cruelty Free Always', 'desc' => 'Every product ethically developed, never tested on animals'],
                    ['icon' => 'bi-droplet', 'title' => 'Non-Alcoholic Fragrances', 'desc' => 'Attars and perfumes crafted for everyone, no alcohol used'],
                    ['icon' => 'bi-shop', 'title' => 'Trusted by Salons Across India', 'desc' => 'Professional-grade products for home and salon use alike'],
                ];
            }
        @endphp
        <div class="sf-usp-strip">
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
                <div class="sf-section-header-row">
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

    @if ($sectionKey === 'featured' && isset($featured) && $featured->isNotEmpty())
        {{-- ══ FEATURED PRODUCTS ══════════════════════════════════════════════════ --}}
        <section class="sf-section sf-section-cream">
            <div class="sf-container">
                <div class="sf-section-header-row">
                    <div>
                        <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Fresh In' }}</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'New Arrivals' }}</h2>
                    </div>
                    <a class="sf-view-all" href="{{ route('search') }}">View All</a>
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
        <section class="sf-section sf-section-white">
            <div class="sf-container">
                <div class="sf-section-header-row">
                    <div>
                        <p class="sf-section-eyebrow">Recently Added</p>
                        <h2 class="sf-section-title">{{ $sTitle ?? 'New Arrivals' }}</h2>
                    </div>
                </div>
                <div class="sf-product-grid">
                    @foreach ($latest as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'award_section')
        {{-- ══ AWARD / BRAND STORY ════════════════════════════════════════════════ --}}
        @php
            $awardTitle  = $ss->get('theme.brand_story_title', "India's Most Promising Beauty Brand");
            $awardText   = $ss->get('theme.brand_story_text', 'Recognized at the Asian Excellence Awards 2021, Dream Attitude brings you a legacy of trust and innovation across hair care, skin care, fragrances, and professional salon essentials. Trusted by salons, wholesalers, and customers across India.');
            $awardLink   = $ss->get('theme.brand_story_link', '');

            // Decode award images (stored as JSON string in DB)
            $awardImagesRaw = $ss->get('theme.award_images', '');
            if (is_array($awardImagesRaw)) {
                $awardImages = $awardImagesRaw;
            } elseif (is_string($awardImagesRaw) && !empty($awardImagesRaw)) {
                $awardImages = json_decode($awardImagesRaw, true) ?: [];
            } else {
                $awardImages = [];
            }

            // Decode award stats (stored as JSON string in DB)
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
                <div class="sf-award-left">
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

    @if ($sectionKey === 'reviews' && isset($topReviews) && $topReviews->isNotEmpty())
        {{-- ══ CUSTOMER REVIEWS ═══════════════════════════════════════════════════ --}}
        <section class="sf-section sf-section-white">
            <div class="sf-container">
                <div style="text-align:center;margin-bottom:36px;">
                    <p class="sf-section-eyebrow">Testimonials</p>
                    <h2 class="sf-section-title" style="margin:0 auto;">{{ $sTitle ?? 'What Our Customers Say' }}</h2>
                    <p style="color:var(--color-text-muted);font-size:13px;margin-top:8px;">{{ $sSubtitle ?? 'Real reviews from verified buyers across India' }}</p>
                </div>
                <div class="sf-review-grid">
                    @foreach ($topReviews as $review)
                    <div class="sf-review-card">
                        <div class="sf-review-stars">
                            @for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>@endfor
                        </div>
                        <p class="sf-review-text">"{{ \Illuminate\Support\Str::limit($review->body, 140) }}"</p>
                        <div class="sf-reviewer-name">{{ $review->reviewer_name }}</div>
                        @if($review->product)
                        <div class="sf-reviewer-product">on <a href="{{ route('product.show', $review->product->slug) }}" style="color:inherit;text-decoration:underline;">{{ $review->product->name }}</a></div>
                        @endif
                        @if($review->verified_purchase)
                        <div class="sf-reviewer-verified"><i class="bi bi-patch-check-fill"></i> Verified Purchase</div>
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
{{-- No extra scripts needed — existing slider JS removed since hero is now split-grid not a slider --}}
@endpush
