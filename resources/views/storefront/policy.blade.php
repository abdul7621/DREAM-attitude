@extends('layouts.storefront')

@section('title', $title)

@section('content')
<div class="sf-container">
    <div style="max-width: 800px; margin: 40px auto; min-height: 50vh; background: var(--color-bg-primary); padding: 40px; border-radius: var(--radius-md); box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--color-border);">
        <h1 class="sf-section-title" style="margin-bottom: 24px;">{{ $title }}</h1>
        <div class="policy-content" style="color: var(--color-text-secondary); line-height: 1.8; font-size: 14px;">
            {!! preg_replace("/\n\n+/", "<br><br>", trim($content)) !!}
        </div>
    </div>
</div>
@endsection
