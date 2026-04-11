@extends('layouts.storefront')

@section('title', $category->seo_title ?: $category->name)

@section('content')
<section class="sf-section">
    <div class="sf-container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="sf-category-banner">
            @if($category->image_path)
                <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}" loading="lazy">
            @endif
            <div class="cat-banner-overlay">
                <h1>{{ $category->name }}</h1>
                @if($category->description)
                    <p>{{ $category->description }}</p>
                @endif
            </div>
        </div>

        @if($products->isEmpty())
            <div class="text-center py-5">
                <h4 class="text-muted">No products found in this category.</h4>
            </div>
        @else
            <div class="sf-product-grid mt-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
