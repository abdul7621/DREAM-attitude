@extends('layouts.storefront')

@section('title', $category->seo_title ?: $category->name)
@if ($category->seo_description)
    @section('meta_description', $category->seo_description)
@endif

@push('meta')
    <link rel="canonical" href="{{ route('category.show', $category, true) }}">
    @if(request()->has('sort'))
        <meta name="robots" content="noindex, follow">
    @endif
    @if($products->previousPageUrl())
        <link rel="prev" href="{{ $products->previousPageUrl() }}">
    @endif
    @if($products->nextPageUrl())
        <link rel="next" href="{{ $products->nextPageUrl() }}">
    @endif
    @php
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home', [], true)
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $category->name,
                    'item' => route('category.show', $category, true)
                ]
            ]
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@section('content')
<section class="sf-hero" style="height: 300px;">
    <div class="sf-hero-img-wrap" style="height: 100%;">
        @if($category->image_path)
            <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="sf-hero-img" loading="eager">
        @else
            <div style="background:var(--color-bg-elevated);width:100%;height:100%;"></div>
        @endif
    </div>
    <div class="sf-hero-overlay" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 100%);"></div>
    <div class="sf-hero-content" style="width: 100%; text-align: center; left: 0;">
        <h1 class="sf-hero-title">{{ $category->name }}</h1>
        @if($category->description)
            <p class="sf-hero-sub" style="max-width: 600px; margin: 0 auto;">{{ $category->description }}</p>
        @endif
    </div>
</section>

<section class="sf-section">
    <div class="sf-container">
        <nav style="margin-bottom: 32px; font-size: 13px; color: var(--color-text-muted);">
            <a href="{{ route('home') }}" style="color: var(--color-text-secondary);">Home</a> 
            <span style="margin: 0 8px;">/</span> 
            <span style="color: var(--color-gold);">{{ $category->name }}</span>
        </nav>

        @if($products->isEmpty())
            <div style="text-align: center; padding: 60px 0;">
                <h4 style="color: var(--color-text-muted);">No products found in this category.</h4>
            </div>
        @else
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <span style="font-size:13px;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:1px;">{{ $products->total() }} Products</span>
                <select onchange="window.location=this.value" class="sf-input" style="width:auto;min-width:180px;padding:8px 12px;">
                    <option value="?sort=newest" {{ request('sort','newest')=='newest'?'selected':'' }}>Newest First</option>
                    <option value="?sort=price_asc" {{ request('sort')=='price_asc'?'selected':'' }}>Price: Low → High</option>
                    <option value="?sort=price_desc" {{ request('sort')=='price_desc'?'selected':'' }}>Price: High → Low</option>
                    <option value="?sort=bestseller" {{ request('sort')=='bestseller'?'selected':'' }}>Bestsellers</option>
                </select>
            </div>
            <div class="sf-product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif

        <div style="margin-top: 40px;">
            {{ $products->links('vendor.pagination.storefront') }}
        </div>
    </div>
</section>
@endsection
