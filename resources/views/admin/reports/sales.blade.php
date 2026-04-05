@extends('layouts.admin')
@section('title', 'Sales Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Sales Report</h1>
    
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
</div>

<div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Gross Revenue</h6>
                    <h2 class="mb-1">₹{{ number_format($grossRevenue) }}</h2>
                    <p class="mb-0 small">
                        @if($revenueTrend > 0)
                            <i class="bi bi-arrow-up-right-circle text-white"></i> +{{ $revenueTrend }}% vs prior period
                        @elseif($revenueTrend < 0)
                            <i class="bi bi-arrow-down-right-circle text-white"></i> {{ $revenueTrend }}% vs prior period
                        @else
                            <i class="bi bi-dash-circle text-white"></i> Flat vs prior period
                        @endif
                    </p>
                </div>
            </div>
        </div>
    <div class="col-md-3">
        <div class="card h-100 border-danger">
            <div class="card-body">
                <div class="text-muted small">Refunded Amount</div>
                <div class="h3 fw-bold text-danger mb-0">₹{{ number_format($refundAmount, 2) }}</div>
                <div class="text-muted small">{{ $refundValuePercent }}% of gross revenue</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Net Revenue</div>
                <div class="h3 fw-bold text-success mb-0">₹{{ number_format($netRevenue, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Avg Order Value (AOV)</div>
                <div class="h3 fw-bold mb-0">₹{{ number_format($aov, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Orders</h6>
                    <h2 class="mb-1">{{ number_format($totalOrders) }}</h2>
                    <p class="mb-0 small">
                        ~{{ $conversionRate }}% Conversion (Orders/Carts)
                    </p>
                </div>
            </div>
        </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Prepaid Orders</div>
                <div class="h3 fw-bold text-primary mb-0">{{ $prepaidOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">COD Orders</div>
                <div class="h3 fw-bold text-warning mb-0">{{ $codOrders }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-graph-up-arrow me-1"></i> Revenue Trend ({{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M') }})
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="250"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($chartData, 'date')) !!},
        datasets: [{
            label: 'Daily Revenue (₹)',
            data: {!! json_encode(array_column($chartData, 'revenue')) !!},
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.1)',
            borderWidth: 2,
            tension: 0.3,
            fill: true
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
</script>
@endpush
