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
<section class="sf-hero sf-cat-hero">
    <div class="sf-hero-img-wrap sf-cat-hero-img-wrap">
        @if($category->image_path)
            <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" class="sf-hero-img" loading="eager">
        @else
            <div class="sf-cat-hero-fallback"></div>
        @endif
    </div>
    <div class="sf-hero-overlay sf-cat-hero-overlay"></div>
    <div class="sf-hero-content sf-cat-hero-content">
        <h1 class="sf-hero-title">{{ $category->name }}</h1>
        @if($category->description)
            <p class="sf-hero-sub sf-cat-hero-sub">{{ $category->description }}</p>
        @endif
    </div>
</section>

<section class="sf-section">
    <div class="sf-container">
        <nav class="sf-breadcrumb">
            <a href="{{ route('home') }}" class="sf-breadcrumb-link">Home</a> 
            <span class="sf-breadcrumb-sep">/</span> 
            <span class="sf-breadcrumb-active">{{ $category->name }}</span>
        </nav>

        @if($products->isEmpty())
            <div class="sf-cat-empty">
                <h4 class="sf-cat-empty-msg">No products found in this category.</h4>
            </div>
        @else
            <div class="sf-cat-toolbar">
                <span class="sf-cat-count">{{ $products->total() }} Products</span>
                <select onchange="window.location=this.value" class="sf-input sf-cat-sort">
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
