@extends('layouts.storefront')

@section('title', $category->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>
    <h1 class="h3 mb-4">{{ $category->name }}</h1>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach ($products as $product)
            <div class="col">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
