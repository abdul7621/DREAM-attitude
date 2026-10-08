@extends('influencer.layout')
@section('title', 'Payouts & Earnings — ' . $influencer->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 fw-bold text-white mb-1"><i class="bi bi-wallet2 text-warning me-2"></i>Payouts & Financial History</h1>
        <p class="text-muted small mb-0">Track your settled payouts, transaction reference numbers, and payment details.</p>
    </div>
    <button type="button" class="inf-btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#payoutSettingsModal">
        <i class="bi bi-pencil-square me-1"></i> Edit Bank / UPI
    </button>
</div>

{{-- Payout Balances & Bank Info Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Total Commission Paid</div>
            <div class="inf-stat-val text-success" style="color: #4ade80 !important;">₹{{ number_format($influencer->paid_earnings, 2) }}</div>
            <div class="text-muted small">Transferred to your bank/UPI account</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Pending / Unpaid Balance</div>
            <div class="inf-stat-val text-warning" style="color: #facc15 !important;">₹{{ number_format($influencer->pending_payout, 2) }}</div>
            <div class="text-muted small">Will be cleared in next payout cycle</div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="inf-card p-3 h-100 d-flex flex-column justify-content-center">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Configured Payout Destination</div>
            @if($influencer->upi_id)
                <div class="text-warning fw-bold font-monospace"><i class="bi bi-qr-code me-1"></i> {{ $influencer->upi_id }}</div>
            @elseif(!empty($influencer->bank_details['account_no']))
                <div class="text-white fw-semibold">
                    <i class="bi bi-bank me-1"></i> A/C: {{ $influencer->bank_details['account_no'] }} ({{ $influencer->bank_details['ifsc'] ?? '' }})
                </div>
            @else
                <div class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i> No bank/UPI details added yet.</div>
            @endif
        </div>
    </div>
</div>

{{-- Paid Transactions Statement --}}
<div class="inf-card">
    <h5 class="fw-bold mb-3 text-white"><i class="bi bi-clock-history text-warning me-2"></i>Settled Payouts Statement</h5>

    <div class="table-responsive">
        <table class="inf-table">
            <thead>
                <tr>
                    <th>Payout Date</th>
                    <th>Order Reference</th>
                    <th class="text-end">Order Sale</th>
                    <th class="text-end">Paid Amount</th>
                    <th>Bank UTR / Transaction Reference</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($payouts as $p)
                <tr>
                    <td class="text-muted small">{{ $p->paid_at ? $p->paid_at->format('d M Y, h:i A') : '—' }}</td>
                    <td>
                        <span class="font-monospace text-warning">
                            @if($p->order)
                                ORD-***{{ substr($p->order->order_number, -6) }}
                            @else
                                ORD-***{{ $p->order_id }}
                            @endif
                        </span>
                    </td>
                    <td class="text-end fw-semibold">₹{{ number_format($p->order_subtotal, 2) }}</td>
                    <td class="text-end fw-bold text-success" style="color: #4ade80 !important;">₹{{ number_format($p->commission_amount, 2) }}</td>
                    <td>
                        <span class="font-monospace text-white small bg-black border border-secondary border-opacity-50 px-2 py-1 rounded">
                            {{ $p->payout_reference ?: 'DIRECT TRANSFER' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="inf-badge-paid"><i class="bi bi-check-circle-fill me-1"></i>Settled</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-wallet2" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No paid payout transactions recorded yet.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($payouts->hasPages())
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
            {{ $payouts->links() }}
        </div>
    @endif
</div>

@endsection
