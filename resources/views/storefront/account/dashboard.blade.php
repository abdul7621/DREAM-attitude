@extends('layouts.account')
@section('title', 'My Account')
@section('account-content')
<div class="sf-account-content">
    <h1 style="color:white;font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-grid" style="color:var(--color-gold);"></i>Dashboard
    </h1>

    {{-- Stat Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;margin-bottom:32px;">
        <div class="sf-account-card" style="text-align:center;padding:24px 16px;margin-bottom:0;">
            <span style="color:var(--color-gold);font-size:28px;font-weight:600;display:block;">{{ $totalOrders }}</span>
            <span style="color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-top:4px;display:block;">Total Orders</span>
        </div>
        <div class="sf-account-card" style="text-align:center;padding:24px 16px;margin-bottom:0;">
            <span style="color:var(--color-gold);font-size:28px;font-weight:600;display:block;">₹{{ number_format($totalSpent, 0) }}</span>
            <span style="color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-top:4px;display:block;">Total Spent</span>
        </div>
        <div class="sf-account-card" style="text-align:center;padding:24px 16px;margin-bottom:0;">
            <span style="color:var(--color-gold);font-size:28px;font-weight:600;display:block;">{{ $wishlistCount }}</span>
            <span style="color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-top:4px;display:block;">Wishlist Items</span>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="sf-account-card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--color-border);font-weight:600;color:white;font-size:14px;">Recent Orders</div>
        @if ($recentOrders->isEmpty())
            <div style="text-align:center;padding:48px 20px;color:var(--color-text-muted);">
                <i class="bi bi-bag" style="font-size:32px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
                No orders yet. <a href="{{ route('home') }}" style="text-decoration:none;color:var(--color-gold);font-weight:600;">Start shopping →</a>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <th style="padding:10px 20px;text-align:left;color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;font-weight:400;">Order #</th>
                            <th style="padding:10px 20px;text-align:left;color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;font-weight:400;">Date</th>
                            <th style="padding:10px 20px;text-align:left;color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;font-weight:400;">Total</th>
                            <th style="padding:10px 20px;text-align:left;color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;font-weight:400;">Status</th>
                            <th style="padding:10px 20px;text-align:right;color:var(--color-text-muted);font-size:11px;text-transform:uppercase;letter-spacing:0.5px;font-weight:400;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($recentOrders as $order)
                        <tr style="border-bottom:1px solid var(--color-border);transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.02)'" onmouseleave="this.style.background='transparent'">
                            <td style="padding:12px 20px;color:white;font-weight:600;font-size:13px;">{{ Str::limit($order->order_number, 16) }}</td>
                            <td style="padding:12px 20px;color:var(--color-text-muted);font-size:13px;">{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                            <td style="padding:12px 20px;color:white;font-size:13px;">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td style="padding:12px 20px;"><span class="sf-badge {{ strtolower($order->order_status) }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
                            <td style="padding:12px 20px;text-align:right;">
                                <a href="{{ route('account.orders.show', $order) }}" style="text-decoration:none;color:var(--color-gold);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px;text-align:right;border-top:1px solid var(--color-border);">
                <a href="{{ route('account.orders') }}" style="text-decoration:none;color:var(--color-gold);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:1px;">View All Orders →</a>
            </div>
        @endif
    </div>
</div>
@endsection
