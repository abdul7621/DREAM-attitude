@extends('layouts.storefront')

@section('title', $page->seo_title ?: $page->title)

@push('meta')
    @if ($page->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
@endpush

@push('styles')
@endpush

@section('content')
<div class="sf-container">
    <div class="sf-cms-container">
        <h1 class="sf-section-title sf-cms-title">{{ $page->title }}</h1>
        <div class="sf-cms-content">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
