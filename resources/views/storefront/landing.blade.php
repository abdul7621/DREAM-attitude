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
    @if($page->hero_image)
    <link rel="preload" as="image" href="{{ asset('storage/' . $page->hero_image) }}">
    @endif
    @include('partials.tracking-head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></noscript>
    <style>
        /* ── Premium Luxury Color Palette & Variables ── */
        :root {
            --primary-dark: #161513; /* Elegant rich onyx/charcoal */
            --primary-gold: #c5a880; /* Elegant champagne gold */
            --primary-gold-dark: #a38258;
            --gold-gradient: linear-gradient(135deg, #e5d5c0 0%, #c5a880 50%, #a38258 100%);
            --gold-btn-gradient: linear-gradient(135deg, #dfcbab 0%, #c5a880 50%, #a38258 100%);
            --gold-glow: 0 0 15px rgba(197, 168, 128, 0.35);
            --light-gold: #faf7f2;
            --accent-green: #2c5e43; /* Muted luxury emerald for trust */
            --accent-red: #9e2a2b;   /* Muted luxury crimson for concerns */
            --text-main: #2b2825;    /* Warm dark charcoal */
            --text-muted: #78726c;   /* Warm muted gray */
            --bg-offwhite: #faf8f5;  /* Creamy soft off-white */
            --surface-white: #ffffff;
            --border-light: rgba(197, 168, 128, 0.15);
            --shadow-soft: 0 10px 30px rgba(28, 25, 23, 0.03);
            --shadow-elevated: 0 20px 45px rgba(28, 25, 23, 0.06);
            --shadow-button: 0 8px 24px rgba(163, 130, 88, 0.3);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            background: var(--bg-offwhite);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        img { max-width: 100%; height: auto; display: block; }
        
        /* Typography overrides */
        h1, h2, h3, .lp-section-title, .lp-step-body h3, .lp-offer h3 {
            font-family: 'Playfair Display', Georgia, serif;
        }

        /* ── Trust Top Bar ── */
        .lp-topbar {
            background: var(--primary-dark);
            color: #fff;
            text-align: center;
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(197, 168, 128, 0.2);
        }
        .lp-topbar span { opacity: 0.8; font-weight: 400; text-transform: none; letter-spacing: 0.3px; color: #e5e0d8; }
        .lp-topbar strong { color: var(--primary-gold); letter-spacing: 1px; font-weight: 700; }

        /* ── Hero Section ── */
        .lp-hero {
            padding: 48px 20px;
            text-align: center;
            background: radial-gradient(circle at center, #faf7f2 0%, #f4ede0 100%);
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border-light);
        }
        .lp-hero::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(197, 168, 128, 0.08) 0%, transparent 60%);
            z-index: 0; pointer-events: none;
        }
        .lp-hero > * { position: relative; z-index: 1; }

        .lp-hero-img {
            max-width: 320px;
            margin: 0 auto 32px;
            border-radius: 20px;
            padding: 8px;
            background: #ffffff;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-elevated);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .lp-hero-img:hover { 
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 25px 50px rgba(28, 25, 23, 0.12);
            border-color: var(--primary-gold);
        }
        
        .lp-hero h1 {
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .lp-hero .lp-sub {
            font-size: 15px;
            color: var(--text-muted);
            margin-bottom: 28px;
            max-width: 440px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
            line-height: 1.6;
        }
        .lp-price-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .lp-price-old {
            font-size: 18px;
            color: #b5ada5;
            text-decoration: line-through;
            font-weight: 400;
        }
        .lp-price-new {
            font-size: 38px;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
            font-family: 'Playfair Display', Georgia, serif;
        }
        .lp-badge {
            background: var(--primary-dark);
            color: var(--primary-gold);
            font-size: 10px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border: 1px solid var(--primary-gold);
            box-shadow: 0 4px 12px rgba(28, 25, 23, 0.1);
        }

        /* ── Buttons (Sales Focused) ── */
        .lp-cta {
            display: block;
            width: 100%;
            max-width: 380px;
            margin: 0 auto 18px;
            padding: 18px 24px;
            background: var(--gold-btn-gradient);
            color: var(--primary-dark);
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid var(--primary-gold);
            border-radius: 100px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: var(--shadow-button);
            position: relative;
            overflow: hidden;
            animation: pulse-gold 3s infinite;
        }
        .lp-cta::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-25deg); animation: shimmer 3s infinite;
        }
        @keyframes shimmer { 100% { left: 200%; } }
        @keyframes pulse-gold {
            0% {
                box-shadow: 0 8px 24px rgba(197, 168, 128, 0.3), 0 0 0 0 rgba(197, 168, 128, 0.4);
            }
            70% {
                box-shadow: 0 8px 24px rgba(197, 168, 128, 0.3), 0 0 0 10px rgba(197, 168, 128, 0);
            }
            100% {
                box-shadow: 0 8px 24px rgba(197, 168, 128, 0.3), 0 0 0 0 rgba(197, 168, 128, 0);
            }
        }
        .lp-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(197, 168, 128, 0.5), inset 0 1px 0 rgba(255,255,255,0.2);
            filter: brightness(1.05);
        }
        .lp-cta:active { transform: scale(0.97); }

        .lp-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1b4d3e;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 100px;
            background: rgba(44, 94, 67, 0.08);
            border: 1px solid rgba(44, 94, 67, 0.15);
            transition: all 0.2s;
        }
        .lp-wa-btn:hover { background: rgba(44, 94, 67, 0.12); transform: translateY(-1px); }
        .lp-wa-btn i { font-size: 18px; color: #25D366; }

        /* ── Layout Sections ── */
        .lp-section {
            padding: 56px 20px;
            max-width: 500px;
            margin: 0 auto;
        }
        .lp-section-alt { background: var(--surface-white); border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); }
        .lp-section-title {
            font-size: 26px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 36px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .lp-section-title::after {
            content: ''; display: block; width: 50px; height: 2px;
            background: var(--primary-gold); margin: 14px auto 0; opacity: 0.8;
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
            font-size: 15px;
            font-weight: 500;
            color: var(--text-main);
            padding: 16px 20px;
            background: var(--bg-offwhite);
            border-radius: 16px;
            border: 1px solid rgba(197, 168, 128, 0.12);
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }
        .lp-pain-list li:hover {
            transform: translateX(4px);
            border-color: rgba(158, 42, 43, 0.3);
            background: #fff;
        }
        .lp-pain-list li i {
            font-size: 20px;
            color: var(--accent-red);
            flex-shrink: 0;
            background: rgba(158, 42, 43, 0.08);
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
        }

        /* ── The System Steps ── */
        .lp-steps {
            display: flex;
            flex-direction: column;
            gap: 28px;
            position: relative;
        }
        .lp-steps::before {
            content: ''; position: absolute; top: 30px; bottom: 30px; left: 36px;
            width: 1px; background: rgba(197, 168, 128, 0.25); z-index: 0;
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
            border: 1px solid var(--border-light);
            transition: all 0.3s ease;
        }
        .lp-step:hover {
            transform: translateY(-2px);
            border-color: var(--primary-gold);
            box-shadow: var(--shadow-elevated);
        }
        .lp-step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-dark);
            color: var(--primary-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 0 0 6px #fff, 0 4px 12px rgba(28, 25, 23, 0.15);
            border: 1px solid var(--primary-gold);
        }
        .lp-step-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            background: var(--light-gold);
            border: 1px solid rgba(197, 168, 128, 0.15);
        }
        .lp-step-body h3 {
            font-size: 17px;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 6px;
        }
        .lp-step-body p {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ── Trust Bar & Description ── */
        .lp-trust-wrapper {
            background: var(--primary-dark);
            color: #fff;
            padding: 48px 20px;
            text-align: center;
            background-image: radial-gradient(circle at top right, rgba(197, 168, 128, 0.15), transparent 45%);
            border-top: 1px solid rgba(197, 168, 128, 0.2);
            border-bottom: 1px solid rgba(197, 168, 128, 0.2);
        }
        .lp-trust-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 28px 36px;
            max-width: 500px;
            margin: 0 auto;
        }
        .lp-trust-item {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            color: #dcd7ce;
        }
        .lp-trust-item strong {
            display: block;
            font-size: 30px;
            font-weight: 700;
            color: var(--primary-gold);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
            text-transform: none;
            font-family: 'Playfair Display', serif;
        }
        .lp-trust-desc {
            max-width: 420px;
            margin: 36px auto 0;
            padding-top: 28px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .lp-trust-desc p {
            font-size: 13.5px;
            color: rgba(229, 224, 216, 0.75);
            line-height: 1.8;
            margin: 0;
            font-weight: 400;
        }

        /* ── Offer Box (Premium Checkout Feel) ── */
        .lp-offer {
            background: var(--surface-white);
            border: 1px solid var(--primary-gold);
            border-radius: 24px;
            padding: 36px 24px;
            text-align: center;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            box-shadow: var(--shadow-elevated);
            outline: 1px solid rgba(197, 168, 128, 0.3);
            outline-offset: -8px;
        }
        .lp-offer::before {
            content: 'LIMITED TIME EXCLUSIVE';
            position: absolute; top: -11px; left: 50%; transform: translateX(-50%);
            background: var(--gold-btn-gradient); color: var(--primary-dark);
            font-size: 9px; font-weight: 800; letter-spacing: 1.5px; padding: 5px 16px;
            border-radius: 100px;
            box-shadow: 0 4px 10px rgba(197, 168, 128, 0.25);
        }
        .lp-offer h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }
        .lp-offer-items {
            list-style: none;
            margin-bottom: 28px;
            text-align: left;
            background: var(--light-gold);
            padding: 20px 24px;
            border-radius: 16px;
            border: 1px solid rgba(197, 168, 128, 0.08);
        }
        .lp-offer-items li {
            padding: 8px 0;
            font-size: 14px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        .lp-offer-items li i { color: var(--primary-gold-dark); margin-right: 12px; font-size: 15px; }
        .lp-offer-badges {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 20px;
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .lp-offer-badges span { display: flex; align-items: center; }
        .lp-offer-badges span i { margin-right: 6px; color: var(--accent-green); font-size: 15px; }

        /* ── Reviews (Testimonials) ── */
        .lp-reviews {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .lp-review {
            background: var(--bg-offwhite);
            border: 1px solid rgba(197, 168, 128, 0.1);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-soft);
            position: relative;
            transition: all 0.3s ease;
        }
        .lp-review:hover {
            border-color: var(--primary-gold);
            background: #ffffff;
            box-shadow: var(--shadow-elevated);
        }
        .lp-review::before {
            content: '\201C'; position: absolute; top: 12px; right: 24px;
            font-size: 70px; color: rgba(197, 168, 128, 0.15); font-family: 'Playfair Display', serif; line-height: 1;
        }
        .lp-review-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }
        .lp-review-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            border: 1.5px solid var(--primary-gold);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .lp-review-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-dark);
            font-family: 'Playfair Display', serif;
        }
        .lp-review-stars { color: var(--primary-gold-dark); font-size: 11px; margin-top: 3px; letter-spacing: 1px; }
        .lp-review-text {
            font-size: 14.5px;
            color: var(--text-main);
            line-height: 1.7;
            font-style: italic;
            font-weight: 400;
        }
        .lp-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--accent-green);
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── FAQ ── */
        .lp-faq-item {
            border: 1px solid rgba(197, 168, 128, 0.2);
            border-radius: 16px;
            margin-bottom: 12px;
            overflow: hidden;
            background: var(--surface-white);
            transition: all 0.3s;
        }
        .lp-faq-item:hover { border-color: var(--primary-gold); }
        .lp-faq-q {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            color: var(--primary-dark);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .lp-faq-q i { transition: transform 0.3s; font-size: 14px; color: var(--primary-gold-dark); }
        .lp-faq-a {
            padding: 0 24px 20px;
            font-size: 14px;
            color: var(--text-muted);
            display: none;
            line-height: 1.6;
        }
        .lp-faq-item.open { box-shadow: var(--shadow-soft); border-color: var(--primary-gold); }
        .lp-faq-item.open .lp-faq-a { display: block; }
        .lp-faq-item.open .lp-faq-q { color: var(--primary-gold-dark); }
        .lp-faq-item.open .lp-faq-q i { transform: rotate(180deg); }

        /* ── Sticky Bottom Bar ── */
        .lp-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 -8px 30px rgba(28, 25, 23, 0.08);
            border-top: 1px solid rgba(197, 168, 128, 0.2);
        }
        .lp-sticky-cta {
            flex: 1;
            display: block;
            padding: 15px;
            background: var(--gold-btn-gradient);
            color: var(--primary-dark);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: none;
            border-radius: 100px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: var(--shadow-button);
        }
        .lp-sticky-cta:active { transform: scale(0.97); }
        .lp-sticky-wa {
            width: 48px;
            height: 48px;
            background: #25D366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            text-decoration: none;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(37,211,102,0.35);
            transition: all 0.2s;
        }
        .lp-sticky-wa:active { transform: scale(0.95); }

        /* ── Floating WhatsApp (Desktop) ── */
        .lp-float-wa {
            position: fixed;
            bottom: 92px;
            right: 24px;
            z-index: 998;
            width: 56px;
            height: 56px;
            background: #25D366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(37,211,102,0.3);
            transition: all 0.3s ease;
        }
        .lp-float-wa:hover { transform: scale(1.08) translateY(-2px); box-shadow: 0 12px 28px rgba(37,211,102,0.4); }

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
<main>
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
        <img src="{{ asset('storage/' . $page->hero_image) }}" alt="{{ $page->hero_headline }}" class="lp-hero-img"
             width="320" height="320" loading="eager" fetchpriority="high">
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
                    <img src="{{ asset('storage/' . $step['image']) }}" alt="{{ $step['title'] }}" class="lp-step-img"
                         width="280" height="280" loading="lazy">
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
                        <div class="lp-verified-badge"><i class="bi bi-patch-check-fill"></i> Verified Buyer</div>
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
</main>

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
