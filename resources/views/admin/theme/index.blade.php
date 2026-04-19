@extends('layouts.admin')
@section('title', 'Theme Builder')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Theme Builder</h1>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-shop-window"></i> Preview Store
    </a>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'theme' ? 'active' : '' }}" href="{{ route('admin.theme.index', ['tab' => 'theme']) }}">
            <i class="bi bi-palette me-1"></i> Global Theme
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'homepage' ? 'active' : '' }}" href="{{ route('admin.theme.index', ['tab' => 'homepage']) }}">
            <i class="bi bi-layout-text-window me-1"></i> Homepage Sections
        </a>
    </li>
</ul>

<form action="{{ route('admin.theme.update') }}" method="post" enctype="multipart/form-data">
@csrf @method('PUT')
<input type="hidden" name="_tab" value="{{ $tab }}">
<div class="row g-4">

@if ($tab === 'theme')
    {{-- Left Column: Colors & Fonts --}}
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-palette me-2"></i> Brand Colors & Typography</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Primary Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="theme_primary_color" class="form-control form-control-color bg-light" value="{{ $theme['theme.primary_color'] ?? '#0d6efd' }}" style="max-width:60px;">
                            <input type="text" class="form-control" value="{{ $theme['theme.primary_color'] ?? '#0d6efd' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="theme_secondary_color" class="form-control form-control-color bg-light" value="{{ $theme['theme.secondary_color'] ?? '#6c757d' }}" style="max-width:60px;">
                            <input type="text" class="form-control" value="{{ $theme['theme.secondary_color'] ?? '#6c757d' }}" disabled>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="form-label">Font Family</label>
                        <select name="theme_font_family" class="form-select">
                            <option value="Inter, sans-serif" {{ ($theme['theme.font_family'] ?? '') === 'Inter, sans-serif' ? 'selected' : '' }}>Inter (Modern & Clean)</option>
                            <option value="'Outfit', sans-serif" {{ ($theme['theme.font_family'] ?? '') === "'Outfit', sans-serif" ? 'selected' : '' }}>Outfit (Geometric & Bold)</option>
                            <option value="'Roboto', sans-serif" {{ ($theme['theme.font_family'] ?? '') === "'Roboto', sans-serif" ? 'selected' : '' }}>Roboto (Classic & Readable)</option>
                            <option value="'Lora', serif" {{ ($theme['theme.font_family'] ?? '') === "'Lora', serif" ? 'selected' : '' }}>Lora (Elegant Serif)</option>
                        </select>
                        <div class="form-text">Requires page refresh to preview the new Google Font.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><i class="bi bi-border-style me-2"></i> Styling Options</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Base Border Radius</label>
                        <select name="theme_border_radius" class="form-select">
                            <option value="0" {{ ($theme['theme.border_radius'] ?? '') === '0' ? 'selected' : '' }}>Square (0px)</option>
                            <option value="0.375rem" {{ ($theme['theme.border_radius'] ?? '') === '0.375rem' ? 'selected' : '' }}>Slight Curve (6px)</option>
                            <option value="0.75rem" {{ ($theme['theme.border_radius'] ?? '') === '0.75rem' ? 'selected' : '' }}>Rounded (12px)</option>
                            <option value="1.5rem" {{ ($theme['theme.border_radius'] ?? '') === '1.5rem' ? 'selected' : '' }}>Pill (24px)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Button Style</label>
                        <select name="theme_button_style" class="form-select">
                            <option value="solid" {{ ($theme['theme.button_style'] ?? '') === 'solid' ? 'selected' : '' }}>Solid Fill</option>
                            <option value="outline" {{ ($theme['theme.button_style'] ?? '') === 'outline' ? 'selected' : '' }}>Outline Border</option>
                        </select>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Card Shadows</label>
                        <select name="theme_card_shadow" class="form-select">
                            <option value="none" {{ ($theme['theme.card_shadow'] ?? '') === 'none' ? 'selected' : '' }}>Flat (No shadow, Bordered)</option>
                            <option value="shadow-sm" {{ ($theme['theme.card_shadow'] ?? '') === 'shadow-sm' ? 'selected' : '' }}>Soft Elevation</option>
                            <option value="shadow" {{ ($theme['theme.card_shadow'] ?? '') === 'shadow' ? 'selected' : '' }}>Standard Shadow</option>
                        </select>
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="form-label">Spacing Scale</label>
                        <select name="theme_spacing_scale" class="form-select">
                            <option value="0.85" {{ ($theme['theme.spacing_scale'] ?? '') === '0.85' ? 'selected' : '' }}>Compact (Dense)</option>
                            <option value="1" {{ ($theme['theme.spacing_scale'] ?? '') === '1' ? 'selected' : '' }}>Normal (Standard)</option>
                            <option value="1.2" {{ ($theme['theme.spacing_scale'] ?? '') === '1.2' ? 'selected' : '' }}>Relaxed (Airy)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Assets & Saves --}}
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-images me-2"></i> Store Assets</div>
            <div class="card-body">
                
                <label class="form-label">Store Logo</label>
                @if(!empty($theme['theme.logo']))
                    <div class="mb-3 p-3 bg-light rounded text-center" style="max-height:120px;display:flex;align-items:center;justify-content:center;">
                        <img src="{{ asset('storage/' . $theme['theme.logo']) }}" alt="Logo" style="max-height:80px; max-width:100%;">
                    </div>
                @endif
                <input type="file" name="theme_logo" class="form-control mb-4" accept="image/*">

                <label class="form-label">Favicon (Browser Tab Icon)</label>
                @if(!empty($theme['theme.favicon']))
                    <div class="mb-3 text-center">
                        <img src="{{ asset('storage/' . $theme['theme.favicon']) }}" alt="Favicon" style="height:32px; width:32px" class="bg-light p-1 rounded border">
                    </div>
                @endif
                <input type="file" name="theme_favicon" class="form-control" accept="image/png, image/jpeg, image/ico">
                <div class="form-text">Recommended: 32x32px PNG or ICO format.</div>

            </div>
        </div>
        
        <div class="card border-primary mb-4 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-primary"><i class="bi bi-info-circle me-1"></i> Changes</h6>
                <p class="small text-muted mb-3">Theme configurations apply immediately globally across the storefront upon saving. Cache is flushed automatically.</p>
                <div class="d-grid">
                    <button class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Save Theme Settings</button>
                </div>
            </div>
        </div>
    </div>

@endif

@if ($tab === 'homepage')
    <div class="col-lg-8">
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white"><i class="bi bi-search me-2"></i> Homepage SEO Settings</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">SEO Title</label>
                    <input type="text" name="theme_home_seo_title" class="form-control" value="{{ $theme['theme.home_seo_title'] ?? '' }}" placeholder="e.g. {{ config('app.name') }} | Best D2C Platform India">
                    <div class="form-text">Overrides the default title tag on the homepage.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">SEO Description</label>
                    <textarea name="theme_home_seo_description" rows="2" class="form-control" placeholder="e.g. Shop the best products at {{ config('app.name') }}.">{{ $theme['theme.home_seo_description'] ?? '' }}</textarea>
                    <div class="form-text">Meta description for the homepage.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">SEO Content Block</label>
                    <textarea name="theme_home_seo_content" rows="4" class="form-control" placeholder="Write 150-300 words of keyword-rich content here... HTML is allowed.">{{ $theme['theme.home_seo_content'] ?? '' }}</textarea>
                    <div class="form-text">This will be displayed just below the Hero banner section to improve On-Page SEO.</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-layout-wtf me-2"></i> Section Ordering</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Check the sections you want to display and drag them or input order to arrange. Currently implemented as a simple Ordered List.</p>
                <div class="list-group mb-3">
                    @php
                        $available = [
                            'hero'             => '🖼️ Hero Banner (Slider)',
                            'trust_strip'      => '✅ Trust Strip (Dark Bar)',
                            'benefits_strip'   => '🎯 Benefits Strip (Circles)',
                            'categories'       => '🗂️ Category Circles',
                            'usp_strip'        => '🏆 Why Trust (Brand Authority)',
                            'bestsellers'      => '🔥 Bestsellers',
                            'offers_banner'    => '🏷️ Offers Banner',
                            'reviews'          => '💬 Customer Reviews (Slider)',
                            'featured'         => '🆕 Featured / New Arrivals',
                            'latest'           => '📦 Latest Products',
                            'instagram_follow' => '📸 Instagram Follow',
                            'award_section'    => '🏆 Award / Brand Story',
                        ];
                        $active = $theme['theme.home_sections'] ?? [];
                    @endphp
                    @foreach($active as $key)
                        @if(isset($available[$key]))
                            <div class="list-group-item d-flex align-items-center gap-3 bg-light">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="home_sections[]" value="{{ $key }}" checked>
                                    <label class="form-check-label fw-medium">{{ $available[$key] }}</label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($available as $key => $label)
                        @if(!in_array($key, $active))
                            <div class="list-group-item d-flex align-items-center gap-3">
                                <i class="bi bi-grip-vertical text-muted opacity-50"></i>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="home_sections[]" value="{{ $key }}">
                                    <label class="form-check-label text-muted">{{ $label }}</label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="form-text">Untick to hide a section from the homepage.</div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-images me-2"></i> Hero Slides</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Add banner slides with individual links. Design your banners with text baked into the image. <strong>Recommended: 1920×700px</strong></p>
                <div id="slidesContainer">
                @php $heroSlides = is_array($theme['theme.hero_slides'] ?? null) ? $theme['theme.hero_slides'] : []; @endphp
                @foreach($heroSlides as $i => $slide)
                    <div class="slide-row border rounded p-3 mb-3 position-relative bg-light">
                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:8px;z-index:2;" onclick="this.closest('.slide-row').remove()"><i class="bi bi-trash"></i></button>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3 text-center">
                                <label class="form-label small fw-bold mb-1">Desktop <small class="text-muted">(1920x800)</small> <span class="text-danger">*</span></label>
                                @if(!empty($slide['image']))
                                <img src="{{ asset('storage/' . $slide['image']) }}" class="img-fluid rounded border mb-1" style="max-height:60px;">
                                <input type="hidden" name="slide_existing[{{ $i }}]" value="{{ $slide['image'] }}">
                                @endif
                                <input type="file" name="slide_images[{{ $i }}]" class="form-control form-control-sm" accept="image/*" {{ empty($slide['image']) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-3 text-center">
                                <label class="form-label small fw-bold mb-1">Mobile <small class="text-muted">(1080x1350)</small> <span class="text-danger">*</span></label>
                                @if(!empty($slide['image_mobile']))
                                <img src="{{ asset('storage/' . $slide['image_mobile']) }}" class="img-fluid rounded border mb-1" style="max-height:60px;">
                                <input type="hidden" name="slide_existing_mobile[{{ $i }}]" value="{{ $slide['image_mobile'] }}">
                                @endif
                                <input type="file" name="slide_images_mobile[{{ $i }}]" class="form-control form-control-sm" accept="image/*" {{ empty($slide['image_mobile']) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small mb-1">Link URL</label>
                                        <input type="text" name="slide_links[{{ $i }}]" class="form-control form-control-sm" value="{{ $slide['link'] ?? '' }}" placeholder="/category/hair-care">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small mb-1">Alt Text</label>
                                        <input type="text" name="slide_alts[{{ $i }}]" class="form-control form-control-sm" value="{{ $slide['alt'] ?? '' }}" placeholder="Product name + benefit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addSlideBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add Slide
                </button>
                <span class="text-muted small ms-2">(Max 10)</span>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-type me-2"></i> Hero Text Overlay <span class="badge bg-secondary ms-1">Optional</span></div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="theme_hero_overlay_enabled" name="theme_hero_overlay_enabled" value="1" {{ !empty($theme['theme.hero_overlay_enabled']) ? 'checked' : '' }}>
                    <label class="form-check-label" for="theme_hero_overlay_enabled"><strong>Show Text Overlay on Slider</strong></label>
                    <div class="form-text">OFF = pure image slider (recommended if your banners already have text baked in). ON = title, subtitle & button shown on top of all slides.</div>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="theme_hero_title" class="form-control" value="{{ $theme['theme.hero_title'] ?? '' }}" placeholder="Salon Quality">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Title Suffix <span class="badge bg-secondary">Optional italic line</span></label>
                        <input type="text" name="theme_hero_title_suffix" class="form-control" value="{{ $theme['theme.hero_title_suffix'] ?? '' }}" placeholder="at Your Fingertips">
                        <div class="form-text">Italic text shown on a new line below the title. Leave empty to hide.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Eyebrow <span class="badge bg-secondary">NEW</span></label>
                        <input type="text" name="theme_hero_eyebrow" class="form-control" value="{{ $theme['theme.hero_eyebrow'] ?? '' }}" placeholder="Professional Beauty, Delivered">
                        <div class="form-text">Small text above the main title.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Award Badge Text <span class="badge bg-secondary">NEW</span></label>
                        <input type="text" name="theme_hero_award_text" class="form-control" value="{{ $theme['theme.hero_award_text'] ?? '' }}" placeholder="★ India's Most Promising Brand 2021">
                        <div class="form-text">Gold badge shown above title.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hero Subtitle</label>
                        <input type="text" name="theme_hero_subtitle" class="form-control" value="{{ $theme['theme.hero_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary CTA Text</label>
                        <input type="text" name="theme_hero_cta_text" class="form-control" value="{{ $theme['theme.hero_cta_text'] ?? '' }}" placeholder="Shop Now">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary CTA Link</label>
                        <input type="text" name="theme_hero_cta_link" class="form-control" value="{{ $theme['theme.hero_cta_link'] ?? '' }}" placeholder="/search">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary CTA Text <span class="badge bg-secondary">NEW</span></label>
                        <input type="text" name="theme_hero_cta2_text" class="form-control" value="{{ $theme['theme.hero_cta2_text'] ?? '' }}" placeholder="Explore Categories">
                        <div class="form-text">Leave blank to hide the outline button.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary CTA Link</label>
                        <input type="text" name="theme_hero_cta2_link" class="form-control" value="{{ $theme['theme.hero_cta2_link'] ?? '' }}" placeholder="/categories">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-shield-check me-2"></i> Trust Bar setup</div>
            <div class="card-body">
                <label class="form-label">Trust Bar Text</label>
                <input type="text" name="theme_trust_text" class="form-control" value="{{ $theme['theme.trust_text'] ?? '' }}">
                <div class="form-text">Example: COD Available | Free Shipping | Easy Returns</div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-megaphone me-2"></i> Announcement Bar</div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="theme_announcement_active" name="theme_announcement_active" value="1" {{ !empty($theme['theme.announcement_active']) ? 'checked' : '' }}>
                    <label class="form-check-label" for="theme_announcement_active">Enable Announcement Bar</label>
                </div>
                <label class="form-label">Announcement Text</label>
                <input type="text" name="theme_announcement_text" class="form-control" value="{{ $theme['theme.announcement_text'] ?? '' }}">
                <div class="form-text">Example: Use code SAVE10 for 10% off! | Free Shipping above ₹499</div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-tag me-2"></i> Offers Banner Layout</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Offers Banner Text</label>
                        <input type="text" name="theme_offers_banner_text" class="form-control" value="{{ $theme['theme.offers_banner_text'] ?? '' }}">
                        <div class="form-text">Used if no image is uploaded.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Offers Banner Link</label>
                        <input type="text" name="theme_offers_banner_link" class="form-control" value="{{ $theme['theme.offers_banner_link'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Brand Story / Award Section ─── --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-award me-2"></i> Award / Brand Story Section <span class="badge bg-success">NEW</span></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Controls the plum-coloured award section on the homepage. Images are pulled from <strong>Hero Slides</strong> (up to 4), or you can upload specific award images below.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Section Title</label>
                        <input type="text" name="theme_brand_story_title" class="form-control" value="{{ $theme['theme.brand_story_title'] ?? '' }}" placeholder="India's Most Promising Beauty Brand">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Section Description</label>
                        <textarea name="theme_brand_story_text" class="form-control" rows="3" placeholder="Recognized at the Asian Excellence Awards 2021...">{{ $theme['theme.brand_story_text'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Read Story Link</label>
                        <input type="text" name="theme_brand_story_link" class="form-control" value="{{ $theme['theme.brand_story_link'] ?? '' }}" placeholder="Leave blank to hide">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label fw-semibold">Stats (3 entries shown)</label>
                        <div class="row g-2">
                            @php
                                $awardStats = is_array($theme['theme.award_stats'] ?? null) ? $theme['theme.award_stats'] : [
                                    ['num' => '400+', 'label' => 'Products'],
                                    ['num' => '2021', 'label' => 'Award Year'],
                                    ['num' => 'PAN India', 'label' => 'Delivery'],
                                ];
                            @endphp
                            @foreach(array_slice($awardStats, 0, 3) as $si => $stat)
                            <div class="col-md-4">
                                <input type="text" name="award_stats[{{ $si }}][num]" class="form-control mb-1" value="{{ $stat['num'] ?? '' }}" placeholder="400+">
                                <input type="text" name="award_stats[{{ $si }}][label]" class="form-control" value="{{ $stat['label'] ?? '' }}" placeholder="Products">
                            </div>
                            @endforeach
                        </div>
                        <div class="form-text">Number on top, label below. Example: 400+ / Products</div>
                    </div>

                    {{-- ── Award Section Images (4 slots) ── --}}
                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold">Section Images <span class="badge bg-warning text-dark">4 Images</span></label>
                        <p class="text-muted small mb-2">Ye 4 images right side pe grid mein dikhti hain. <strong>Recommended: 800×800px square</strong>. Agar koi image nahi lagayi toh bestseller products dikhenge.</p>
                        @php
                            $awardImages = is_array($theme['theme.award_images'] ?? null) ? $theme['theme.award_images'] : [];
                        @endphp
                        <div class="row g-3">
                            @for($ai = 0; $ai < 4; $ai++)
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-light" style="min-height: 130px; position: relative;">
                                    <div class="fw-semibold small mb-2 text-muted">Image {{ $ai + 1 }}</div>
                                    @if(!empty($awardImages[$ai]))
                                        <img src="{{ asset('storage/' . $awardImages[$ai]) }}"
                                             alt="Award Image {{ $ai + 1 }}"
                                             class="img-fluid rounded mb-2"
                                             style="height: 80px; width: 100%; object-fit: cover;">
                                        <input type="hidden" name="award_images_existing[{{ $ai }}]" value="{{ $awardImages[$ai] }}">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" name="award_images_remove[{{ $ai }}]" value="1" id="rm_award_{{ $ai }}">
                                            <label class="form-check-label small text-danger" for="rm_award_{{ $ai }}">Remove</label>
                                        </div>
                                    @else
                                        <div style="height:80px; display:flex; align-items:center; justify-content:center; color:#bbb;">
                                            <i class="bi bi-image" style="font-size:2rem;"></i>
                                        </div>
                                    @endif
                                    <input type="file" name="award_images[{{ $ai }}]" class="form-control form-control-sm mt-1" accept="image/*">
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Trust Strip ─── --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-check2-all me-2"></i> Trust Strip (Dark Bar) <span class="badge bg-success">NEW</span></div>
            <div class="card-body">
                <p class="text-muted small mb-3">The 4-item dark bar shown just below the hero. Edit value and label for each item.</p>
                @php
                    $trustItems = is_array($theme['theme.trust_strip_items'] ?? null) ? $theme['theme.trust_strip_items'] : [
                        ['val' => '400+', 'label' => 'Premium Products'],
                        ['val' => 'No Paraben · No SLS', 'label' => 'Clean Formulations'],
                        ['val' => 'Cruelty Free', 'label' => 'Ethically Crafted'],
                        ['val' => '60-Day Returns', 'label' => 'Hassle-Free Policy'],
                    ];
                @endphp
                <div class="row g-3">
                    @foreach($trustItems as $ti => $tItem)
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <label class="form-label small fw-semibold">Item {{ $ti + 1 }}</label>
                            <input type="text" name="trust_strip[{{ $ti }}][val]" class="form-control form-control-sm mb-1" value="{{ $tItem['val'] ?? '' }}" placeholder="Value (e.g. 400+)">
                            <input type="text" name="trust_strip[{{ $ti }}][label]" class="form-control form-control-sm" value="{{ $tItem['label'] ?? '' }}" placeholder="Label (e.g. Premium Products)">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── USP Strip ─── --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-stars me-2"></i> USP Strip (4 Features) <span class="badge bg-success">NEW</span></div>
            <div class="card-body">
                <p class="text-muted small mb-3">The 4-column feature strip (No Paraben, Cruelty Free, etc). Use Bootstrap icon names.</p>
                @php
                    $uspItems = is_array($theme['theme.usp_strip_items'] ?? null) ? $theme['theme.usp_strip_items'] : [
                        ['icon' => 'bi-stars', 'title' => 'No Paraben, No SLS, No Silicones', 'desc' => 'Clean formulas you can trust daily'],
                        ['icon' => 'bi-heart', 'title' => 'Cruelty Free Always', 'desc' => 'Never tested on animals'],
                        ['icon' => 'bi-droplet', 'title' => 'Non-Alcoholic Fragrances', 'desc' => 'Attars and perfumes for everyone'],
                        ['icon' => 'bi-shop', 'title' => 'Trusted by Salons Across India', 'desc' => 'Professional-grade for home and salon'],
                    ];
                @endphp
                <div class="row g-3">
                    @foreach($uspItems as $ui => $uItem)
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <label class="form-label small fw-semibold">Feature {{ $ui + 1 }}</label>
                            <input type="text" name="usp_strip[{{ $ui }}][icon]" class="form-control form-control-sm mb-1" value="{{ $uItem['icon'] ?? 'bi-stars' }}" placeholder="Bootstrap icon class (e.g. bi-stars)">
                            <input type="text" name="usp_strip[{{ $ui }}][title]" class="form-control form-control-sm mb-1" value="{{ $uItem['title'] ?? '' }}" placeholder="Feature Title">
                            <input type="text" name="usp_strip[{{ $ui }}][desc]" class="form-control form-control-sm" value="{{ $uItem['desc'] ?? '' }}" placeholder="Short description">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="form-text mt-2">Bootstrap icons: bi-stars, bi-heart, bi-droplet, bi-shield-check, bi-truck, bi-shop, bi-patch-check</div>
            </div>
        </div>

        {{-- ── Benefits Strip ─── --}}
        <div class="card mb-4 border-dark">
            <div class="card-header bg-dark text-white"><i class="bi bi-diagram-3 me-2"></i> Problem → Solution Matrix <span class="badge bg-gold text-dark" style="background:#C9A84C;">ENGINE</span></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Build the conversion engine here. Map customer problems directly to products. (Enter exact Product IDs comma-separated).</p>
                @php
                    $matrixRaw = $theme['theme.problem_matrix'] ?? null;
                    if(is_string($matrixRaw) && !empty($matrixRaw)) {
                        $matrixItems = json_decode($matrixRaw, true) ?: [];
                    } elseif (is_array($matrixRaw)) {
                        $matrixItems = $matrixRaw;
                    } else {
                        $matrixItems = [
                            ['problem' => 'Hair Fall & Thinning?', 'products' => ''],
                            ['problem' => 'Dull, Lifeless Skin?', 'products' => ''],
                            ['problem' => 'Frizzy Hair & Split Ends?', 'products' => ''],
                        ];
                    }
                @endphp
                <div class="row g-3">
                    @foreach($matrixItems as $mi => $mItem)
                    <div class="col-12">
                        <div class="p-3 border rounded bg-light border-start border-4 border-dark">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Problem Headline {{ $mi + 1 }}</label>
                                    <input type="text" name="problem_matrix[{{ $mi }}][problem]" class="form-control" value="{{ $mItem['problem'] ?? '' }}" placeholder="e.g. Hair Fall? -> Try This">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold">Product IDs (Comma Separated)</label>
                                    <input type="text" name="problem_matrix[{{ $mi }}][products]" class="form-control" value="{{ $mItem['products'] ?? '' }}" placeholder="e.g. 12, 45, 89">
                                    <div class="form-text" style="font-size:11px;">Enter 3 Product IDs exactly. Find IDs in the Products tab.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-bullseye me-2"></i> Benefits Strip (Circular Icons) <span class="badge bg-success">NEW</span></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Circular icons with outcome-based labels. Scrolls horizontally on mobile. Use outcome messaging, not generic claims.</p>
                @php
                    $benefitsItems = is_array($theme['theme.benefits_items'] ?? null) ? $theme['theme.benefits_items'] : [
                        ['icon' => 'bi-droplet-half', 'label' => 'Hair Fall Control'],
                        ['icon' => 'bi-snow2', 'label' => 'Dandruff Reduction'],
                        ['icon' => 'bi-brilliance', 'label' => 'Salon Smooth Finish'],
                        ['icon' => 'bi-shield-check', 'label' => 'No Harsh Chemicals'],
                        ['icon' => 'bi-flower1', 'label' => 'Non-Alcoholic Fragrance'],
                    ];
                @endphp
                <div class="row g-3">
                    @foreach(array_slice($benefitsItems, 0, 6) as $bi => $bItem)
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <label class="form-label small fw-semibold">Benefit {{ $bi + 1 }}</label>
                            <input type="text" name="benefits_items[{{ $bi }}][icon]" class="form-control form-control-sm mb-1" value="{{ $bItem['icon'] ?? 'bi-check-circle' }}" placeholder="Bootstrap icon (e.g. bi-droplet-half)">
                            <input type="text" name="benefits_items[{{ $bi }}][label]" class="form-control form-control-sm" value="{{ $bItem['label'] ?? '' }}" placeholder="Label (e.g. Hair Fall Control)">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="form-text mt-2">Use outcome labels: Hair Fall Control, Dandruff Reduction, Salon Smooth Finish, etc. Max 6 items.</div>
            </div>
        </div>

        {{-- ── Instagram Follow ─── --}}
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-instagram me-2"></i> Instagram Follow Section</div>
            <div class="card-body">
                <label class="form-label">Instagram Handle</label>
                <div class="input-group">
                    <span class="input-group-text">@</span>
                    <input type="text" name="theme_instagram_handle" class="form-control" value="{{ $theme['theme.instagram_handle'] ?? 'dream_attitude_international' }}" placeholder="dream_attitude_international">
                </div>
                <div class="form-text">Your Instagram username without @. Links to instagram.com/{handle}/</div>
            </div>
        </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4 border-info">
            <div class="card-body">
                <h6 class="card-title text-info"><i class="bi bi-info-circle me-1"></i> Hero Slides</h6>
                <p class="small text-muted mb-0">Manage hero slides in the left panel. Upload banner images (1920×700px) with text baked in. Each slide can link to a different page.</p>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header bg-white">Offers Banner Image</div>
            <div class="card-body">
                @if(!empty($theme['theme.offers_banner_image']))
                    <div class="mb-3 text-center">
                        <img src="{{ asset('storage/' . $theme['theme.offers_banner_image']) }}" alt="Offers Banner" class="img-fluid rounded border">
                    </div>
                @endif
                <input type="file" name="theme_offers_banner_image" class="form-control" accept="image/*">
            </div>
        </div>
        
        <div class="card border-primary mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-grid">
                    <button class="btn btn-primary btn-lg"><i class="bi bi-check2-circle me-1"></i> Save Homepage</button>
                </div>
            </div>
        </div>
    </div>
@endif

</div>
</form>
@if ($tab === 'homepage')
<script>
var slideIndex = {{ count($heroSlides ?? []) }};
document.getElementById('addSlideBtn').addEventListener('click', function() {
    if (slideIndex >= 10) { alert('Maximum 10 slides allowed'); return; }
    var c = document.getElementById('slidesContainer');
    var html = '<div class="slide-row border rounded p-3 mb-3 position-relative bg-light">' +
        '<button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top:8px;right:8px;z-index:2;" onclick="this.closest(\'.slide-row\').remove()"><i class="bi bi-trash"></i></button>' +
        '<div class="row g-2 align-items-center">' +
        '<div class="col-md-3 text-center">' +
        '<label class="form-label small mb-1 fw-bold">Desktop <small class="text-muted">(1920x800)</small> <span class="text-danger">*</span></label>' +
        '<input type="file" name="slide_images[' + slideIndex + ']" class="form-control form-control-sm" accept="image/*" required>' +
        '</div>' +
        '<div class="col-md-3 text-center">' +
        '<label class="form-label small mb-1 fw-bold">Mobile <small class="text-muted">(1080x1350)</small> <span class="text-danger">*</span></label>' +
        '<input type="file" name="slide_images_mobile[' + slideIndex + ']" class="form-control form-control-sm" accept="image/*" required>' +
        '</div>' +
        '<div class="col-md-6">' +
        '<div class="row g-2">' +
        '<div class="col-12">' +
        '<label class="form-label small mb-1">Link URL</label>' +
        '<input type="text" name="slide_links[' + slideIndex + ']" class="form-control form-control-sm" placeholder="/category/hair-care">' +
        '</div>' +
        '<div class="col-12">' +
        '<label class="form-label small mb-1">Alt Text</label>' +
        '<input type="text" name="slide_alts[' + slideIndex + ']" class="form-control form-control-sm" placeholder="Product name + benefit">' +
        '</div>' +
        '</div></div>' +
        '</div></div>';
    c.insertAdjacentHTML('beforeend', html);
    slideIndex++;
});
</script>
@endif
@endsection
