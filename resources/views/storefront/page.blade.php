@extends('layouts.storefront')

@section('title', $page->seo_title ?: $page->title)

@push('meta')
    @if ($page->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
@endpush

@section('content')
    <article class="bg-white rounded shadow-sm p-4">
        <h1 class="h3 mb-4">{{ $page->title }}</h1>
        <div class="commerce-page-content">
            {!! $page->content !!}
        </div>
    </article>
@endsection
