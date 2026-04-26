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
    
    .live-indicator { display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: #b91c1c; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .live-dot { width: 8px; height: 8px; background: #dc2626; border-radius: 50%; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); } }
    
    .feed-item { padding: 12px 20px; border-bottom: 1px solid #f3f4f6; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center; }
    .feed-item:last-child { border-bottom: none; }
    .feed-time { color: #9ca3af; font-size: 0.75rem; width: 60px; }
    .feed-event { font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; color: #4b5563; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1" style="font-weight: 700; color: #111827;">Decision Engine</h1>
        <p class="text-muted mb-0">Storefront Intelligence & Traffic Analytics</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="live-indicator">
            <div class="live-dot"></div> {{ $liveCount }} Active Visitors
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="range" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto; min-width: 140px;">
                <option value="today" {{ $range == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $range == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="7d" {{ $range == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="14d" {{ $range == '14d' ? 'selected' : '' }}>Last 14 Days</option>
                <option value="30d" {{ $range == '30d' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </form>
    </div>
</div>

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
        <div class="kpi-card">
            <div class="kpi-title">Avg Session</div>
            <div class="kpi-value">{{ $overview['avg_duration_formatted'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-title">Conversion Rate</div>
            <div class="kpi-value text-success">{{ $overview['conversion_rate'] }}%</div>
            <div class="text-muted small mt-1">₹{{ number_format($overview['revenue'], 0) }} Revenue</div>
        </div>
    </div>
</div>

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
        <div class="table-card h-100 d-flex flex-column">
            <div class="table-card-header">Live Event Feed</div>
            <div class="flex-grow-1" style="overflow-y: auto; max-height: 400px;">
                @forelse($liveEvents as $e)
                <div class="feed-item">
                    <div class="d-flex align-items-center gap-2" style="flex: 1; overflow: hidden;">
                        <span class="feed-time">{{ $e->created_at->diffForHumans(null, true, true) }}</span>
                        <span class="feed-event">{{ $e->event_name }}</span>
                        @if($e->product)
                            <span class="text-truncate text-muted small">{{ $e->product->name }}</span>
                        @elseif($e->page_url)
                            <span class="text-truncate text-muted small" title="{{ $e->page_url }}">{{ parse_url($e->page_url, PHP_URL_PATH) ?? '/' }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">No recent events</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
