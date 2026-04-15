@extends('layouts.storefront')

@section('title', $page->seo_title ?: $page->title)

@push('meta')
    @if ($page->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
@endpush

@push('styles')
<style>
    .cms-content { color: var(--color-text-secondary); line-height: 1.8; font-size: 15px; }
    .cms-content h1, .cms-content h2, .cms-content h3 { color: var(--color-text-primary); margin-top: 1.5rem; margin-bottom: 0.75rem; line-height: 1.3; }
    .cms-content p { margin-bottom: 1rem; }
    .cms-content ul, .cms-content ol { margin-bottom: 1rem; padding-left: 1.5rem; }
    .cms-content img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 1rem 0; }
    .cms-content a { color: var(--color-gold); text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="sf-container">
    <div style="max-width: 800px; margin: 40px auto; min-height: 50vh; background: var(--color-bg-primary); padding: 40px; border-radius: var(--radius-md); box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--color-border);">
        <h1 class="sf-section-title" style="margin-bottom: 24px;">{{ $page->title }}</h1>
        <div class="cms-content">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
