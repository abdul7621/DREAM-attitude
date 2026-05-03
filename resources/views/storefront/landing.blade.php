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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            color: #1a1a1a;
            background: #fff;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; height: auto; display: block; }

        /* ── Trust Top Bar ────────────────────────── */
        .lp-topbar {
            background: #111;
            color: #fff;
            text-align: center;
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .lp-topbar span { opacity: 0.7; }
        .lp-topbar strong { color: #fbbf24; }

        /* ── Hero ─────────────────────────────────── */
        .lp-hero {
            padding: 32px 20px 40px;
            text-align: center;
            background: linear-gradient(180deg, #fefce8 0%, #fff 100%);
        }
        .lp-hero-img {
            max-width: 340px;
            margin: 0 auto 24px;
            border-radius: 16px;
        }
        .lp-hero h1 {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 10px;
            color: #111;
        }
        .lp-hero .lp-sub {
            font-size: 15px;
            color: #555;
            margin-bottom: 20px;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }
        .lp-price-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .lp-price-old {
            font-size: 18px;
            color: #999;
            text-decoration: line-through;
        }
        .lp-price-new {
            font-size: 32px;
            font-weight: 800;
            color: #111;
        }
        .lp-badge {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── CTA Button ──────────────────────────── */
        .lp-cta {
            display: block;
            width: 100%;
            max-width: 360px;
            margin: 0 auto 12px;
            padding: 16px 24px;
            background: #111;
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(0,0,0,0.2);
        }
        .lp-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.25); }
        .lp-cta:active { transform: scale(0.98); }

        .lp-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #25D366;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            margin-top: 4px;
        }
        .lp-wa-btn i { font-size: 18px; }

        /* ── Section Base ─────────────────────────── */
        .lp-section {
            padding: 40px 20px;
            max-width: 480px;
            margin: 0 auto;
        }
        .lp-section-alt { background: #f9fafb; }
        .lp-section-title {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            color: #111;
        }

        /* ── Pain Section ─────────────────────────── */
        .lp-pain-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .lp-pain-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 500;
            color: #333;
            padding: 14px 16px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .lp-pain-list li i {
            font-size: 20px;
            color: #dc2626;
            flex-shrink: 0;
        }

        /* ── Steps ────────────────────────────────── */
        .lp-steps {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .lp-step {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 16px;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }
        .lp-step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            flex-shrink: 0;
        }
        .lp-step-img {
            width: 72px;
            height: 72px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .lp-step-body h3 { font-size: 15px; font-weight: 700; color: #111; margin-bottom: 2px; }
        .lp-step-body p { font-size: 13px; color: #666; }

        /* ── Trust Bar ────────────────────────────── */
        .lp-trust-bar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px 24px;
            padding: 24px 20px;
            background: #111;
            color: #fff;
        }
        .lp-trust-item {
            text-align: center;
            font-size: 13px;
            font-weight: 500;
        }
        .lp-trust-item strong {
            display: block;
            font-size: 20px;
            font-weight: 800;
            color: #fbbf24;
        }

        /* ── Offer Box ────────────────────────────── */
        .lp-offer {
            background: #fff;
            border: 2px solid #111;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            max-width: 380px;
            margin: 0 auto;
        }
        .lp-offer h3 { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
        .lp-offer-items {
            list-style: none;
            margin-bottom: 16px;
        }
        .lp-offer-items li {
            padding: 6px 0;
            font-size: 14px;
            color: #333;
        }
        .lp-offer-items li i { color: #16a34a; margin-right: 6px; }
        .lp-offer-badges {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 12px;
            font-size: 12px;
            color: #666;
        }
        .lp-offer-badges span i { margin-right: 4px; color: #16a34a; }

        /* ── Reviews ──────────────────────────────── */
        .lp-reviews {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .lp-review {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
        }
        .lp-review-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .lp-review-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #f3f4f6;
        }
        .lp-review-name { font-weight: 600; font-size: 14px; }
        .lp-review-stars { color: #fbbf24; font-size: 13px; }
        .lp-review-text { font-size: 14px; color: #444; line-height: 1.5; }

        /* ── FAQ ──────────────────────────────────── */
        .lp-faq-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 10px;
            overflow: hidden;
            background: #fff;
        }
        .lp-faq-q {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            color: #111;
        }
        .lp-faq-q i { transition: transform 0.2s; font-size: 14px; color: #999; }
        .lp-faq-a {
            padding: 0 16px 14px;
            font-size: 13px;
            color: #555;
            display: none;
        }
        .lp-faq-item.open .lp-faq-a { display: block; }
        .lp-faq-item.open .lp-faq-q i { transform: rotate(180deg); }

        /* ── Sticky Bottom Bar ────────────────────── */
        .lp-sticky {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: #111;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }
        .lp-sticky-cta {
            flex: 1;
            display: block;
            padding: 12px;
            background: #fbbf24;
            color: #111;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .lp-sticky-wa {
            width: 44px;
            height: 44px;
            background: #25D366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            text-decoration: none;
            flex-shrink: 0;
        }

        /* ── Floating WhatsApp ────────────────────── */
        .lp-float-wa {
            position: fixed;
            bottom: 76px;
            right: 16px;
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
            box-shadow: 0 4px 14px rgba(37,211,102,0.4);
            animation: lp-pulse 2s infinite;
        }
        @keyframes lp-pulse {
            0%, 100% { box-shadow: 0 4px 14px rgba(37,211,102,0.4); }
            50% { box-shadow: 0 4px 24px rgba(37,211,102,0.6); }
        }

        /* ── Spacer for sticky bar ────────────────── */
        .lp-bottom-spacer { height: 70px; }

        /* ── Desktop tweaks ───────────────────────── */
        @media (min-width: 640px) {
            .lp-hero h1 { font-size: 36px; }
            .lp-section { max-width: 560px; }
            .lp-hero-img { max-width: 400px; }
            .lp-float-wa { bottom: 84px; right: 24px; }
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
            {{ $page->hero_cta_text ?? 'Abhi Order Karo' }} — ₹{{ number_format($page->offer_price) }}
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

{{-- ═══ SECTION 4: TRUST BAR ═══ --}}
@if($page->trust_points && count($page->trust_points) > 0)
<div class="lp-trust-bar">
    @foreach($page->trust_points as $tp)
        @php
            $parts = explode('|', $tp, 2);
        @endphp
        <div class="lp-trust-item">
            <strong>{{ trim($parts[0]) }}</strong>
            @if(!empty($parts[1])) {{ trim($parts[1]) }} @endif
        </div>
    @endforeach
</div>
@endif

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
            <button type="submit" class="lp-cta">{{ $page->hero_cta_text ?? 'Abhi Order Karo' }} — ₹{{ number_format($page->offer_price) }}</button>
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
