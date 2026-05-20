@extends('layouts.account')
@section('title', 'My Account')
@section('account-content')
    <h1 class="sf-account-title">
        <i class="bi bi-grid"></i>Dashboard
    </h1>

    {{-- Stat Cards --}}
    <div class="sf-account-stat-grid">
        <div class="sf-account-card sf-account-stat-card">
            <span class="sf-account-stat-val">{{ $totalOrders }}</span>
            <span class="sf-account-stat-label">Total Orders</span>
        </div>
        <div class="sf-account-card sf-account-stat-card">
            <span class="sf-account-stat-val">₹{{ number_format($totalSpent, 0) }}</span>
            <span class="sf-account-stat-label">Total Spent</span>
        </div>
        <div class="sf-account-card sf-account-stat-card">
            <span class="sf-account-stat-val">{{ $wishlistCount }}</span>
            <span class="sf-account-stat-label">Wishlist Items</span>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="sf-account-card sf-account-panel">
        <div class="sf-account-panel-header">Recent Orders</div>
        @if ($recentOrders->isEmpty())
            <div class="sf-account-empty">
                <i class="bi bi-bag sf-account-empty-icon"></i>
                No orders yet. <a href="{{ route('home') }}" class="sf-account-link-gold">Start shopping →</a>
            </div>
        @else
            <div class="sf-account-table-wrap">
                <table class="sf-account-table">
                    <thead>
                        <tr>
                            <th class="sf-account-th">Order #</th>
                            <th class="sf-account-th">Date</th>
                            <th class="sf-account-th">Total</th>
                            <th class="sf-account-th">Status</th>
                            <th class="sf-account-th-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recentOrders as $order)
                        <tr class="sf-account-tr">
                            <td class="sf-account-td-bold">{{ Str::limit($order->order_number, 16) }}</td>
                            <td class="sf-account-td-muted">{{ ($order->placed_at ?? $order->created_at)?->format('d M Y') ?? '—' }}</td>
                            <td class="sf-account-td">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td class="sf-account-td"><span class="sf-badge {{ strtolower($order->order_status) }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
                            <td class="sf-account-td-right">
                                <a href="{{ route('account.orders.show', $order) }}" class="sf-account-action-link">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="sf-account-panel-footer">
                <a href="{{ route('account.orders') }}" class="sf-account-action-link">View All Orders →</a>
            </div>
        @endif
    </div>
@endsection

