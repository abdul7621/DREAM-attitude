@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Dashboard</h1>
    <span class="small text-muted">{{ now()->format('l, d M Y') }}</span>
</div>

{{-- ── KPI Cards ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Today's Orders</div>
                <div class="h3 fw-bold mb-0">{{ $todayOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Today's Revenue</div>
                <div class="h3 fw-bold text-success mb-0">₹{{ number_format($todayRevenue, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Revenue</div>
                <div class="h3 fw-bold mb-0">₹{{ number_format($totalRevenue, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Avg Order Value</div>
                <div class="h3 fw-bold mb-0">₹{{ number_format($aov, 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Actionable Insights (Decision Cards) ─────────────────── --}}
<h5 class="mb-3">Actionable Insights</h5>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <a href="{{ route('admin.orders.index', ['status' => 'placed']) }}" class="text-decoration-none">
            <div class="card h-100 border-primary bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <div class="h3 fw-bold text-primary mb-1">{{ $pendingOrders }}</div>
                    <div class="text-primary small fw-semibold">Pending Orders</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card h-100 {{ count($lowStockVariants) > 0 ? 'border-warning bg-warning bg-opacity-10' : '' }}">
            <div class="card-body text-center">
                <div class="h3 fw-bold {{ count($lowStockVariants) > 0 ? 'text-warning text-dark' : 'text-muted' }} mb-1">{{ count($lowStockVariants) }}</div>
                <div class="{{ count($lowStockVariants) > 0 ? 'text-warning text-dark' : 'text-muted' }} small fw-semibold">Low Stock Items</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.orders.index', ['payment' => 'cod']) }}" class="text-decoration-none">
            <div class="card h-100 {{ $highRiskCod > 0 ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                <div class="card-body text-center">
                    <div class="h3 fw-bold {{ $highRiskCod > 0 ? 'text-danger' : 'text-muted' }} mb-1">{{ $highRiskCod }}</div>
                    <div class="{{ $highRiskCod > 0 ? 'text-danger' : 'text-muted' }} small fw-semibold">High Risk COD (>₹5k)</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-xl-2">
        <a href="{{ route('admin.reviews.index') }}" class="text-decoration-none">
            <div class="card h-100 {{ $pendingReviewsCount > 0 ? 'border-info bg-info bg-opacity-10' : '' }}">
                <div class="card-body text-center">
                    <div class="h3 fw-bold {{ $pendingReviewsCount > 0 ? 'text-info text-dark' : 'text-muted' }} mb-1">{{ $pendingReviewsCount }}</div>
                    <div class="{{ $pendingReviewsCount > 0 ? 'text-info text-dark' : 'text-muted' }} small fw-semibold">Pending Reviews</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('admin.returns.index') }}" class="text-decoration-none">
            <div class="card h-100 {{ $pendingReturns > 0 ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                <div class="card-body text-center">
                    <div class="h3 fw-bold {{ $pendingReturns > 0 ? 'text-danger' : 'text-muted' }} mb-1">{{ $pendingReturns }}</div>
                    <div class="{{ $pendingReturns > 0 ? 'text-danger' : 'text-muted' }} small fw-semibold">Pending Returns</div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ── Charts Row ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-graph-up"></i> Revenue — Last 7 Days
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart"></i> COD vs Prepaid
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if ($codOrders + $prepaidOrders > 0)
                    <canvas id="codChart" height="200"></canvas>
                @else
                    <p class="text-muted small">No orders yet</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Top Products + Recent Orders ────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-trophy"></i> Top 5 Products
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th class="text-center">Sold</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                    @forelse ($topProducts as $tp)
                        <tr>
                            <td class="small">{{ $tp->product_name_snapshot }}</td>
                            <td class="text-center">{{ $tp->total_qty }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format($tp->total_revenue, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No sales data yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1"></i> Recent Orders</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Order</th><th>Customer</th><th class="text-end">Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-semibold">{{ $order->order_number }}</a></td>
                            <td class="small">{{ $order->customer_name }}</td>
                            <td class="text-end">₹{{ number_format($order->grand_total, 0) }}</td>
                            <td><span class="badge bg-{{ $order->statusColor() }}" style="font-size:.65rem;">{{ $order->statusLabel() }}</span></td>
                            <td class="small text-muted">{{ $order->placed_at?->format('d M') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Low Stock + Recent Reviews ──────────────────────────── --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header text-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i> Low Stock (≤ 5 units)
            </div>
            <ul class="list-group list-group-flush">
            @forelse ($lowStockVariants as $v)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="small">{{ $v->product->name }} — {{ $v->title }}</span>
                    <span class="badge bg-danger">{{ $v->stock_qty }}</span>
                </li>
            @empty
                <li class="list-group-item text-muted small">All stocked up! 🎉</li>
            @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-star-half me-1"></i> Recent Reviews</span>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">Manage</a>
            </div>
            <ul class="list-group list-group-flush">
            @forelse ($recentReviews as $review)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-warning" style="font-size:.8rem;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="small fw-semibold">{{ $review->reviewer_name }}</span>
                            <span class="small text-muted">on {{ $review->product?->name ?? 'Unknown' }}</span>
                        </div>
                        <span class="badge bg-{{ $review->is_approved ? 'success' : 'warning text-dark' }}" style="font-size:.6rem;">
                            {{ $review->is_approved ? 'Approved' : 'Pending' }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-muted small">No reviews yet.</li>
            @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($revenueChart)->pluck('date')) !!},
        datasets: [{
            label: 'Revenue (₹)',
            data: {!! json_encode(collect($revenueChart)->pluck('revenue')) !!},
            backgroundColor: 'rgba(37,99,235,.2)',
            borderColor: 'rgba(37,99,235,1)',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString('en-IN') } }
        }
    }
});

// COD vs Prepaid Pie
@if ($codOrders + $prepaidOrders > 0)
new Chart(document.getElementById('codChart'), {
    type: 'doughnut',
    data: {
        labels: ['COD', 'Prepaid'],
        datasets: [{
            data: [{{ $codOrders }}, {{ $prepaidOrders }}],
            backgroundColor: ['#f59e0b', '#2563eb'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } }
    }
});
@endif
</script>
@endpush
