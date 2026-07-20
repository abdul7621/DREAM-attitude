@extends('layouts.account')
@section('title', 'Loyalty Wallet')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-wallet2" style="color:var(--color-gold);"></i>Loyalty Reward Wallet
</h1>

<div style="display:grid;gap:24px;grid-template-columns:1fr;">

    {{-- Stats and VIP Tier Card --}}
    @php
        $totalSpend = (float) \App\Models\Order::where('user_id', auth()->id())
            ->where('order_status', 'delivered')
            ->sum('grand_total');

        $tierName = 'Bronze Beauty Member';
        $tierColor = '#CD7F32';
        $tierIcon = 'bi-award-fill';

        if ($totalSpend > 25000) {
            $tierName = 'Platinum Elite Member';
            $tierColor = '#E5E4E2';
            $tierIcon = 'bi-gem';
        } elseif ($totalSpend > 10000) {
            $tierName = 'Gold Luxe Member';
            $tierColor = '#C9A84C';
            $tierIcon = 'bi-stars';
        } elseif ($totalSpend > 2500) {
            $tierName = 'Silver Glow Member';
            $tierColor = '#C0C0C0';
            $tierIcon = 'bi-shield-shaded';
        }
    @endphp
    <div class="sf-account-card" style="background: linear-gradient(135deg, #111827 0%, #1F2937 100%); border: none; padding: 28px; border-radius: 16px; color: #FFFFFF; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.1);padding:4px 12px;border-radius:20px;margin-bottom:12px;font-size:11px;font-weight:700;letter-spacing:0.5px;color:{{ $tierColor }};">
                    <i class="bi {{ $tierIcon }}"></i> {{ $tierName }}
                </div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.75; font-weight: 700; color: #E5E7EB; display: block; margin-bottom: 6px;">Available Balance</span>
                <span style="font-size: 38px; font-weight: 800; color: var(--color-gold); line-height: 1;">₹{{ number_format($loyaltyBalance, 2) }}</span>
                <span style="font-size: 12px; opacity: 0.85; display: block; margin-top: 8px; color: #F3F4F6;">Get <strong>5% flat cashback</strong> reward points automatically on the grand total of every delivered order. 1 Point = ₹1.</span>
            </div>
            <i class="bi bi-gem" style="font-size: 40px; color: var(--color-gold); opacity: 0.9;"></i>
        </div>
    </div>

    {{-- Transactions Ledger History --}}
    <div class="sf-account-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--color-border);color:var(--color-text-primary);font-weight:600;font-size:14px;">Transaction History</div>
        @if ($ledger->isEmpty())
            <div style="text-align:center;padding:40px;color:var(--color-text-muted);font-size:13px;">
                <i class="bi bi-journal-text" style="font-size:24px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
                No transactions recorded yet.
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Date</th>
                            <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Type</th>
                            <th style="padding:10px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Amount</th>
                            <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($ledger as $log)
                        <tr style="border-bottom:1px solid var(--color-border);">
                            <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td style="padding:12px 20px;font-size:13px;">
                                @if($log->amount > 0)
                                    <span style="color:#25d366;font-weight:600;">Earned</span>
                                @else
                                    <span style="color:var(--color-error);font-weight:600;">Redeemed</span>
                                @endif
                            </td>
                            <td style="padding:12px 20px;text-align:right;font-size:13px;font-weight:600;color:{{ $log->amount > 0 ? '#25d366' : 'var(--color-error)' }};">
                                {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount, 0) }}
                            </td>
                            <td style="padding:12px 20px;color:var(--color-text-primary);font-size:13px;">{{ $log->note }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:12px 20px;">{{ $ledger->links() }}</div>
        @endif
    </div>

</div>
@endsection
