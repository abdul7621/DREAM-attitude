@extends('layouts.account')
@section('title', 'My Account')
@section('account-content')
<h1 class="h4 fw-bold mb-4" style="color: white;"><i class="bi bi-grid me-2 text-gold"></i>Dashboard</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">
    <div class="sf-account-card text-center text-white p-4" style="margin-bottom: 0;">
        <div class="fs-2 fw-bold text-gold">{{ $totalOrders }}</div>
        <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Total Orders</small>
    </div>
    <div class="sf-account-card text-center text-white p-4" style="margin-bottom: 0;">
        <div class="fs-2 fw-bold text-gold">₹{{ number_format($totalSpent, 0) }}</div>
        <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Total Spent</small>
    </div>
    <div class="sf-account-card text-center text-white p-4" style="margin-bottom: 0;">
        <div class="fs-2 fw-bold text-gold">{{ $wishlistCount }}</div>
        <small class="text-muted text-uppercase" style="letter-spacing: 1px;">Wishlist Items</small>
    </div>
</div>

<div class="sf-account-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border); font-weight: 600; color: white;">Recent Orders</div>
    @if ($recentOrders->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-bag fs-1 d-block mb-3" style="color: var(--color-gold);"></i>
            No orders yet. <a href="{{ route('home') }}" class="fw-semibold text-gold" style="text-decoration:none;">Start shopping →</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.02); margin:0;">
                <thead><tr style="border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">
                    <th class="fw-normal px-4 py-3 border-0">Order #</th>
                    <th class="fw-normal px-4 py-3 border-0">Date</th>
                    <th class="fw-normal px-4 py-3 border-0">Total</th>
                    <th class="fw-normal px-4 py-3 border-0">Status</th>
                    <th class="fw-normal px-4 py-3 border-0"></th>
                </tr></thead>
                <tbody>
                @foreach ($recentOrders as $order)
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td class="fw-semibold px-4 py-3 border-0 align-middle text-white">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 border-0 align-middle text-muted">{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3 border-0 align-middle text-white">₹{{ number_format($order->grand_total, 2) }}</td>
                        <td class="px-4 py-3 border-0 align-middle"><span class="sf-badge {{ strtolower($order->order_status) }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
                        <td class="px-4 py-3 border-0 align-middle text-end"><a href="{{ route('account.orders.show', $order) }}" style="text-decoration:none; color:var(--color-gold); font-size:12px; font-weight:600; text-transform:uppercase;">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 20px; text-align: right; border-top: 1px solid var(--color-border);">
            <a href="{{ route('account.orders') }}" style="text-decoration:none; color:var(--color-text-secondary); font-size:12px; font-weight:600; text-transform:uppercase;">View All Orders →</a>
        </div>
    @endif
</div>
<style>
.text-gold { color: var(--color-gold) !important; }
</style>
@endsection
