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

    {{-- Loyalty Wallet Panel --}}
    <div class="sf-account-card sf-account-panel mb-4" style="border-color: rgba(201,168,76,0.3) !important; background: rgba(201,168,76,0.02);">
        <div class="sf-account-panel-header" style="background: rgba(201,168,76,0.05); color: var(--color-gold); font-weight: bold; border-bottom: 1px solid rgba(201,168,76,0.15);">
            <i class="bi bi-wallet2 me-2"></i> Loyalty Reward Wallet
        </div>
        <div class="p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <div style="font-size: 13px; color: var(--color-text-secondary);">Available Points Balance</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--color-gold); margin-top: 4px;">{{ number_format($loyaltyBalance, 0) }} pts</div>
                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 4px;">1 Point = ₹1 Store Discount. Points are earned automatically on order delivery.</div>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('account.loyalty.redeem') }}" method="post" class="border p-3 rounded bg-white">
                        @csrf
                        <div class="fw-semibold small mb-2 text-dark">Redeem Points for Discount Coupon</div>
                        <div class="input-group">
                            <input type="number" name="amount" class="form-control form-control-sm" placeholder="Min 10 points" min="10" max="{{ (int) $loyaltyBalance }}" required>
                            <button type="submit" class="btn btn-sm btn-primary" style="background: var(--color-gold); border-color: var(--color-gold); color: white;">Convert to Coupon</button>
                        </div>
                        <div class="form-text small" style="font-size: 10px; margin-top: 4px; color: var(--color-text-muted);">Coupon will be restricted to your account and valid for 90 days.</div>
                    </form>
                </div>
            </div>
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

    {{-- Recently Viewed --}}
    @if ($recentlyViewed->isNotEmpty())
    <div class="sf-account-card sf-account-panel mt-4" style="border-color: var(--color-border) !important;">
        <div class="sf-account-panel-header">Recently Viewed Products</div>
        <div class="p-3">
            <div class="row g-3">
                @foreach ($recentlyViewed as $prod)
                    @php
                        $var = $prod->variants->firstWhere('is_active', true) ?? $prod->variants->first();
                        $primary = $prod->primaryImage();
                    @endphp
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center h-100 bg-white d-flex flex-column justify-content-between" style="border-color: var(--color-border) !important;">
                            <a href="{{ route('product.show', $prod) }}" class="text-decoration-none text-dark d-block">
                                @if($primary)
                                    <img src="{{ asset('storage/' . $primary->path) }}" alt="{{ $prod->name }}" class="img-fluid rounded mb-2" style="height: 80px; object-fit: contain;">
                                @else
                                    <div class="bg-secondary-subtle rounded mb-2" style="height: 80px;"></div>
                                @endif
                                <div class="fw-semibold small text-truncate" title="{{ $prod->name }}" style="font-size: 12px; color: var(--color-text-primary);">{{ $prod->name }}</div>
                            </a>
                            <div class="mt-2 text-success fw-bold small">₹{{ number_format($var?->price_retail ?? 0) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endsection

