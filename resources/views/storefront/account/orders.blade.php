@extends('layouts.account')
@section('title', 'My Orders')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-receipt" style="color:var(--color-gold);"></i>My Orders
</h1>

@if ($orders->isEmpty())
    <div class="sf-account-card" style="text-align:center;padding:48px 20px;color:var(--color-text-muted);">
        <i class="bi bi-bag" style="font-size:32px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
        You haven't placed any orders yet.
        <a href="{{ route('home') }}" style="text-decoration:none;color:var(--color-gold);font-weight:600;">Start shopping →</a>
    </div>
@else
<div class="sf-account-card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Order #</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Date</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Items</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Total</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Status</th>
                    <th style="padding:12px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;"></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($orders as $order)
                <tr style="border-bottom:1px solid var(--color-border);transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.02)'" onmouseleave="this.style.background='transparent'">
                    <td style="padding:12px 20px;color:var(--color-text-primary);font-weight:600;font-size:13px;">{{ Str::limit($order->order_number, 16) }}</td>
                    <td style="padding:12px 20px;color:var(--color-text-muted);font-size:13px;">{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                    <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">{{ $order->order_items_count ?? '—' }}</td>
                    <td style="padding:12px 20px;color:var(--color-text-primary);font-size:13px;">₹{{ number_format($order->grand_total, 2) }}</td>
                    <td style="padding:12px 20px;"><span class="sf-badge {{ strtolower($order->order_status) }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
                    <td style="padding:12px 20px;text-align:right;">
                        <a href="{{ route('account.orders.show', $order) }}" style="text-decoration:none;color:var(--color-gold);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">View</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:16px;">{{ $orders->links() }}</div>
@endif
@endsection
