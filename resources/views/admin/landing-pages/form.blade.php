@extends('layouts.admin')
@section('title', $page ? 'Edit Landing Page' : 'New Landing Page')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.landing-pages.index') }}">Landing Pages</a></li>
            <li class="breadcrumb-item active">{{ $page ? 'Edit' : 'Create' }}</li>
        </ol>
    </nav>
    <h1 class="h3 mb-0" style="font-weight: 700;">{{ $page ? 'Edit: ' . $page->title : 'New Landing Page' }}</h1>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $page ? route('admin.landing-pages.update', $page) : route('admin.landing-pages.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($page) @method('PUT') @endif

    <div class="row g-4">
        {{-- ── LEFT COLUMN ──────────────────────────── --}}
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Basic Info</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $page?->title) }}" required>
                            <small class="text-muted">Admin reference name (also shown on offer box)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">URL Slug <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">/offer/</span>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $page?->slug) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SEO Title</label>
                            <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $page?->seo_title) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SEO Description</label>
                            <input type="text" name="seo_description" class="form-control" value="{{ old('seo_description', $page?->seo_description) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hero Section --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Hero Section</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Headline <span class="text-danger">*</span></label>
                            <input type="text" name="hero_headline" class="form-control" value="{{ old('hero_headline', $page?->hero_headline) }}" placeholder="Hair fall ruk nahi raha?" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sub-headline</label>
                            <input type="text" name="hero_subheadline" class="form-control" value="{{ old('hero_subheadline', $page?->hero_subheadline) }}" placeholder="Root se strengthen karo — 2-step Onion routine">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CTA Button Text</label>
                            <input type="text" name="hero_cta_text" class="form-control" value="{{ old('hero_cta_text', $page?->hero_cta_text ?? 'Abhi Order Karo') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hero Image</label>
                            <input type="file" name="hero_image_file" class="form-control" accept="image/*">
                            @if($page?->hero_image)
                                <small class="text-muted">Current: {{ $page->hero_image }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pain Points --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Pain Points <small class="text-muted">(one per line)</small></div>
                <div class="card-body">
                    <textarea name="problem_points_raw" class="form-control" rows="4" placeholder="Comb mein baal gir rahe?&#10;Wash ke baad aur zyada fall?&#10;Shampoo badle, result nahi mila?">{{ old('problem_points_raw', $page?->problem_points ? implode("\n", $page->problem_points) : '') }}</textarea>
                </div>
            </div>

            {{-- Product Steps --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Routine Steps <small class="text-muted">(JSON)</small></div>
                <div class="card-body">
                    <textarea name="steps_json" class="form-control font-monospace" rows="6" placeholder='[{"title":"Step 1: Root Strengthening Oil","desc":"Fall ko root se roko"},{"title":"Step 2: Scalp Clean Shampoo","desc":"Bina dryness ke clean karo"}]'>{{ old('steps_json', $page?->steps ? json_encode($page->steps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    <small class="text-muted">Format: [{"title":"...","desc":"...","image":"optional-path"}, ...]</small>
                </div>
            </div>

            {{-- Trust Points --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Trust Points <small class="text-muted">(one per line — use | for number|label)</small></div>
                <div class="card-body">
                    <textarea name="trust_points_raw" class="form-control" rows="4" placeholder="4.9★|Google Rating&#10;17,000+|Reviews&#10;45+|Years Legacy&#10;🏆|Award Winner">{{ old('trust_points_raw', $page?->trust_points ? implode("\n", $page->trust_points) : '') }}</textarea>
                </div>
            </div>

            {{-- Products in Kit --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Products in Kit <small class="text-muted">(JSON — product_id, variant_id, qty)</small></div>
                <div class="card-body">
                    <textarea name="products_json" class="form-control font-monospace" rows="5" placeholder='[{"product_id":12,"variant_id":45,"qty":1},{"product_id":8,"variant_id":22,"qty":1}]'>{{ old('products_json', $page?->products ? json_encode($page->products, JSON_PRETTY_PRINT) : '') }}</textarea>

                    <div class="mt-3">
                        <small class="fw-bold">Product & Variant Reference:</small>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mt-1 mb-0" style="font-size: 12px;">
                                <thead class="table-light"><tr><th>Product</th><th>Product ID</th><th>Variant</th><th>Variant ID</th><th>Price</th></tr></thead>
                                <tbody>
                                    @foreach($products as $prod)
                                        @foreach($prod->variants as $v)
                                        <tr>
                                            <td>{{ $prod->name }}</td>
                                            <td><code>{{ $prod->id }}</code></td>
                                            <td>{{ $v->name ?: 'Default' }}</td>
                                            <td><code>{{ $v->id }}</code></td>
                                            <td>₹{{ number_format($v->price, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reviews --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Customer Reviews <small class="text-muted">(JSON — max 3)</small></div>
                <div class="card-body">
                    <textarea name="reviews_json" class="form-control font-monospace" rows="6" placeholder='[{"name":"Priya S.","rating":5,"text":"Hair fall bahut kam ho gaya 2 weeks me!","photo":""},{"name":"Rohit M.","rating":5,"text":"Baal soft aur strong feel ho rahe hai","photo":""}]'>{{ old('reviews_json', $page?->reviews ? json_encode($page->reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    <small class="text-muted">Format: [{"name":"...","rating":5,"text":"...","photo":"optional-storage-path"}, ...] — Keep max 3.</small>
                </div>
            </div>

            {{-- FAQ --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">FAQ <small class="text-muted">(JSON — max 2)</small></div>
                <div class="card-body">
                    <textarea name="faq_json" class="form-control font-monospace" rows="4" placeholder='[{"q":"COD available hai?","a":"Haan, pan India COD aur free delivery available hai."},{"q":"Kitne din me result dikhega?","a":"First 2-3 washes me fall reduce feel hoga."}]'>{{ old('faq_json', $page?->faq ? json_encode($page->faq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN ─────────────────────────── --}}
        <div class="col-lg-4">
            {{-- Publish --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Publish</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" {{ old('is_active', $page?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActiveCheck">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i> {{ $page ? 'Update' : 'Create' }} Landing Page
                    </button>
                    @if($page)
                        <a href="{{ route('landing.show', $page->slug) }}" target="_blank" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-eye me-1"></i> Preview
                        </a>
                    @endif
                </div>
            </div>

            {{-- Pricing --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Pricing</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Offer Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="offer_price" class="form-control" value="{{ old('offer_price', $page?->offer_price ?? 799) }}" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Original Price (₹) <small class="text-muted">strikethrough</small></label>
                        <input type="number" name="original_price" class="form-control" value="{{ old('original_price', $page?->original_price ?? 1299) }}" step="0.01">
                    </div>
                    <div>
                        <label class="form-label">Offer Badge</label>
                        <input type="text" name="offer_badge" class="form-control" value="{{ old('offer_badge', $page?->offer_badge) }}" placeholder="Summer Sale">
                    </div>
                </div>
            </div>

            {{-- Badges --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Badges</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="show_cod_badge" value="1" id="codCheck" {{ old('show_cod_badge', $page?->show_cod_badge ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="codCheck">Show COD Available</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_free_ship" value="1" id="shipCheck" {{ old('show_free_ship', $page?->show_free_ship ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="shipCheck">Show Free Shipping</label>
                    </div>
                </div>
            </div>

            {{-- WhatsApp --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">WhatsApp</div>
                <div class="card-body">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $page?->whatsapp_number ?? '8141939616') }}" placeholder="8141939616">
                </div>
            </div>

            {{-- Trust Description --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Trust Description</div>
                <div class="card-body">
                    <textarea name="trust_description" class="form-control" rows="4" placeholder="Dream Attitude is the online brand of NR Beauty World...">{{ old('trust_description', $page?->trust_description) }}</textarea>
                    <small class="text-muted">Optional paragraph shown in trust section</small>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
