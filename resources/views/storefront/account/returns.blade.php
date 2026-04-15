@extends('layouts.account')
@section('title', 'My Returns')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-arrow-return-left" style="color:var(--color-gold);"></i>My Return Requests
</h1>

@if ($returns->isEmpty())
    <div class="sf-account-card" style="text-align:center;padding:48px 20px;color:var(--color-text-muted);">
        <i class="bi bi-arrow-return-left" style="font-size:32px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
        No return requests yet.
    </div>
@else
<div class="sf-account-card" style="padding:0;overflow:hidden;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Return #</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Order</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Status</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Resolution</th>
                    <th style="padding:12px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Requested</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($returns as $r)
                <tr style="border-bottom:1px solid var(--color-border);">
                    <td style="padding:12px 20px;color:var(--color-text-primary);font-weight:600;font-size:13px;">#{{ $r->id }}</td>
                    <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">{{ $r->order->order_number }}</td>
                    <td style="padding:12px 20px;">
                        @php
                            $statusClass = match($r->status) {
                                'requested' => 'processing',
                                'approved' => 'shipped',
                                'received' => 'shipped',
                                'closed' => 'delivered',
                                default => ''
                            };
                        @endphp
                        <span class="sf-badge {{ $statusClass }}">{{ $r->status }}</span>
                    </td>
                    <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">{{ $r->resolution ?? '—' }}</td>
                    <td style="padding:12px 20px;color:var(--color-text-muted);font-size:13px;">{{ $r->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div style="margin-top:16px;">{{ $returns->links('vendor.pagination.storefront') }}</div>
@endif
@endsection
