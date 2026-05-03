<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->seo_title ?? $page->hero_headline }}</title>
    <meta name="description" content="{{ $page->seo_description ?? $page->hero_subheadline }}">
    <meta property="og:title" content="{{ $page->seo_title ?? $page->hero_headline }}">
    <meta property="og:description" content="{{ $page->seo_description ?? $page->hero_subheadline }}">
    @if($page->hero_image)
    <meta property="og:image" content="{{ asset('storage/' . $page->hero_image) }}">
    @endif
    @include('partials.tracking-head')
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></noscript>
    <style>
        /* ── Premium Color Palette & Variables ── */
        :root {
            --primary-dark: #0a0a0a;
            --primary-gold: #d4af37; /* Richer, classic gold */
            --light-gold: #fdfbf7;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --text-main: #1c1c1c;
            --text-muted: #6b7280;
            --bg-offwhite: #fafafa;
            --surface-white: #ffffff;
            --shadow-soft: 0 4px 20px rgba(0,0,0,0.03);
            --shadow-elevated: 0 10px 30px rgba(0,0,0,0.06);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            background: var(--bg-offwhite);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        img { max-width: 100%; height: auto; display: block; }

        /* ── Trust Top Bar ── */
        .lp-topbar {
            background: var(--primary-dark);
            color: #fff;
            text-align: center;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .lp-topbar span { opacity: 0.7; font-weight: 400; text-transform: none; }
        .lp-topbar strong { color: var(--primary-gold); letter-spacing: 1px;}

        /* ── Hero Section ── */
        .lp-hero {
            padding: 40px 20px 48px;
            text-align: center;
            background: linear-gradient(135deg, var(--light-gold) 0%, #fff 100%);
            position: relative;
            overflow: hidden;
        }
        .lp-hero::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(212,175,55,0.05) 0%, transparent 50%);
            z-index: 0; pointer-events: none;
        }
        .lp-hero > * { position: relative; z-index: 1; }

        .lp-hero-img {
            max-width: 320px;
            margin: 0 auto 28px;
            border-radius: 20px;
            box-shadow: var(--shadow-elevated);
            transition: transform 0.3s ease;
        }
        .lp-hero-img:hover { transform: translateY(-5px); }
        
        .lp-hero h1 {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 12px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .lp-hero .lp-sub {
            font-size: 16px;
            color: var(--text-muted);
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
        }
        .lp-price-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 20px;
        }
        .lp-price-old {
            font-size: 18px;
            color: #9ca3af;
            text-decoration: line-through;
            font-weight: 500;
        }
        .lp-price-new {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -1px;
        }
        .lp-badge {
            background: var(--primary-dark);
            color: var(--primary-gold);
            font-size: 11px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* ── Buttons (Sales Focused) ── */
        .lp-cta {
            display: block;
            width: 100%;
            max-width: 380px;
            margin: 0 auto 16px;
            padding: 18px 24px;
            background: linear-gradient(180deg, #1f1f1f 0%, #000000 100%);
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            border: 1px solid #333;
            border-radius: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        .lp-cta::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: skewX(-20deg); animation: shimmer 3s infinite;
        }
        @keyframes shimmer { 100% { left: 200%; } }
        .lp-cta:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,0.2); border-color: var(--primary-gold); }
        .lp-cta:active { transform: scale(0.98); }

        .lp-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #15803d;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            background: rgba(37, 211, 102, 0.1);
            transition: background 0.2s;
        }
        .lp-wa-btn:hover { background: rgba(37, 211, 102, 0.15); }
        .lp-wa-btn i { font-size: 18px; color: #25D366; }

        /* ── Layout Sections ── */
        .lp-section {
            padding: 48px 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        .lp-section-alt { background: var(--surface-white); }
        .lp-section-title {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 28px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .lp-section-title::after {
            content: ''; display: block; width: 40px; height: 3px;
            background: var(--primary-gold); margin: 12px auto 0; border-radius: 2px;
        }

        /* ── Pain Points (Elegant Cards) ── */
        .lp-pain-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .lp-pain-list li {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 16px;
            font-weight: 500;
            color: var(--text-main);
            padding: 18px 20px;
            background: var(--surface-white);
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: var(--shadow-soft);
            transition: transform 0.2s;
        }
        .lp-pain-list li:hover { transform: translateX(5px); border-color: rgba(239,68,68,0.2); }
        .lp-pain-list li i {
            font-size: 22px;
            color: var(--accent-red);
            flex-shrink: 0;
            background: rgba(239,68,68,0.1);
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
        }

        /* ── The System Steps ── */
        .lp-steps {
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: relative;
        }
        .lp-steps::before {
            content: ''; position: absolute; top: 30px; bottom: 30px; left: 34px;
            width: 2px; background: rgba(0,0,0,0.05); z-index: 0;
        }
        .lp-step {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 20px;
            background: var(--surface-white);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            position: relative;
            z-index: 1;
            border: 1px solid rgba(0,0,0,0.02);
        }
        .lp-step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-gold);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 0 0 6px #fff, 0 4px 10px rgba(212,175,55,0.3);
        }
        .lp-step-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f8f8f8;
        }
        .lp-step-body h3 { font-size: 16px; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px; }
        .lp-step-body p { font-size: 14px; color: var(--text-muted); line-height: 1.5; }

        /* ── Trust Bar & Description ── */
        .lp-trust-wrapper {
            background: var(--primary-dark);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
            background-image: radial-gradient(circle at top right, rgba(212,175,55,0.1), transparent 40%);
        }
        .lp-trust-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 24px 32px;
            max-width: 500px;
            margin: 0 auto;
        }
        .lp-trust-item {
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }
        .lp-trust-item strong {
            display: block;
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-gold);
            margin-bottom: 2px;
            letter-spacing: -0.5px;
            text-transform: none;
        }
        .lp-trust-desc {
            max-width: 420px;
            margin: 32px auto 0;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .lp-trust-desc p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
            margin: 0;
            font-weight: 400;
        }

        /* ── Offer Box (Premium Checkout Feel) ── */
        .lp-offer {
            background: var(--surface-white);
            border: 2px solid var(--primary-gold);
            border-radius: 24px;
            padding: 32px 24px;
            text-align: center;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            box-shadow: var(--shadow-elevated);
        }
        .lp-offer::before {
            content: 'LIMITED TIME OFFER';
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: var(--primary-gold); color: #fff;
            font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 4px 12px;
            border-radius: 20px;
        }
        .lp-offer h3 { font-size: 20px; font-weight: 800; margin-bottom: 20px; color: var(--primary-dark); }
        .lp-offer-items {
            list-style: none;
            margin-bottom: 24px;
            text-align: left;
            background: var(--bg-offwhite);
            padding: 16px;
            border-radius: 12px;
        }
        .lp-offer-items li {
            padding: 6px 0;
            font-size: 14px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        .lp-offer-items li i { color: var(--primary-gold); margin-right: 10px; font-size: 16px; }
        .lp-offer-badges {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 16px;
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .lp-offer-badges span { display: flex; align-items: center; }
        .lp-offer-badges span i { margin-right: 6px; color: var(--accent-green); font-size: 14px; }

        /* ── Reviews (Testimonials) ── */
        .lp-reviews {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .lp-review {
            background: var(--surface-white);
            border: none;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-soft);
            position: relative;
        }
        .lp-review::before {
            content: '\201C'; position: absolute; top: 10px; right: 20px;
            font-size: 60px; color: rgba(212,175,55,0.1); font-family: serif; line-height: 1;
        }
        .lp-review-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }
        .lp-review-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--bg-offwhite);
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .lp-review-name { font-weight: 700; font-size: 15px; color: var(--primary-dark); }
        .lp-review-stars { color: var(--primary-gold); font-size: 12px; margin-top: 2px; }
        .lp-review-text { font-size: 15px; color: var(--text-muted); line-height: 1.6; font-style: italic; }

        /* ── FAQ ── */
        .lp-faq-item {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px;
            margin-bottom: 12px;
            overflow: hidden;
            background: var(--surface-white);
            transition: all 0.3s;
        }
        .lp-faq-item:hover { border-color: rgba(0,0,0,0.1); }
        .lp-faq-q {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            color: var(--primary-dark);
        }
        .lp-faq-q i { transition: transform 0.3s; font-size: 16px; color: var(--primary-gold); }
        .lp-faq-a {
            padding: 0 20px 20px;
            font-size: 14px;
            color: var(--text-muted);
            display: none;
            line-height: 1.6;
        }
        .lp-faq-item.open { box-shadow: var(--shadow-soft); }
        .lp-faq-item.open .lp-faq-a { display: block; }
        .lp-faq-item.open .lp-faq-q i { transform: rotate(180deg); }

        /* ── Sticky Bottom Bar ── */
        .lp-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.08);
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .lp-sticky-cta {
            flex: 1;
            display: block;
            padding: 14px;
            background: var(--primary-dark);
            color: var(--primary-gold);
            font-size: 16px;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: transform 0.2s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .lp-sticky-cta:active { transform: scale(0.98); }
        .lp-sticky-wa {
            width: 50px;
            height: 50px;
            background: #25D366;
            color: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            text-decoration: none;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(37,211,102,0.3);
            transition: transform 0.2s;
        }
        .lp-sticky-wa:active { transform: scale(0.95); }

        /* ── Floating WhatsApp (Desktop) ── */
        .lp-float-wa {
            position: fixed;
            bottom: 86px;
            right: 24px;
            z-index: 998;
            width: 60px;
            height: 60px;
            background: #25D366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            text-decoration: none;
            box-shadow: 0 8px 25px rgba(37,211,102,0.4);
            transition: transform 0.3s;
        }
        .lp-float-wa:hover { transform: scale(1.05); }

        .lp-bottom-spacer { height: 80px; }

        @media (min-width: 640px) {
            .lp-hero h1 { font-size: 42px; }
            .lp-section { max-width: 600px; padding: 64px 20px; }
            .lp-hero-img { max-width: 400px; }
            .lp-float-wa { bottom: 30px; }
        }
    </style>
</head>
<body>
@include('partials.tracking-body')

@php
    $waLink = 'https://wa.me/91' . ($page->whatsapp_number ?? '8141939616') . '?text=' . urlencode('Hi, mujhe ' . $page->title . ' ke baare mein jaanna hai');
@endphp

{{-- ═══ TOP TRUST BAR ═══ --}}
<div class="lp-topbar">
    Powered by <strong>NR Beauty World</strong> <span>· 4.9★ · 17,000+ Google Reviews · 45 Years in Beauty</span>
</div>

{{-- ═══ SECTION 1: HERO ═══ --}}
<section class="lp-hero">
    @if($page->hero_image)
        <img src="{{ asset('storage/' . $page->hero_image) }}" alt="{{ $page->hero_headline }}" class="lp-hero-img">
    @endif

    <h1>{{ $page->hero_headline }}</h1>

    @if($page->hero_subheadline)
        <p class="lp-sub">{{ $page->hero_subheadline }}</p>
    @endif

    <div class="lp-price-row">
        @if($page->original_price)
            <span class="lp-price-old">₹{{ number_format($page->original_price) }}</span>
        @endif
        <span class="lp-price-new">₹{{ number_format($page->offer_price) }}</span>
        @if($page->offer_badge)
            <span class="lp-badge">{{ $page->offer_badge }}</span>
        @endif
    </div>

    <form action="{{ route('landing.buy', $page->slug) }}" method="POST" id="lpBuyForm">
        @csrf
        <button type="submit" class="lp-cta" id="lpBuyBtn">
            {{ $page->hero_cta_text ?? 'Abhi Order Karo' }}
        </button>
    </form>

    @if($page->whatsapp_number)
        <a href="{{ $waLink }}" target="_blank" class="lp-wa-btn">
            <i class="bi bi-whatsapp"></i> Koi sawaal? WhatsApp karo
        </a>
    @endif
</section>

{{-- ═══ SECTION 2: PAIN AGITATION ═══ --}}
@if($page->problem_points && count($page->problem_points) > 0)
<section class="lp-section lp-section-alt">
    <ul class="lp-pain-list">
        @foreach($page->problem_points as $pain)
            <li><i class="bi bi-exclamation-circle-fill"></i> {{ $pain }}</li>
        @endforeach
    </ul>
</section>
@endif

{{-- ═══ SECTION 3: THE SYSTEM — PRODUCTS ═══ --}}
@if($page->steps && count($page->steps) > 0)
<section class="lp-section">
    <h2 class="lp-section-title">2-Step Hair Control Routine</h2>
    <div class="lp-steps">
        @foreach($page->steps as $i => $step)
            <div class="lp-step">
                <div class="lp-step-num">{{ $i + 1 }}</div>
                @if(!empty($step['image']))
                    <img src="{{ asset('storage/' . $step['image']) }}" alt="{{ $step['title'] }}" class="lp-step-img">
                @endif
                <div class="lp-step-body">
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['desc'] ?? '' }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══ SECTION 4: TRUST BAR & DESCRIPTION ═══ --}}
<div class="lp-trust-wrapper">
    @if($page->trust_points && count($page->trust_points) > 0)
    <div class="lp-trust-bar">
        @foreach($page->trust_points as $tp)
            @php $parts = explode('|', $tp, 2); @endphp
            <div class="lp-trust-item">
                <strong>{{ trim($parts[0]) }}</strong>
                @if(!empty($parts[1])) {{ trim($parts[1]) }} @endif
            </div>
        @endforeach
    </div>
    @endif

    {{-- ═══ TRUST DESCRIPTION ═══ --}}
    @if($page->trust_description)
    <div class="lp-trust-desc">
        <p>{!! nl2br(e($page->trust_description)) !!}</p>
    </div>
    @endif
</div>

{{-- ═══ SECTION 5: OFFER BOX + CTA ═══ --}}
<section class="lp-section">
    <div class="lp-offer">
        <h3>{{ $page->title }}</h3>
        <ul class="lp-offer-items">
            @foreach($page->products as $prod)
                @php $product = $products[$prod['product_id']] ?? null; @endphp
                @if($product)
                    <li><i class="bi bi-check-circle-fill"></i> {{ $product->name }}</li>
                @endif
            @endforeach
        </ul>
        <div class="lp-price-row">
            @if($page->original_price)
                <span class="lp-price-old">₹{{ number_format($page->original_price) }}</span>
            @endif
            <span class="lp-price-new">₹{{ number_format($page->offer_price) }}</span>
        </div>
        <form action="{{ route('landing.buy', $page->slug) }}" method="POST">
            @csrf
            <button type="submit" class="lp-cta">{{ $page->hero_cta_text ?? 'Abhi Order Karo' }}</button>
        </form>
        <div class="lp-offer-badges">
            @if($page->show_free_ship)
                <span><i class="bi bi-truck"></i> Free Delivery</span>
            @endif
            @if($page->show_cod_badge)
                <span><i class="bi bi-cash-coin"></i> COD Available</span>
            @endif
            <span><i class="bi bi-arrow-repeat"></i> Easy Returns</span>
        </div>
    </div>
</section>

{{-- ═══ SECTION 6: REVIEWS (max 3) ═══ --}}
@if($page->reviews && count($page->reviews) > 0)
<section class="lp-section lp-section-alt">
    <h2 class="lp-section-title">Customers Ka Experience</h2>
    <div class="lp-reviews">
        @foreach(array_slice($page->reviews, 0, 3) as $review)
            <div class="lp-review">
                <div class="lp-review-header">
                    @if(!empty($review['photo']))
                        <img src="{{ asset('storage/' . $review['photo']) }}" alt="{{ $review['name'] }}" class="lp-review-avatar">
                    @else
                        <div class="lp-review-avatar" style="display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;color:#666;">
                            {{ strtoupper(substr($review['name'] ?? 'C', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="lp-review-name">{{ $review['name'] ?? 'Customer' }}</div>
                        <div class="lp-review-stars">
                            @for($s = 1; $s <= ($review['rating'] ?? 5); $s++)
                                <i class="bi bi-star-fill"></i>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="lp-review-text">{{ $review['text'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══ SECTION 7: FAQ (max 2) ═══ --}}
@if($page->faq && count($page->faq) > 0)
<section class="lp-section">
    <h2 class="lp-section-title">Common Questions</h2>
    @foreach(array_slice($page->faq, 0, 2) as $fq)
        <div class="lp-faq-item">
            <button class="lp-faq-q" onclick="this.parentElement.classList.toggle('open')">
                {{ $fq['q'] ?? '' }}
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="lp-faq-a">{{ $fq['a'] ?? '' }}</div>
        </div>
    @endforeach
</section>
@endif

{{-- ═══ Bottom Spacer ═══ --}}
<div class="lp-bottom-spacer"></div>

{{-- ═══ STICKY BOTTOM BAR ═══ --}}
<div class="lp-sticky">
    <form action="{{ route('landing.buy', $page->slug) }}" method="POST" style="flex:1;display:flex;">
        @csrf
        <button type="submit" class="lp-sticky-cta">₹{{ number_format($page->offer_price) }} — Abhi Order Karo</button>
    </form>
    @if($page->whatsapp_number)
        <a href="{{ $waLink }}" target="_blank" class="lp-sticky-wa"><i class="bi bi-whatsapp"></i></a>
    @endif
</div>

{{-- ═══ FLOATING WHATSAPP (desktop) ═══ --}}
@if($page->whatsapp_number)
<a href="{{ $waLink }}" target="_blank" class="lp-float-wa" style="display:none;" id="lpFloatWa">
    <i class="bi bi-whatsapp"></i>
</a>
<script>
    // Show floating WA only on desktop (sticky bar has WA on mobile)
    if (window.innerWidth >= 640) {
        document.getElementById('lpFloatWa').style.display = 'flex';
    }
</script>
@endif

{{-- ═══ Meta Pixel ViewContent ═══ --}}
<script>
    if (typeof fbq === 'function') {
        fbq('track', 'ViewContent', {
            value: {{ $page->offer_price }},
            currency: 'INR',
            content_type: 'product_group',
            content_name: @json($page->title)
        });
    }
</script>
</body>
</html>
