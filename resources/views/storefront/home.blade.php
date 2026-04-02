@extends('layouts.storefront')

@section('title', config('app.name'))

@section('content')
    <h1 class="h3 mb-4">{{ config('app.name') }}</h1>
    @if ($featured->isNotEmpty())
        <h2 class="h5 mb-3">Featured</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-5">
            @foreach ($featured as $product)
                <div class="col">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    @endif
    <h2 class="h5 mb-3">Latest</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach ($latest as $product)
            <div class="col">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@endsection
