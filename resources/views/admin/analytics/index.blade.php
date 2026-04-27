@extends('layouts.admin')
@section('title', 'Decision Engine')

@push('admin-styles')
<style>
    .kpi-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; height: 100%; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .kpi-title { font-size: 0.85rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .kpi-value { font-size: 1.8rem; font-weight: 700; color: #111827; }
    
    .funnel-container { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .funnel-step { display: flex; align-items: center; margin-bottom: 16px; }
    .funnel-label { width: 140px; font-weight: 500; font-size: 0.9rem; color: #374151; }
    .funnel-bar-wrapper { flex: 1; height: 28px; background: #f3f4f6; border-radius: 4px; overflow: hidden; position: relative; }
    .funnel-bar { height: 100%; background: #3b82f6; border-radius: 4px; display: flex; align-items: center; padding-left: 12px; color: #fff; font-size: 0.8rem; font-weight: 600; transition: width 0.5s ease; }
    .funnel-bar.step-product { background: #60a5fa; }
    .funnel-bar.step-cart { background: #f59e0b; }
    .funnel-bar.step-checkout { background: #10b981; }
    .funnel-bar.step-purchase { background: #059669; }
    .funnel-pct { width: 60px; text-align: right; font-weight: 600; font-size: 0.9rem; color: #4b5563; }
    
    .table-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .table-card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #111827; font-size: 1rem; }
    
    .live-dot { width: 8px; height: 8px; background: #dc2626; border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); } }
    
    .feed-item { padding: 10px 16px; border-bottom: 1px solid #f3f4f6; font-size: 0.82rem; display: flex; align-items: center; gap: 8px; }
    .feed-item:last-child { border-bottom: none; }
    .feed-time { color: #9ca3af; font-size: 0.7rem; width: 40px; flex-shrink: 0; }
    .feed-event { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; color: #4b5563; font-size: 0.75rem; }

    .pulse-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); height: 100%; }
    .pulse-card .pulse-header { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; }
    .pulse-card .pulse-body { padding: 12px 16px; }
    .pulse-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 0.82rem; }
    .pulse-row .pulse-label { color: #374151; }
    .pulse-row .pulse-count { font-weight: 700; color: #111827; }
    .pulse-big { font-size: 2rem; font-weight: 800; color: #111827; line-height: 1; }
    .pulse-sub { font-size: 0.75rem; color: #6b7280; margin-top: 4px; }
    .source-badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 0.65rem; font-weight: 600; }
    .source-facebook { background: #dbeafe; color: #1d4ed8; }
    .source-direct { background: #f3f4f6; color: #374151; }
    .source-google { background: #dcfce7; color: #166534; }
    .source-instagram { background: #fce7f3; color: #9d174d; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1" style="font-weight: 700; color: #111827;">Decision Engine</h1>
        <p class="text-muted mb-0">Storefront Intelligence & Traffic Analytics</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.analytics.sessions') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-clock-history"></i> Sessions
        </a>
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="range" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 140px;">
                <option value="today" {{ $range == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $range == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="7d" {{ $range == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="14d" {{ $range == '14d' ? 'selected' : '' }}>Last 14 Days</option>
                <option value="30d" {{ $range == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90d" {{ $range == '90d' ? 'selected' : '' }}>Last 90 Days</option>
            </select>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- LIVE VISITOR PULSE                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #fef2f2, #fff7ed, #fefce8); border: 1px solid #fecaca;">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="live-dot"></span>
        <span class="fw-bold" style="font-size: 1.1rem; color: #111827;">{{ $livePulse['total'] }} Live Visitors</span>
        <span class="text-muted small">(last 5 min, bots excluded)</span>
    </div>
    <div class="row g-3">
        {{-- Card 1: Traffic Sources --}}
        <div class="col-lg col-sm-6">
            <div class="pulse-card">
                <div class="pulse-header"><i class="bi bi-broadcast me-1"></i> Traffic Sources</div>
                <div class="pulse-body">
                    @forelse(array_slice($livePulse['sources'], 0, 5, true) as $source => $count)
                        <div class="pulse-row">
                            <span class="pulse-label">
                                @php
                                    $srcLower = strtolower($source);
                                    $badgeClass = match(true) {
                                        str_contains($srcLower, 'facebook') || str_contains($srcLower, 'meta') => 'source-facebook',
                                        str_contains($srcLower, 'instagram') => 'source-instagram',
                                        str_contains($srcLower, 'google') => 'source-google',
                                        default => 'source-direct',
                                    };
                                @endphp
                                <span class="source-badge {{ $badgeClass }}">{{ $source }}</span>
                            </span>
                            <span class="pulse-count">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-muted small py-2">No active visitors</div>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Card 2: Visitor Intent --}}
        <div class="col-lg col-sm-6">
            <div class="pulse-card">
                <div class="pulse-header"><i class="bi bi-bullseye me-1"></i> Visitor Intent</div>
                <div class="pulse-body">
                    <div class="pulse-row">
                        <span class="pulse-label"><i class="bi bi-fire text-danger me-1"></i> High Intent</span>
                        <span class="pulse-count text-danger">{{ $livePulse['intents']['high_intent'] }}</span>
                    </div>
                    <div class="pulse-row">
                        <span class="pulse-label"><i class="bi bi-eye text-primary me-1"></i> Evaluators</span>
                        <span class="pulse-count text-primary">{{ $livePulse['intents']['product_evaluators'] }}</span>
                    </div>
                    <div class="pulse-row">
                        <span class="pulse-label"><i class="bi bi-person text-secondary me-1"></i> Cold Browsers</span>
                        <span class="pulse-count text-secondary">{{ $livePulse['intents']['cold_browsers'] }}</span>
                    </div>
                    <div class="pulse-row">
                        <span class="pulse-label"><i class="bi bi-arrow-repeat text-success me-1"></i> Returning</span>
                        <span class="pulse-count text-success">{{ $livePulse['intents']['customers'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- Card 3: Live Products --}}
        <div class="col-lg col-sm-6">
            <div class="pulse-card">
                <div class="pulse-header"><i class="bi bi-box-seam me-1"></i> Live Products</div>
                <div class="pulse-body">
                    @forelse($liveProducts as $prod)
                        <div class="pulse-row">
                            <span class="pulse-label text-truncate" style="max-width: 140px;" title="{{ $prod['name'] }}">{{ Str::limit($prod['name'], 22) }}</span>
                            <span class="pulse-count text-nowrap">
                                <i class="bi bi-eye text-primary"></i> {{ $prod['views'] }}
                                @if($prod['atc'] > 0)
                                    <i class="bi bi-cart-plus text-success ms-1"></i> {{ $prod['atc'] }}
                                @endif
                            </span>
                        </div>
                    @empty
                        <div class="text-muted small py-2">No product activity</div>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Card 4: Geography --}}
        <div class="col-lg col-sm-6">
            <div class="pulse-card">
                <div class="pulse-header"><i class="bi bi-geo-alt me-1"></i> Geography</div>
                <div class="pulse-body">
                    @forelse(array_slice($livePulse['geography'], 0, 5, true) as $city => $count)
                        <div class="pulse-row">
                            <span class="pulse-label">{{ $city }}</span>
                            <span class="pulse-count">{{ $count }}</span>
                        </div>
                    @empty
                        <div class="text-muted small py-2">GeoIP pending setup</div>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Card 5: Campaign Pulse --}}
        <div class="col-lg col-sm-6">
            <div class="pulse-card">
                <div class="pulse-header"><i class="bi bi-megaphone me-1"></i> Campaign Pulse</div>
                <div class="pulse-body">
                    @forelse(array_slice($livePulse['campaigns'], 0, 4, true) as $campaign => $count)
                        <div class="pulse-row">
                            <span class="pulse-label text-truncate" style="max-width: 120px;" title="{{ $campaign }}">{{ Str::limit($campaign, 16) }}</span>
                            <span class="pulse-count">{{ $count }} live</span>
                        </div>
                    @empty
                        <div class="text-muted small py-2">No active campaigns</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- KPI CARDS                                                          --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-title">Visitors</div>
            <div class="kpi-value">{{ number_format($overview['visitors']) }}</div>
            <div class="text-muted small mt-1">{{ number_format($overview['sessions']) }} Sessions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-title">Bounce Rate</div>
            <div class="kpi-value">{{ $overview['bounce_rate'] }}%</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-left: 4px solid #ef4444;">
            <div class="kpi-title">Abandoned Carts</div>
            <div class="kpi-value text-danger">{{ $abandonment['abandonment_rate'] }}%</div>
            <div class="text-muted small mt-1 text-danger fw-semibold">₹{{ number_format($abandonment['lost_revenue'], 0) }} Lost Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-title">Conversion Rate</div>
            <div class="kpi-value text-success">{{ $overview['conversion_rate'] }}%</div>
            <div class="text-muted small mt-1 fw-bold text-success">₹{{ number_format($overview['revenue'], 0) }} Won Revenue</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- CAPTURE ENGINE & RECOVERY OS                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <h5 class="mb-3" style="font-weight: 600;">Capture Engine & Recovery Intelligence</h5>
    </div>
    <div class="col-md-4">
        <div class="kpi-card" style="background: linear-gradient(135deg, var(--color-bg-surface), rgba(201,168,76,0.1)); border: 1px solid var(--color-gold);">
            <div class="kpi-title" style="color: var(--color-gold);">Recovered Revenue</div>
            <div class="kpi-value text-gold">₹{{ number_format($captureStats['recovered_revenue'], 0) }}</div>
            <div class="text-muted small mt-1"><i class="bi bi-arrow-repeat me-1"></i> From {{ number_format($captureStats['recovered_count']) }} recovered carts</div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100 border-0 shadow-sm" style="background: var(--color-bg-surface);">
            <div class="card-body p-4">
                <h6 class="mb-4 text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 1px;">Checkout Offer A/B Lift</h6>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="flex: 1;">
                        <div class="text-muted small mb-1">Variant A (Modal Offer)</div>
                        <div class="fw-bold fs-5">{{ $captureStats['variant_a_conv'] }}% <span class="text-muted fs-6 fw-normal">({{ $captureStats['variant_a_orders'] }} orders)</span></div>
                    </div>
                    <div style="width: 1px; height: 40px; background: var(--color-border); margin: 0 20px;"></div>
                    <div style="flex: 1;">
                        <div class="text-muted small mb-1">Control (Standard Checkout)</div>
                        <div class="fw-bold fs-5">{{ $captureStats['control_conv'] }}% <span class="text-muted fs-6 fw-normal">({{ $captureStats['control_orders'] }} orders)</span></div>
                    </div>
                    <div style="width: 1px; height: 40px; background: var(--color-border); margin: 0 20px;"></div>
                    <div style="flex: 1; text-align: right;">
                        <div class="text-muted small mb-1">Variant Lift</div>
                        @if($captureStats['lift_pct'] > 0)
                            <div class="fw-bold fs-4 text-success">+{{ $captureStats['lift_pct'] }}%</div>
                        @elseif($captureStats['lift_pct'] < 0)
                            <div class="fw-bold fs-4 text-danger">{{ $captureStats['lift_pct'] }}%</div>
                        @else
                            <div class="fw-bold fs-4 text-muted">0%</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- DECISION FLAGS                                                     --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@if(count($flags) > 0)
<div class="mb-4">
    <h6 class="mb-3 text-uppercase text-muted" style="letter-spacing: 1px; font-size: 0.75rem; font-weight: 700;">Decision Flags</h6>
    <div class="row g-3">
        @foreach($flags as $flag)
        <div class="col-md-4">
            <div class="alert alert-{{ $flag['type'] }} d-flex gap-3 mb-0 h-100 border-0" style="background: var(--bs-{{ $flag['type'] }}-bg-subtle);">
                <i class="bi {{ $flag['icon'] }} fs-4 text-{{ $flag['type'] }}"></i>
                <div>
                    <h6 class="alert-heading mb-1 text-{{ $flag['type'] }} fw-bold" style="font-size: 0.9rem;">{{ $flag['title'] }}</h6>
                    <p class="mb-0 text-dark small" style="opacity: 0.85;">{{ $flag['message'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- FUNNEL + TRAFFIC SOURCES                                           --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="funnel-container h-100">
            <h5 class="mb-4" style="font-weight: 600;">Conversion Funnel</h5>
            
            <div class="funnel-step">
                <div class="funnel-label">Sessions</div>
                <div class="funnel-bar-wrapper">
                    <div class="funnel-bar" style="width: 100%;">{{ number_format($funnel['visitors']['count']) }}</div>
                </div>
                <div class="funnel-pct">100%</div>
            </div>
            
            <div class="funnel-step">
                <div class="funnel-label">Product Views</div>
                <div class="funnel-bar-wrapper">
                    <div class="funnel-bar step-product" style="width: {{ $funnel['product']['pct'] }}%;">{{ number_format($funnel['product']['count']) }}</div>
                </div>
                <div class="funnel-pct">{{ $funnel['product']['pct'] }}%</div>
            </div>
            
            <div class="funnel-step">
                <div class="funnel-label">Add to Cart</div>
                <div class="funnel-bar-wrapper">
                    <div class="funnel-bar step-cart" style="width: {{ $funnel['cart']['pct'] }}%;">{{ number_format($funnel['cart']['count']) }}</div>
                </div>
                <div class="funnel-pct">{{ $funnel['cart']['pct'] }}%</div>
            </div>
            
            <div class="funnel-step">
                <div class="funnel-label">Checkout Starts</div>
                <div class="funnel-bar-wrapper">
                    <div class="funnel-bar step-checkout" style="width: {{ $funnel['checkout']['pct'] }}%;">{{ number_format($funnel['checkout']['count']) }}</div>
                </div>
                <div class="funnel-pct">{{ $funnel['checkout']['pct'] }}%</div>
            </div>
            
            <div class="funnel-step">
                <div class="funnel-label">Purchases</div>
                <div class="funnel-bar-wrapper">
                    <div class="funnel-bar step-purchase" style="width: {{ $funnel['purchase']['pct'] }}%;">{{ number_format($funnel['purchase']['count']) }}</div>
                </div>
                <div class="funnel-pct">{{ $funnel['purchase']['pct'] }}%</div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="table-card h-100">
            <div class="table-card-header d-flex justify-content-between align-items-center">
                <span>Traffic Sources</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Source/Medium</th>
                            <th class="text-end">Sessions</th>
                            <th class="text-end pe-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sources as $s)
                        <tr>
                            <td class="ps-3">{{ $s->source_name }}</td>
                            <td class="text-end">{{ number_format($s->sessions) }}</td>
                            <td class="text-end pe-3 text-success">₹{{ number_format($s->revenue, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3 text-muted">No data available</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- PRODUCT INTELLIGENCE + LIVE FEED + SEARCH                         --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="table-card h-100">
            <div class="table-card-header">Product Intelligence</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Product</th>
                            <th class="text-center">Views</th>
                            <th class="text-center">ATC Rate</th>
                            <th class="text-center">Conv. Rate</th>
                            <th class="text-end pe-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        @php
                            $atcRate = $p->total_views > 0 ? round(($p->total_atc / $p->total_views) * 100, 1) : 0;
                            $crRate = $p->total_views > 0 ? round(($p->total_purchases / $p->total_views) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('product.show', $p->product->slug ?? '') }}" target="_blank" class="text-decoration-none fw-medium text-dark">
                                    {{ Str::limit($p->product->name ?? 'Unknown', 40) }}
                                </a>
                            </td>
                            <td class="text-center">{{ number_format($p->total_views) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $atcRate > 5 ? 'success' : 'secondary' }}">{{ $atcRate }}%</span>
                            </td>
                            <td class="text-center fw-semibold text-primary">{{ $crRate }}%</td>
                            <td class="text-end pe-3 fw-bold text-success">₹{{ number_format($p->total_revenue, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No product data for this period. Run aggregation command if needed.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="table-card d-flex flex-column mb-4" style="max-height: 360px;">
            <div class="table-card-header d-flex justify-content-between align-items-center">
                <span>Live Event Feed</span>
                <span class="live-dot"></span>
            </div>
            <div class="flex-grow-1" style="overflow-y: auto;">
                @forelse($liveEvents as $e)
                @php
                    $srcBadge = '';
                    $sessionSource = '';
                    if ($e->session) {
                        $sessionSource = strtolower($e->session->source ?? '');
                    }
                    
                    // Build narrative
                    $narrative = match($e->event_name) {
                        'page_view' => 'Visited ' . (parse_url($e->page_url ?? '', PHP_URL_PATH) ?: '/'),
                        'product_view' => 'Viewed ' . ($e->product->name ?? 'a product'),
                        'add_to_cart' => '🛒 Added ' . ($e->product->name ?? 'item') . ' to cart',
                        'checkout_start' => '💳 Started checkout',
                        'purchase' => '✅ Purchased! ₹' . ($e->meta['revenue'] ?? '0'),
                        'search' => '🔍 Searched "' . ($e->meta['query'] ?? '...') . '"',
                        'scroll_25' => 'Scrolled 25%',
                        'scroll_50' => 'Scrolled 50%',
                        'scroll_75' => 'Scrolled 75%',
                        default => $e->event_name,
                    };
                    
                    $isSignal = in_array($e->event_name, ['add_to_cart', 'checkout_start', 'purchase', 'search']);
                @endphp
                <div class="feed-item {{ $isSignal ? 'bg-warning bg-opacity-10' : '' }}">
                    <span class="feed-time">{{ $e->created_at->diffForHumans(null, true, true) }}</span>
                    @if($sessionSource)
                        @php
                            $sBadge = match(true) {
                                str_contains($sessionSource, 'facebook') => 'source-facebook',
                                str_contains($sessionSource, 'instagram') => 'source-instagram',
                                str_contains($sessionSource, 'google') => 'source-google',
                                default => 'source-direct',
                            };
                        @endphp
                        <span class="source-badge {{ $sBadge }}" style="font-size: 0.6rem;">{{ ucfirst($sessionSource ?: 'Direct') }}</span>
                    @endif
                    <span class="text-truncate" style="flex: 1;">{{ $narrative }}</span>
                </div>
                @empty
                <div class="p-4 text-center text-muted">No recent events</div>
                @endforelse
            </div>
            <div class="p-2 border-top text-center bg-light">
                <a href="{{ route('admin.analytics.sessions') }}" class="btn btn-sm btn-outline-secondary w-100">Open Session Explorer</a>
            </div>
        </div>

        <div class="table-card d-flex flex-column">
            <div class="table-card-header d-flex justify-content-between align-items-center">
                <span>Search Intelligence</span>
                <i class="bi bi-search text-muted"></i>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Search Query</th>
                            <th class="text-end pe-3">Volume</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($search as $s)
                        <tr>
                            <td class="ps-3 d-flex align-items-center gap-2">
                                <span class="fw-medium text-dark">{{ $s['query'] }}</span>
                                @if($s['zero_results'])
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle" style="font-size:0.65rem;">0 Results</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">{{ number_format($s['count']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4 text-muted">No search data recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- GEOGRAPHY INTELLIGENCE                                             --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="table-card h-100">
            <div class="table-card-header">Top Cities ({{ $range == 'today' ? 'Today' : ($range == 'yesterday' ? 'Yesterday' : 'Last ' . str_replace('d', ' Days', $range)) }})</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">City</th>
                            <th class="text-center">Sessions</th>
                            <th class="text-center">Unique Users</th>
                            <th class="text-center">ATC</th>
                            <th class="text-center">Purchases</th>
                            <th class="text-end pe-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($geography['cities'] as $city)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                {{ $city->city }}
                                <span class="d-block text-muted small" style="font-size: 0.7rem;">{{ $city->region }}</span>
                            </td>
                            <td class="text-center">{{ number_format($city->sessions) }}</td>
                            <td class="text-center">{{ number_format($city->unique_visitors) }}</td>
                            <td class="text-center">{{ number_format($city->add_to_cart) }}</td>
                            <td class="text-center text-primary fw-semibold">{{ number_format($city->purchases) }}</td>
                            <td class="text-end pe-3 fw-bold text-success">₹{{ number_format($city->revenue, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No geographic data for this period. Ensure GeoIP is configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="table-card h-100">
            <div class="table-card-header d-flex justify-content-between align-items-center">
                <span>Top Regions / States</span>
                <i class="bi bi-geo-alt text-muted"></i>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Region</th>
                            <th class="text-center">Sessions</th>
                            <th class="text-end pe-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($geography['regions'] as $region)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $region->region }}</td>
                            <td class="text-center">{{ number_format($region->sessions) }}</td>
                            <td class="text-end pe-3 text-success">₹{{ number_format($region->revenue, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No region data recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
