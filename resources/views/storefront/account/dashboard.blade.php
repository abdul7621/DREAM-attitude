@extends('layouts.account')
@section('title', 'My Account')
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-grid me-2"></i>Dashboard</h1>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ $totalOrders }}</div>
            <small class="text-muted">Total Orders</small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">₹{{ number_format($totalSpent, 0) }}</div>
            <small class="text-muted">Total Spent</small>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-danger">{{ $wishlistCount }}</div>
            <small class="text-muted">Wishlist Items</small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Recent Orders</div>
    @if ($recentOrders->isEmpty())
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-bag fs-1 d-block mb-2"></i>
            No orders yet. <a href="{{ route('home') }}" class="fw-semibold">Start shopping →</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th>
                </tr></thead>
                <tbody>
                @foreach ($recentOrders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->order_number }}</td>
                        <td>{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                        <td>₹{{ number_format($order->grand_total, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['color'] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
                        <td><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <a href="{{ route('account.orders') }}" class="btn btn-sm btn-outline-dark">View All Orders →</a>
        </div>
    @endif
</div>
@endsection
