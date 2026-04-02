@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Today's Orders</div>
                <div class="h3 fw-bold">{{ $todayOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Today's Revenue</div>
                <div class="h3 fw-bold">₹{{ number_format($todayRevenue, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Revenue (Paid)</div>
                <div class="h3 fw-bold">₹{{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Pending Orders</div>
                <div class="h3 fw-bold text-warning">{{ $pendingOrders }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Recent Orders</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>#</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th>
                    </tr></thead>
                    <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>₹{{ number_format($order->grand_total, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ $order->order_status }}</span></td>
                            <td>{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold text-danger">Low Stock (≤ 5 units)</div>
            <ul class="list-group list-group-flush">
            @forelse ($lowStockVariants as $v)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $v->product->name }} — {{ $v->title }}</span>
                    <span class="badge bg-danger">{{ $v->stock_qty }}</span>
                </li>
            @empty
                <li class="list-group-item text-muted">All stocked up! 🎉</li>
            @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
