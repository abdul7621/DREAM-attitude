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
        <div class="card mb-4">
            <div class="card-header bg-white"><i class="bi bi-layout-wtf me-2"></i> Section Ordering</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Check the sections you want to display and drag them or input order to arrange. Currently implemented as a simple Ordered List.</p>
                <div class="list-group mb-3">
                    @php
                        $available = ['hero' => 'Hero Banner', 'categories' => 'Category Grid', 'featured' => 'Featured Products', 'bestsellers' => 'Bestsellers', 'latest' => 'New Arrivals', 'offers_banner' => 'Offers Banner', 'trust' => 'Trust Bar', 'reviews' => 'Testimonials'];
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
                                @if(!empty($slide['image']))
                                <img src="{{ asset('storage/' . $slide['image']) }}" class="img-fluid rounded border mb-1" style="max-height:70px;">
                                <input type="hidden" name="slide_existing[{{ $i }}]" value="{{ $slide['image'] }}">
                                @endif
                                <input type="file" name="slide_images[{{ $i }}]" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Link URL</label>
                                <input type="text" name="slide_links[{{ $i }}]" class="form-control form-control-sm" value="{{ $slide['link'] ?? '' }}" placeholder="/category/hair-care">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Alt Text</label>
                                <input type="text" name="slide_alts[{{ $i }}]" class="form-control form-control-sm" value="{{ $slide['alt'] ?? '' }}" placeholder="Banner description">
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
                <p class="text-muted small mb-3">Shows on top of slides. Leave empty if your banner images already have text.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="theme_hero_title" class="form-control" value="{{ $theme['theme.hero_title'] ?? '' }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hero Subtitle</label>
                        <input type="text" name="theme_hero_subtitle" class="form-control" value="{{ $theme['theme.hero_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CTA Button Text</label>
                        <input type="text" name="theme_hero_cta_text" class="form-control" value="{{ $theme['theme.hero_cta_text'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CTA Button Link</label>
                        <input type="text" name="theme_hero_cta_link" class="form-control" value="{{ $theme['theme.hero_cta_link'] ?? '' }}">
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
        '<input type="file" name="slide_images[' + slideIndex + ']" class="form-control form-control-sm" accept="image/*" required>' +
        '</div>' +
        '<div class="col-md-5">' +
        '<label class="form-label small mb-1">Link URL</label>' +
        '<input type="text" name="slide_links[' + slideIndex + ']" class="form-control form-control-sm" placeholder="/category/hair-care">' +
        '</div>' +
        '<div class="col-md-4">' +
        '<label class="form-label small mb-1">Alt Text</label>' +
        '<input type="text" name="slide_alts[' + slideIndex + ']" class="form-control form-control-sm" placeholder="Banner description">' +
        '</div>' +
        '</div></div>';
    c.insertAdjacentHTML('beforeend', html);
    slideIndex++;
});
</script>
@endif
@endsection
