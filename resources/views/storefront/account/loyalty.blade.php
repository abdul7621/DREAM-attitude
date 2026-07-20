@extends('layouts.account')
@section('title', 'Loyalty Wallet')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-wallet2" style="color:var(--color-gold);"></i>Loyalty Reward Wallet
</h1>

<div style="display:grid;gap:24px;grid-template-columns:1fr;">

    {{-- Stats and Redemption form --}}
    <div class="sf-account-card">
        <div style="font-weight:600;color:var(--color-text-primary);font-size:14px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--color-border);">Wallet Summary</div>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:24px;align-items:start;">
            <div>
                <div style="font-size: 13px; color: var(--color-text-secondary);">Available Points Balance</div>
                <div style="font-size: 36px; font-weight: 700; color: var(--color-gold); margin-top: 8px;">{{ number_format($loyaltyBalance, 0) }} pts</div>
                <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 8px; line-height: 1.5;">
                    * 1 Point = ₹1 Store Discount. Points are earned automatically on order delivery.
                </div>
            </div>
            
            <div style="background:var(--color-bg-elevated); border:1px solid var(--color-border); border-radius:var(--radius-sm); padding:20px;">
                <div style="font-weight:600; color:var(--color-text-primary); font-size:13px; margin-bottom:12px;">Convert Points to Coupon</div>
                <form action="{{ route('account.loyalty.redeem') }}" method="post">
                    @csrf
                    <div style="margin-bottom:12px;">
                        <label class="sf-label">Redemption Amount (Min 10 pts)</label>
                        <input type="number" name="amount" class="sf-input" placeholder="Enter points to convert..." min="10" max="{{ (int) $loyaltyBalance }}" required style="background: #fff;">
                    </div>
                    <button type="submit" class="sf-btn-primary" style="width:100%; height:40px; font-size:12px;">Redeem Now</button>
                </form>
                <div style="font-size:10px; color:var(--color-text-muted); margin-top:8px; text-align:center;">
                    Generated coupon is restricted to your account and valid for 90 days.
                </div>
            </div>
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
