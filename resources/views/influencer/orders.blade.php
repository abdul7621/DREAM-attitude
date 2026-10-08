@extends('influencer.layout')
@section('title', 'Referral Orders — ' . $influencer->name)
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 fw-bold text-white mb-1"><i class="bi bi-bag-check text-warning me-2"></i>Referral Orders History</h1>
        <p class="text-muted small mb-0">Complete record of customer purchases made using your coupon code.</p>
    </div>
    <a href="{{ route('influencer.dashboard') }}" class="inf-btn-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="inf-card">
    <div class="table-responsive">
        <table class="inf-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Order Reference</th>
                    <th>Coupon Used</th>
                    <th class="text-end">Order Subtotal</th>
                    <th class="text-center">Rate</th>
                    <th class="text-end">Commission Earned</th>
                    <th class="text-center">Order Status</th>
                    <th class="text-center">Payout Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($commissions as $comm)
                <tr>
                    <td class="text-muted small">{{ $comm->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        <span class="fw-semibold font-monospace text-warning">
                            @if($comm->order)
                                ORD-***{{ substr($comm->order->order_number, -6) }}
                            @else
                                ORD-***{{ $comm->order_id }}
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-black border border-warning border-opacity-50 text-warning px-2 py-1 font-monospace">
                            {{ $comm->coupon_code }}
                        </span>
                    </td>
                    <td class="text-end fw-semibold">₹{{ number_format($comm->order_subtotal, 2) }}</td>
                    <td class="text-center text-muted small">{{ $comm->commission_rate }}%</td>
                    <td class="text-end fw-bold text-success" style="color: #4ade80 !important;">₹{{ number_format($comm->commission_amount, 2) }}</td>
                    <td class="text-center">
                        @if($comm->order)
                            @php $st = strtolower($comm->order->order_status); @endphp
                            @if($st === 'delivered')
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 px-2 py-1">Delivered</span>
                            @elseif($st === 'shipped')
                                <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 px-2 py-1">Shipped</span>
                            @elseif(in_array($st, ['cancelled', 'refunded']))
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 px-2 py-1">Cancelled</span>
                            @else
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 px-2 py-1">Processing</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @if($comm->status === 'paid')
                            <span class="inf-badge-paid"><i class="bi bi-check-circle me-1"></i>Paid</span>
                        @elseif($comm->status === 'approved')
                            <span class="inf-badge-approved">Approved</span>
                        @elseif($comm->status === 'pending')
                            <span class="inf-badge-pending">Pending</span>
                        @elseif($comm->status === 'cancelled')
                            <span class="inf-badge-cancelled">Cancelled</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-bag-x" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No referral orders found yet.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($commissions->hasPages())
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
            {{ $commissions->links() }}
        </div>
    @endif
</div>

@endsection
