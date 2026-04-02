@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<h1 class="h4 mb-3">Store Settings</h1>
<form action="{{ route('admin.settings.update') }}" method="post">
@csrf @method('PUT')
<div class="row g-3">

{{-- Store --}}
<div class="col-12"><h5 class="border-bottom pb-2">🏪 Store</h5></div>
<div class="col-md-6">
    <label class="form-label">Store Name</label>
    <input type="text" name="store__name" class="form-control" value="{{ $groups['store']['name'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">Store Tagline</label>
    <input type="text" name="store__tagline" class="form-control" value="{{ $groups['store']['tagline'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">GSTIN (optional)</label>
    <input type="text" name="store__gstin" class="form-control" value="{{ $groups['store']['gstin'] ?? '' }}" placeholder="22AAAAA0000A1Z5">
</div>
<div class="col-md-6">
    <label class="form-label">Phone (for display)</label>
    <input type="text" name="store__phone" class="form-control" value="{{ $groups['store']['phone'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">Email (for display)</label>
    <input type="text" name="store__email" class="form-control" value="{{ $groups['store']['email'] ?? '' }}">
</div>

{{-- SEO --}}
<div class="col-12"><h5 class="border-bottom pb-2 mt-2">🔍 SEO</h5></div>
<div class="col-md-6">
    <label class="form-label">Site Meta Title</label>
    <input type="text" name="seo__title" class="form-control" value="{{ $groups['seo']['title'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">Meta Description</label>
    <input type="text" name="seo__description" class="form-control" value="{{ $groups['seo']['description'] ?? '' }}">
</div>
<div class="col-12">
    <label class="form-label">Robots.txt Body (leave blank for default)</label>
    <textarea name="seo__robots_body" class="form-control" rows="3">{{ $groups['seo']['robots_body'] ?? '' }}</textarea>
</div>

{{-- Tracking --}}
<div class="col-12"><h5 class="border-bottom pb-2 mt-2">📊 Tracking & Ads</h5></div>
<div class="col-md-4">
    <label class="form-label">GTM Container ID</label>
    <input type="text" name="tracking__gtm_id" class="form-control" value="{{ $groups['tracking']['gtm_id'] ?? '' }}" placeholder="GTM-XXXXXX">
</div>
<div class="col-md-4">
    <label class="form-label">GA4 Measurement ID</label>
    <input type="text" name="tracking__ga4_id" class="form-control" value="{{ $groups['tracking']['ga4_id'] ?? '' }}" placeholder="G-XXXXXXXXXX">
</div>
<div class="col-md-4">
    <label class="form-label">Meta Pixel ID</label>
    <input type="text" name="tracking__pixel_id" class="form-control" value="{{ $groups['tracking']['pixel_id'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">Meta CAPI Access Token</label>
    <input type="text" name="tracking__capi_token" class="form-control" value="{{ $groups['tracking']['capi_token'] ?? '' }}" placeholder="Leave blank to disable CAPI">
</div>

{{-- Payments --}}
<div class="col-12"><h5 class="border-bottom pb-2 mt-2">💳 Payments</h5></div>
<div class="col-md-6">
    <label class="form-label">Razorpay Key ID</label>
    <input type="text" name="payment__razorpay_key" class="form-control" value="{{ $groups['payment']['razorpay_key'] ?? '' }}">
</div>
<div class="col-md-6">
    <label class="form-label">Razorpay Key Secret</label>
    <input type="password" name="payment__razorpay_secret" class="form-control" value="{{ $groups['payment']['razorpay_secret'] ?? '' }}" autocomplete="new-password">
</div>

{{-- Notifications --}}
<div class="col-12"><h5 class="border-bottom pb-2 mt-2">🔔 Notifications</h5></div>
<div class="col-md-4">
    <label class="form-label">WhatsApp Provider</label>
    <select name="notify__whatsapp_provider" class="form-select">
        <option value="">— Disabled —</option>
        <option value="wati" {{ ($groups['notify']['whatsapp_provider'] ?? '') === 'wati' ? 'selected' : '' }}>Wati</option>
        <option value="2factor" {{ ($groups['notify']['whatsapp_provider'] ?? '') === '2factor' ? 'selected' : '' }}>2Factor</option>
    </select>
</div>
<div class="col-md-4">
    <label class="form-label">WhatsApp API Token</label>
    <input type="password" name="notify__whatsapp_token" class="form-control" value="{{ $groups['notify']['whatsapp_token'] ?? '' }}" autocomplete="new-password">
</div>
<div class="col-md-4">
    <label class="form-label">Wati Base URL</label>
    <input type="text" name="notify__wati_url" class="form-control" value="{{ $groups['notify']['wati_url'] ?? '' }}" placeholder="https://live-xxx.wati.io">
</div>

<div class="col-12 mt-2">
    <button type="submit" class="btn btn-primary">Save Settings</button>
</div>
</div>
</form>
@endsection
