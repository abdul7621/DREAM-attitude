@extends('layouts.storefront')
@section('title', '404 — Page Not Found')
@section('content')
<section class="sf-section" style="min-height:60vh;
  display:flex;align-items:center;">
<div class="sf-container" style="text-align:center;">
  <div style="font-size:96px;font-weight:500;
    color:var(--color-gold);line-height:1;">404</div>
  <h1 style="color:var(--color-text-primary);font-size:22px;
    margin-top:16px;font-weight:500;">
    Page not found</h1>
  <p style="color:var(--color-text-muted);
    font-size:14px;margin-top:8px;">
    The page you are looking for does not exist.</p>
  <a href="{{ route('home') }}"
    class="sf-hero-cta"
    style="margin-top:32px;display:inline-block;">
    Back to Home</a>
</div>
</section>
@endsection
