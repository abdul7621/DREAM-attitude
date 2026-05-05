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
    $sectionsRaw = $ss->get('theme.home_sections', '[]');
    $sections = is_array($sectionsRaw) ? $sectionsRaw : (is_string($sectionsRaw) ? json_decode($sectionsRaw, true) : []);
    if (empty($sections)) {
        $sections = ['hero', 'trust_strip', 'categories', 'usp_strip', 'bestsellers', 'offers_banner', 'reviews', 'featured', 'instagram_follow', 'award_section'];
    }

    // Force usp_strip right after categories (regardless of DB order)
    $sectionKeys = array_map(fn($s) => is_array($s) ? ($s['key'] ?? '') : $s, $sections);
    $uspIdx = array_search('usp_strip', $sectionKeys);
    $catIdx = array_search('categories', $sectionKeys);
    if ($uspIdx !== false && $catIdx !== false && $uspIdx !== $catIdx + 1) {
        $uspItem = $sections[$uspIdx];
        array_splice($sections, $uspIdx, 1);
        // Recalculate catIdx after splice
        $sectionKeys2 = array_map(fn($s) => is_array($s) ? ($s['key'] ?? '') : $s, $sections);
        $catIdx2 = array_search('categories', $sectionKeys2);
        array_splice($sections, $catIdx2 + 1, 0, [$uspItem]);
    }
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
                    <a href="{{ $slide['link'] ?? $heroCta1Link }}" style="display:block; width:100%;">
                        <div class="sf-hero-img-wrap">
                            <picture class="sf-hero-picture">
                                @if(!empty($slide['image_mobile']))
                                <source media="(max-width: 768px)" srcset="{{ asset('storage/' . $slide['image_mobile']) }}" width="1080" height="1350">
                                @endif
                                <img src="{{ asset('storage/' . $slide['image']) }}"
                                     alt="{{ $slide['alt'] ?? $heroTitle }}"
                                     width="1920" height="800"
                                     fetchpriority="{{ $idx === 0 ? 'high' : 'auto' }}"
                                     loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                                     class="sf-hero-img">
                            </picture>
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
        <div class="sf-trust-strip" data-aos="fade-up">
            @foreach($trustItems as $item)
            <div class="sf-trust-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="sf-trust-val counter" data-target="{{ intval(preg_replace('/[^0-9]/', '', $item['val'] ?? '0')) }}" data-suffix="{{ preg_replace('/[0-9]/', '', $item['val'] ?? '') }}">{{ $item['val'] ?? '' }}</div>
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
        {{-- ══ CATEGORIES — 3 Big Cards Layout ═════════════════ --}}
        <section class="sf-section" style="background:var(--color-bg-primary); padding: 48px 0;">
            <div class="sf-container">
                <div style="text-align:center; margin-bottom:40px;" data-aos="fade-up">
                    <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Curated For You' }}</p>
                    <h2 class="sf-section-title" style="margin:0 auto; color:var(--color-text-primary);">{{ $sTitle ?? 'Shop by Category' }}</h2>
                </div>
                <div class="sf-category-grid">
                    @foreach ($categories->take(3) as $cat)
                    <a href="{{ route('category.show', $cat) }}" class="sf-category-banner" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 100 }}">
                        @if ($cat->image_path)
                            <img src="{{ asset('storage/'.$cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                        @else
                            {{-- Fallback placeholder --}}
                            <div style="width:100%; height:100%; background:#222; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-image text-muted" style="font-size:3rem;"></i>
                            </div>
                        @endif
                        <div class="sf-category-content">
                            <h3>{{ $cat->name }}</h3>
                            <div class="sf-category-cta">Shop Now <i class="bi bi-arrow-right ms-1"></i></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($sectionKey === 'usp_strip')
        {{-- ══ BRAND AUTHORITY BLOCK — Hardcoded (Not admin-configurable generic USP) ═══════════════ --}}
        <div class="sf-usp-strip">
            <h2 class="sf-usp-section-title" data-aos="fade-up">45+ Years of Beauty Expertise. Trusted by Thousands.</h2>
            <p class="sf-usp-section-sub" data-aos="fade-up" data-aos-delay="100">Powered by NR Beauty World — a name dominating the beauty market for over 45 years.</p>
            <div class="sf-usp-grid">
                <div class="sf-usp-item">
                    <div class="sf-usp-icon-wrap"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="sf-usp-title">45+ Years of Industry Experience</div>
                        <div class="sf-usp-desc">Backed by decades of real-world salon and retail expertise.</div>
                    </div>
                </div>
                <div class="sf-usp-item">
                    <div class="sf-usp-icon-wrap"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="sf-usp-title">Trusted by 17,000+ Customers</div>
                        <div class="sf-usp-desc">A brand built on consistent performance and repeat buyers.</div>
                    </div>
                </div>
                <div class="sf-usp-item">
                    <div class="sf-usp-icon-wrap"><i class="bi bi-star-fill"></i></div>
                    <div>
                        <div class="sf-usp-title">4.9★ from 16,000+ Reviews</div>
                        <div class="sf-usp-desc">Real feedback from real customers across India.</div>
                    </div>
                </div>
                <div class="sf-usp-item">
                    <div class="sf-usp-icon-wrap"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <div class="sf-usp-title">Built for Scale. Designed for Trust.</div>
                        <div class="sf-usp-desc">Not a new brand — a proven system.</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($sectionKey === 'bestsellers' && isset($bestsellers) && $bestsellers->isNotEmpty())
        {{-- ══ BESTSELLERS (The Bento Box Core) ══════════════════════════════ --}}
        <section class="sf-section sf-section-white" style="background:var(--color-bg-primary);">
            <div class="sf-container">
                <div class="sf-section-header-row sf-animate">
                    <div>
                        <p class="sf-section-eyebrow">{{ $section['eyebrow'] ?? 'Top Picks' }}</p>
                        <h2 class="sf-section-title" style="color:var(--color-text-primary);">{{ $sTitle ?? 'Bestsellers' }}</h2>
                    </div>
                    <a class="sf-view-all" href="{{ route('search', ['sort' => 'bestseller']) }}" style="color:var(--color-gold);">View All</a>
                </div>
                
                {{-- BENTO GRID --}}
                <div class="sf-bento-grid">
                    @foreach ($bestsellers as $index => $product)
                        @php
                            // Index matching for Bento layout mapping
                            $bentoClass = 'sf-bento-item-medium';
                            if ($index === 0 || $index === 5 || $index === 10 || $index === 15) {
                                $bentoClass = 'sf-bento-item-high';
                            }
                        @endphp
                        <x-product-card :product="$product" bentoClass="{{ $bentoClass }}" />
                    @endforeach
                </div>
                
                {{-- FUNNEL CATCH CTA --}}
                <div class="sf-funnel-catch sf-animate">
                    <div class="sf-funnel-catch-glow"></div>
                    <h3>Still Confused? Let Us Guide You</h3>
                    <p>The perfect match for your needs is waiting in our complete collection.</p>
                    <a href="{{ route('search') }}" class="btn" style="background:#fff; color:#0A0A0A; font-weight:700; padding:12px 32px; border-radius:100px; text-transform:uppercase; letter-spacing:1px; font-size:13px;">Find Your Perfect Product <i class="bi bi-arrow-right ms-2"></i></a>
                </div>

                {{-- PROBLEM -> SOLUTION MATRIX ENGINE --}}
                @php
                    $matrixRaw = $ss->get('theme.problem_matrix', '');
                    $matrixItems = is_string($matrixRaw) ? json_decode($matrixRaw, true) : (is_array($matrixRaw) ? $matrixRaw : []);
                @endphp
                @if(!empty($matrixItems))
                <div class="sf-ps-engine sf-animate" style="margin-top:80px;">
                    <div class="sf-ps-tabs" id="psTabs">
                        <div style="margin-bottom:24px;">
                            <p class="sf-section-eyebrow" style="margin-bottom:4px;">Diagnostic Tool</p>
                            <h2 style="font-family:'Playfair Display',serif; font-size:32px; color:var(--color-text-primary); margin:0;">Target Solutions</h2>
                            <p style="color:var(--color-text-muted); font-size:14px; margin-top:8px; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-hand-index-thumb fs-5" style="color:var(--color-gold);"></i> 
                                Tap on a problem below to reveal tailored product solutions.
                            </p>
                        </div>
                        @foreach($matrixItems as $i => $mItem)
                            @if(!empty($mItem['problem']))
                                <div class="sf-ps-tab" data-target="ps-panel-{{$i}}">
                                    <span>{{ $mItem['problem'] }}</span>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <div class="sf-ps-contents" id="psContents">
                        @foreach($matrixItems as $i => $mItem)
                            @if(!empty($mItem['problem']))
                                @php
                                    $pIds = array_map('trim', explode(',', $mItem['products'] ?? ''));
                                    $pIds = array_filter($pIds);
                                    $psProducts = \App\Models\Product::whereIn('id', $pIds)->with(['variants'])->take(3)->get();
                                    
                                    // Order them exactly as IDs were entered if possible, else take directly.
                                    if(count($pIds) > 0) {
                                      $psProducts = $psProducts->sortBy(function($model) use ($pIds) {
                                          return array_search($model->id, $pIds);
                                      });
                                    }
                                @endphp
                                <div class="sf-ps-content" id="ps-panel-{{$i}}">
                                    @if($psProducts->isNotEmpty())
                                        <div class="sf-ps-solution-grid">
                                            @foreach($psProducts as $pi => $psP)
                                                <div class="{{ $loop->first ? 'sf-ps-primary' : '' }}">
                                                    <x-product-card :product="$psP" bentoClass="" />
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="background:var(--color-bg-surface); padding:32px; border-radius:var(--radius-md); text-align:center; color:var(--color-text-muted);">
                                            Products resolving to IDs "{{ $mItem['products'] ?? '' }}" were not found.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
                {{-- END MATRIX --}}

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
                    <i class="bi bi-instagram"></i> Follow {{ '@' . $igHandle }}
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
        <div class="sf-award-section" data-aos="fade-up">
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
                            <div class="sf-award-stat-num counter" data-target="{{ intval(preg_replace('/[^0-9]/', '', $stat['num'])) }}" data-suffix="{{ preg_replace('/[0-9]/', '', $stat['num']) }}">{{ $stat['num'] }}</div>
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

{{-- Review & Categories Auto Slider JS (Performance Optimized) --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    function initNativeSlider(sliderId, trackClass, itemClass, intervalSpeed) {
        var slider = document.getElementById(sliderId) || document.querySelector(sliderId);
        if(!slider) return;
        var track = typeof trackClass === 'string' ? slider.querySelector(trackClass) : trackClass;
        if(!track) return;
        
        var timer = null;
        function autoSlide() {
            var maxScroll = track.scrollWidth - track.clientWidth;
            if (maxScroll <= 0) return; // No need to slide
            
            if (track.scrollLeft + 10 >= maxScroll) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                var item = track.querySelector(itemClass);
                if(!item) return;
                var itemWidth = item.getBoundingClientRect().width;
                var style = window.getComputedStyle(track);
                var gap = parseFloat(style.gap) || 0;
                track.scrollBy({ left: itemWidth + gap, behavior: 'smooth' });
            }
        }
        
        function start() { stop(); timer = setInterval(autoSlide, intervalSpeed); }
        function stop() { if(timer){ clearInterval(timer); timer = null; } }
        
        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('touchstart', stop, {passive: true});
        slider.addEventListener('touchend', start, {passive: true});
        start();
    }

    // Init Reviews (Home)
    initNativeSlider('reviewSlider', '.sf-review-track', '.sf-review-slide', 4000);
    // Init Categories (Home)
    initNativeSlider('.sf-cat-circle-wrap', document.querySelector('.sf-cat-circle-wrap'), '.sf-cat-circle-item', 3500);
});
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

{{-- Problem Solution Engine Tool --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    var psTabs = document.querySelectorAll('.sf-ps-tab');
    var psContents = document.querySelectorAll('.sf-ps-content');
    
    psTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var targetId = tab.getAttribute('data-target');
            
            // Deactivate all
            psTabs.forEach(function(t) { t.classList.remove('active'); });
            psContents.forEach(function(c) { c.classList.remove('active'); });
            
            // Activate selected
            tab.classList.add('active');
            var targetContent = document.getElementById(targetId);
            if(targetContent) targetContent.classList.add('active');
        });
    });
});



</script>
@endpush
