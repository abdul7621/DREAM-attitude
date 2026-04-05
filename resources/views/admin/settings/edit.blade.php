@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Store Settings</h1>
</div>

{{-- ── Tab Navigation ──────────────────────────────────── --}}
@php
    $tabs = [
        'store'       => ['icon' => 'bi-shop',              'label' => 'General'],
        'seo'         => ['icon' => 'bi-search',            'label' => 'SEO'],
        'tracking'    => ['icon' => 'bi-graph-up-arrow',    'label' => 'Tracking & Ads'],
        'payment'     => ['icon' => 'bi-credit-card',       'label' => 'Payments'],
        'shipping'    => ['icon' => 'bi-truck',             'label' => 'Shipping'],
        'checkout'    => ['icon' => 'bi-cart-check',        'label' => 'Checkout'],
        'notify'      => ['icon' => 'bi-bell',              'label' => 'Notifications'],
        'policies'    => ['icon' => 'bi-file-earmark-text', 'label' => 'Policies'],
    ];
    $activeTab = request('tab', 'store');
@endphp

<ul class="nav nav-tabs mb-3" role="tablist">
    @foreach ($tabs as $key => $tab)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}" href="?tab={{ $key }}">
                <i class="bi {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>

<form action="{{ route('admin.settings.update') }}" method="post">
@csrf @method('PUT')
<input type="hidden" name="_tab" value="{{ $activeTab }}">
<div class="card">
<div class="card-body">

{{-- ═══ GENERAL / STORE ════════════════════════════════════ --}}
@if ($activeTab === 'store')
    <h5 class="mb-3">🏪 Store Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Store Mode</label>
            <select name="store__mode" class="form-select border-primary fw-bold text-primary">
                <option value="live" {{ ($groups['store']['mode'] ?? 'live') === 'live' ? 'selected' : '' }}>🟢 Live Storefront</option>
                <option value="maintenance" {{ ($groups['store']['mode'] ?? '') === 'maintenance' ? 'selected' : '' }}>🟡 Maintenance Mode</option>
                <option value="coming_soon" {{ ($groups['store']['mode'] ?? '') === 'coming_soon' ? 'selected' : '' }}>🟣 Coming Soon</option>
            </select>
            <div class="form-text">Admins can always bypass Maintenance/Coming Soon.</div>
        </div>
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
        <div class="col-md-6">
            <label class="form-label">Address</label>
            <input type="text" name="store__address" class="form-control" value="{{ $groups['store']['address'] ?? '' }}">
        </div>
        <div class="col-12">
            <label class="form-label">Currency</label>
            <input type="text" name="store__currency" class="form-control" value="{{ $groups['store']['currency'] ?? 'INR' }}" style="max-width:120px;">
        </div>
    </div>
@endif

{{-- ═══ SEO ════════════════════════════════════════════════ --}}
@if ($activeTab === 'seo')
    <h5 class="mb-3">🔍 SEO Settings</h5>
    <div class="row g-3">
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
    </div>
@endif

{{-- ═══ TRACKING & ADS ═════════════════════════════════════ --}}
@if ($activeTab === 'tracking')
    <h5 class="mb-3">📊 Tracking & Ads</h5>
    <div class="row g-3">
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
        <div class="col-md-6">
            <label class="form-label">CAPI Test Event Code</label>
            <input type="text" name="tracking__capi_test_code" class="form-control" value="{{ $groups['tracking']['capi_test_code'] ?? '' }}" placeholder="TEST12345">
        </div>
    </div>
@endif

{{-- ═══ PAYMENTS ═══════════════════════════════════════════ --}}
@if ($activeTab === 'payment')
    <h5 class="mb-3">💳 Payment Settings</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Razorpay Key ID</label>
            <input type="text" name="payment__razorpay_key" class="form-control" value="{{ $groups['payment']['razorpay_key'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Razorpay Key Secret</label>
            <input type="password" name="payment__razorpay_secret" class="form-control" value="{{ $groups['payment']['razorpay_secret'] ?? '' }}" autocomplete="new-password">
        </div>
        <div class="col-md-6">
            <label class="form-label">COD Enabled</label>
            <select name="payment__cod_enabled" class="form-select">
                <option value="1" {{ ($groups['payment']['cod_enabled'] ?? '1') === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ ($groups['payment']['cod_enabled'] ?? '1') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">COD Extra Charge (₹)</label>
            <input type="number" step="0.01" name="payment__cod_charge" class="form-control" value="{{ $groups['payment']['cod_charge'] ?? '0' }}">
        </div>
    </div>
@endif

{{-- ═══ SHIPPING ═══════════════════════════════════════════ --}}
@if ($activeTab === 'shipping')
    <h5 class="mb-3">🚚 Shipping Settings</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Free Shipping Threshold (₹)</label>
            <input type="number" step="0.01" name="shipping__free_threshold" class="form-control" value="{{ $groups['shipping']['free_threshold'] ?? '' }}" placeholder="e.g. 499">
        </div>
        <div class="col-md-6">
            <label class="form-label">Default Shipping Fee (₹)</label>
            <input type="number" step="0.01" name="shipping__default_fee" class="form-control" value="{{ $groups['shipping']['default_fee'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ship From — City</label>
            <input type="text" name="shipping__origin_city" class="form-control" value="{{ $groups['shipping']['origin_city'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ship From — Pincode</label>
            <input type="text" name="shipping__origin_pincode" class="form-control" value="{{ $groups['shipping']['origin_pincode'] ?? '' }}">
        </div>
    </div>
@endif

{{-- ═══ CHECKOUT ═══════════════════════════════════════════ --}}
@if ($activeTab === 'checkout')
    <h5 class="mb-3">🛒 Checkout Settings</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Guest Checkout</label>
            <select name="checkout__guest_enabled" class="form-select">
                <option value="1" {{ ($groups['checkout']['guest_enabled'] ?? '1') === '1' ? 'selected' : '' }}>Enabled</option>
                <option value="0" {{ ($groups['checkout']['guest_enabled'] ?? '1') === '0' ? 'selected' : '' }}>Disabled (login required)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone Number Required</label>
            <select name="checkout__phone_required" class="form-select">
                <option value="1" {{ ($groups['checkout']['phone_required'] ?? '1') === '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ ($groups['checkout']['phone_required'] ?? '1') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Min Order Amount (₹)</label>
            <input type="number" step="0.01" name="checkout__min_amount" class="form-control" value="{{ $groups['checkout']['min_amount'] ?? '' }}" placeholder="0 = no minimum">
        </div>
    </div>
@endif

{{-- ═══ NOTIFICATIONS ══════════════════════════════════════ --}}
@if ($activeTab === 'notify')
    <h5 class="mb-3">🔔 Notification Settings</h5>
    <div class="row g-3">
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
        <div class="col-md-6">
            <label class="form-label">Order Placed Email</label>
            <select name="notify__email_order_placed" class="form-select">
                <option value="1" {{ ($groups['notify']['email_order_placed'] ?? '1') === '1' ? 'selected' : '' }}>Enabled</option>
                <option value="0" {{ ($groups['notify']['email_order_placed'] ?? '1') === '0' ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Order Shipped Email</label>
            <select name="notify__email_order_shipped" class="form-select">
                <option value="1" {{ ($groups['notify']['email_order_shipped'] ?? '1') === '1' ? 'selected' : '' }}>Enabled</option>
                <option value="0" {{ ($groups['notify']['email_order_shipped'] ?? '1') === '0' ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>
    </div>
@endif

{{-- ═══ POLICIES ═══════════════════════════════════════════ --}}
@if ($activeTab === 'policies')
    <h5 class="mb-3">📝 Store Policies</h5>
    <p class="small text-muted">These are shown in the storefront footer and checkout. Use HTML for formatting.</p>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Privacy Policy</label>
            <textarea name="policies__privacy" class="form-control" rows="5">{{ $groups['policies']['privacy'] ?? '' }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Return Policy</label>
            <textarea name="policies__returns" class="form-control" rows="5">{{ $groups['policies']['returns'] ?? '' }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Shipping Policy</label>
            <textarea name="policies__shipping" class="form-control" rows="5">{{ $groups['policies']['shipping'] ?? '' }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Terms & Conditions</label>
            <textarea name="policies__terms" class="form-control" rows="5">{{ $groups['policies']['terms'] ?? '' }}</textarea>
        </div>
    </div>
@endif

</div>
<div class="card-footer">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save Settings</button>
</div>
</div>
</form>
@endsection
