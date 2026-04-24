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
        'conversion'  => ['icon' => 'bi-funnel-fill',       'label' => 'Conversion Control'],
        'notify'      => ['icon' => 'bi-bell',              'label' => 'Notifications'],
        'email'       => ['icon' => 'bi-envelope',          'label' => 'Email/SMTP'],
        'policies'    => ['icon' => 'bi-file-earmark-text', 'label' => 'Policies'],
        'tax'         => ['icon' => 'bi-percent',           'label' => 'Tax/GST'],
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
        <div class="col-12 mt-2 border p-3 bg-light rounded text-center">
            <i class="bi bi-rocket-takeoff d-block fs-3 mb-2 text-primary"></i>
            <h6>Looking for PhonePe, Cashfree, PayU, or Instamojo?</h6>
            <p class="text-muted small">We have upgraded our payment system. You can now manage multiple advanced gateways from the dedicated panel.</p>
            <a href="{{ route('admin.settings.payments') }}" class="btn btn-primary px-4">Open Advanced Payment Gateways</a>
        </div>
        
        <div class="col-12 mt-4">
            <h6 class="fw-bold border-bottom pb-2">Basic COD & Razorpay Settings</h6>
        </div>
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

        <div class="col-12 mt-4">
            <h6 class="fw-bold border-bottom pb-2">🚚 Logistics Engine (Shipping Provider)</h6>
        </div>
        <div class="col-md-6">
            <label class="form-label">Active Provider</label>
            <div class="d-flex gap-4 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shipping__active_provider" id="provShiprocket" value="shiprocket" {{ ($groups['shipping']['active_provider'] ?? 'shiprocket') === 'shiprocket' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="provShiprocket">Shiprocket</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shipping__active_provider" id="provIthink" value="ithink" {{ ($groups['shipping']['active_provider'] ?? '') === 'ithink' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="provIthink">iThink Logistics</label>
                </div>
            </div>
            <div class="form-text mt-1 text-danger">Fallback mechanism is enabled. If Active Provider fails, the system will try the other.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Auto Push Order on Creation</label>
            <select name="shipping__auto_create" class="form-select">
                <option value="1" {{ ($groups['shipping']['auto_create'] ?? '1') === '1' ? 'selected' : '' }}>Yes (Recommended)</option>
                <option value="0" {{ ($groups['shipping']['auto_create'] ?? '1') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <div class="col-12 mt-3">
            <h6 class="fw-bold text-muted" style="font-size: 13px; text-transform: uppercase;">🔹 Shiprocket Credentials</h6>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="shipping__shiprocket_email" class="form-control" value="{{ $groups['shipping']['shiprocket_email'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="shipping__shiprocket_password" class="form-control" value="{{ $groups['shipping']['shiprocket_password'] ?? '' }}">
        </div>

        <div class="col-12 mt-3">
            <h6 class="fw-bold text-muted" style="font-size: 13px; text-transform: uppercase;">🔹 iThink Logistics Credentials</h6>
        </div>
        <div class="col-md-6">
            <label class="form-label">Access Token</label>
            <input type="text" name="shipping__ithink_access_token" class="form-control" value="{{ $groups['shipping']['ithink_access_token'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Secret Key</label>
            <input type="password" name="shipping__ithink_secret_key" class="form-control" value="{{ $groups['shipping']['ithink_secret_key'] ?? '' }}" autocomplete="new-password">
        </div>

        <div class="col-12 mt-4">
            <h6 class="fw-bold border-bottom pb-2">🧠 Smart Courier Selection Engine</h6>
            <div class="form-text mb-3">When enabled, COD orders will auto-select the best courier via iThink Rate API before shipment creation. Admin can see carrier cost vs charged shipping in order details.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Smart Courier Selection</label>
            <select name="shipping__smart_courier_enabled" class="form-select">
                <option value="0" {{ ($groups['shipping']['smart_courier_enabled'] ?? '0') === '0' ? 'selected' : '' }}>OFF — iThink assigns courier</option>
                <option value="1" {{ ($groups['shipping']['smart_courier_enabled'] ?? '0') === '1' ? 'selected' : '' }}>ON — Auto-select best courier for COD</option>
            </select>
        </div>
        <div class="col-md-6">
            {{-- Spacer --}}
        </div>

        <div class="col-12 mt-3">
            <h6 class="fw-bold text-muted" style="font-size: 13px; text-transform: uppercase;">📦 Default Package Dimensions</h6>
            <div class="form-text mb-2">Used for rate calculation and shipment creation when product-level dimensions are not set.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label">Weight (kg)</label>
            <input type="text" name="shipping__default_weight_kg" class="form-control" value="{{ $groups['shipping']['default_weight_kg'] ?? '0.5' }}" placeholder="0.5">
        </div>
        <div class="col-md-3">
            <label class="form-label">Length (cm)</label>
            <input type="text" name="shipping__default_length_cm" class="form-control" value="{{ $groups['shipping']['default_length_cm'] ?? '10' }}" placeholder="10">
        </div>
        <div class="col-md-3">
            <label class="form-label">Width (cm)</label>
            <input type="text" name="shipping__default_width_cm" class="form-control" value="{{ $groups['shipping']['default_width_cm'] ?? '10' }}" placeholder="10">
        </div>
        <div class="col-md-3">
            <label class="form-label">Height (cm)</label>
            <input type="text" name="shipping__default_height_cm" class="form-control" value="{{ $groups['shipping']['default_height_cm'] ?? '10' }}" placeholder="10">
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

{{-- ═══ CONVERSION CONTROL ════════════════════════════════════ --}}
@if ($activeTab === 'conversion')
    <h5 class="mb-3">🎯 Conversion Control System</h5>
    <p class="small text-muted">Control the micro-copy displayed on product and checkout pages to optimize sales.</p>
    
    <div class="row g-4">
        <div class="col-12">
            <h6 class="fw-bold border-bottom pb-2">🛒 Checkout Page Copy</h6>
        </div>
        <div class="col-md-6">
            <label class="form-label">Prepaid Recommendation Text</label>
            <input type="text" name="conversion_copy__checkout__prepaid_message" class="form-control" value="{{ $groups['conversion_copy']['checkout']['prepaid_message'] ?? config('commerce.conversion_copy.checkout.prepaid_message') }}">
            <div class="form-text">Short aur benefit-based likho (e.g. safe payment, faster delivery).</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Prepaid Highlight Badge</label>
            <input type="text" name="conversion_copy__checkout__prepaid_badge" class="form-control" value="{{ $groups['conversion_copy']['checkout']['prepaid_badge'] ?? config('commerce.conversion_copy.checkout.prepaid_badge') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">COD Message</label>
            <input type="text" name="conversion_copy__checkout__cod_message" class="form-control" value="{{ $groups['conversion_copy']['checkout']['cod_message'] ?? config('commerce.conversion_copy.checkout.cod_message') }}">
            <div class="form-text">Extra charges ya trust clearly explain karo.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">COD Fee Message</label>
            <input type="text" name="conversion_copy__checkout__cod_fee_message" class="form-control" value="{{ $groups['conversion_copy']['checkout']['cod_fee_message'] ?? config('commerce.conversion_copy.checkout.cod_fee_message') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Delivery ETA Message</label>
            <input type="text" name="conversion_copy__checkout__delivery_eta" class="form-control" value="{{ $groups['conversion_copy']['checkout']['delivery_eta'] ?? config('commerce.conversion_copy.checkout.delivery_eta') }}">
            <div class="form-text">Logistics timeline clearly state karo.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Secure Checkout Message</label>
            <input type="text" name="conversion_copy__checkout__secure_message" class="form-control" value="{{ $groups['conversion_copy']['checkout']['secure_message'] ?? config('commerce.conversion_copy.checkout.secure_message') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Payment Error Message</label>
            <input type="text" name="conversion_copy__checkout__payment_error" class="form-control" value="{{ $groups['conversion_copy']['checkout']['payment_error'] ?? config('commerce.conversion_copy.checkout.payment_error') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Place Order Button CTA</label>
            <input type="text" name="conversion_copy__checkout__place_order_cta" class="form-control" value="{{ $groups['conversion_copy']['checkout']['place_order_cta'] ?? config('commerce.conversion_copy.checkout.place_order_cta') }}">
        </div>

        <div class="col-12 mt-4">
            <h6 class="fw-bold border-bottom pb-2">📦 Product Page Copy</h6>
            <div class="form-text mb-3">Note: Global settings. You can override testing urgency per product via Product Meta if needed.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Urgency Message</label>
            <input type="text" name="conversion_copy__product__urgency_message" class="form-control" value="{{ $groups['conversion_copy']['product']['urgency_message'] ?? config('commerce.conversion_copy.product.urgency_message') }}">
            <div class="form-text">Scarcity ya time-based urgency use karo (e.g. {stock} left, dispatch today).</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Buy Now Subtext</label>
            <input type="text" name="conversion_copy__product__buy_now_subtext" class="form-control" value="{{ $groups['conversion_copy']['product']['buy_now_subtext'] ?? config('commerce.conversion_copy.product.buy_now_subtext') }}" placeholder="e.g. Fastest checkout">
            <div class="form-text">Button ke neeche extra micro text.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Trust Badges Title</label>
            <input type="text" name="conversion_copy__product__trust_badges_title" class="form-control" value="{{ $groups['conversion_copy']['product']['trust_badges_title'] ?? config('commerce.conversion_copy.product.trust_badges_title') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Trust Badges Text</label>
            <input type="text" name="conversion_copy__product__trust_badges_text" class="form-control" value="{{ $groups['conversion_copy']['product']['trust_badges_text'] ?? config('commerce.conversion_copy.product.trust_badges_text') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Delivery Promise</label>
            <input type="text" name="conversion_copy__product__delivery_promise" class="form-control" value="{{ $groups['conversion_copy']['product']['delivery_promise'] ?? config('commerce.conversion_copy.product.delivery_promise') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Offer / Savings Message</label>
            <input type="text" name="conversion_copy__product__offer_message" class="form-control" value="{{ $groups['conversion_copy']['product']['offer_message'] ?? config('commerce.conversion_copy.product.offer_message') }}">
            <div class="form-text">Extra savings ya discount highlight karein.</div>
        </div>
    </div>
@endif

{{-- ═══ EMAIL / SMTP ════════════════════════════════════════ --}}
@if ($activeTab === 'email')
    <h5 class="mb-3">✉️ Email / SMTP Settings</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Mail Host</label>
            <input type="text" name="email__mail_host" class="form-control" value="{{ $groups['email']['mail_host'] ?? '' }}" placeholder="smtp.gmail.com">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail Port</label>
            <input type="text" name="email__mail_port" class="form-control" value="{{ $groups['email']['mail_port'] ?? '587' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail Username</label>
            <input type="text" name="email__mail_username" class="form-control" value="{{ $groups['email']['mail_username'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail Password</label>
            <input type="password" name="email__mail_password" class="form-control" value="{{ $groups['email']['mail_password'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail From Address</label>
            <input type="email" name="email__mail_from_address" class="form-control" value="{{ $groups['email']['mail_from_address'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail From Name</label>
            <input type="text" name="email__mail_from_name" class="form-control" value="{{ $groups['email']['mail_from_name'] ?? '' }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Mail Encryption</label>
            <select name="email__mail_encryption" class="form-select">
                <option value="tls" {{ ($groups['email']['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ ($groups['email']['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                <option value="" {{ ($groups['email']['mail_encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
            </select>
        </div>
    </div>
@endif

{{-- ═══ TAX / GST ═══════════════════════════════════════════ --}}
@if ($activeTab === 'tax')
    <h5 class="mb-3">🧾 Tax & GST Settings</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">GST Enabled</label>
            <select name="gst__enabled" class="form-select">
                <option value="1" {{ ($groups['gst']['enabled'] ?? '0') == '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ ($groups['gst']['enabled'] ?? '0') == '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">GST Inclusive Prices</label>
            <select name="gst__inclusive" class="form-select">
                <option value="1" {{ ($groups['gst']['inclusive'] ?? '1') == '1' ? 'selected' : '' }}>Yes (Prices include GST)</option>
                <option value="0" {{ ($groups['gst']['inclusive'] ?? '1') == '0' ? 'selected' : '' }}>No (Add GST at checkout)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Default GST Rate (%)</label>
            <select name="gst__rate" class="form-select">
                <option value="0" {{ ($groups['gst']['rate'] ?? '18') == '0' ? 'selected' : '' }}>0%</option>
                <option value="5" {{ ($groups['gst']['rate'] ?? '18') == '5' ? 'selected' : '' }}>5%</option>
                <option value="12" {{ ($groups['gst']['rate'] ?? '18') == '12' ? 'selected' : '' }}>12%</option>
                <option value="18" {{ ($groups['gst']['rate'] ?? '18') == '18' ? 'selected' : '' }}>18%</option>
                <option value="28" {{ ($groups['gst']['rate'] ?? '18') == '28' ? 'selected' : '' }}>28%</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Store GSTIN</label>
            <input type="text" name="gst__gstin" class="form-control" value="{{ $groups['gst']['gstin'] ?? '' }}" placeholder="22AAAAA0000A1Z5">
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
