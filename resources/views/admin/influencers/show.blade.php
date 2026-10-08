@extends('layouts.admin')
@section('title', 'Influencer Performance — ' . $influencer->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.influencers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h4 mb-0">{{ $influencer->name }}</h1>
            <div class="text-muted small">
                {{ $influencer->user?->email }}
                @if($influencer->phone) · <i class="bi bi-telephone"></i> {{ $influencer->phone }} @endif
                @if($influencer->instagram_handle) · <span class="text-primary"><i class="bi bi-instagram"></i> {{ '@' . $influencer->instagram_handle }}</span> @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.influencers.edit', $influencer) }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Partner
        </a>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#settlePayoutModal">
            <i class="bi bi-cash-coin me-1"></i> Settle Payout
        </button>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Assigned Coupon</div>
                <div class="h4 fw-bold text-dark font-monospace mb-1">
                    <i class="bi bi-tag-fill text-warning me-1"></i>{{ $influencer->coupon?->code ?? 'None' }}
                </div>
                <div class="small text-muted">
                    Commission: <span class="fw-semibold text-primary">{{ $influencer->commission_type === 'percent' ? $influencer->commission_value . '%' : '₹' . $influencer->commission_value }}</span> / order
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Total Sales (GMV)</div>
                <div class="h3 fw-bold text-primary mb-1">₹{{ number_format($influencer->total_sales, 2) }}</div>
                <div class="small text-muted">{{ $influencer->total_orders_count }} total orders referred</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Lifetime Commission</div>
                <div class="h3 fw-bold text-success mb-1">₹{{ number_format($influencer->total_earned, 2) }}</div>
                <div class="small text-muted">Paid: ₹{{ number_format($influencer->paid_earnings, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Unpaid Balance</div>
                <div class="h3 fw-bold text-danger mb-1">₹{{ number_format($influencer->pending_payout, 2) }}</div>
                <div class="small text-muted">UPI: {{ $influencer->upi_id ?: 'Not provided' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Referral Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-bag-check me-2"></i>Referral Orders & Commission Breakdown</h6>
        <span class="badge bg-light text-dark border">{{ $commissions->total() }} Records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Order Number</th>
                    <th class="text-end">Order Subtotal</th>
                    <th class="text-center">Rate</th>
                    <th class="text-end">Commission</th>
                    <th class="text-center">Order Status</th>
                    <th class="text-center">Payout Status</th>
                    <th>Payout Reference</th>
                </tr>
            </thead>
            <tbody>
            @forelse($commissions as $comm)
                <tr>
                    <td class="small text-muted">{{ $comm->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        @if($comm->order)
                            <a href="{{ route('admin.orders.show', $comm->order) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $comm->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted">ORD #{{ $comm->order_id }}</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">₹{{ number_format($comm->order_subtotal, 2) }}</td>
                    <td class="text-center small text-muted">{{ $comm->commission_rate }}%</td>
                    <td class="text-end fw-bold text-success">₹{{ number_format($comm->commission_amount, 2) }}</td>
                    <td class="text-center">
                        @if($comm->order)
                            <span class="badge bg-{{ \App\Models\Order::STATUS_LABELS[$comm->order->order_status]['color'] ?? 'secondary' }}-subtle text-{{ \App\Models\Order::STATUS_LABELS[$comm->order->order_status]['color'] ?? 'secondary' }}">
                                {{ ucfirst($comm->order->order_status) }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @if($comm->status === 'paid')
                            <span class="badge bg-success text-white"><i class="bi bi-check-circle me-1"></i>Paid</span>
                        @elseif($comm->status === 'approved')
                            <span class="badge bg-primary-subtle text-primary">Approved</span>
                        @elseif($comm->status === 'pending')
                            <span class="badge bg-warning-subtle text-warning text-dark">Pending Delivery</span>
                        @elseif($comm->status === 'cancelled')
                            <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                        @endif
                    </td>
                    <td class="small text-muted font-monospace">
                        {{ $comm->payout_reference ?: ($comm->status === 'paid' ? 'Paid on ' . $comm->paid_at?->format('d M') : '—') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No referral orders recorded for this influencer yet.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($commissions->hasPages())
        <div class="card-footer py-2">
            {{ $commissions->links() }}
        </div>
    @endif
</div>

{{-- Settle Payout Modal --}}
<div class="modal fade" id="settlePayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.influencers.payout.settle', $influencer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Settle Commission Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $unpaidCommissions = $influencer->commissions()->whereIn('status', ['pending', 'approved'])->get();
                        $unpaidTotal = $unpaidCommissions->sum('commission_amount');
                    @endphp

                    @if($unpaidCommissions->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i> There are no pending or approved commissions to settle right now.
                        </div>
                    @else
                        <div class="p-3 bg-light rounded mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Influencer:</span>
                                <span class="fw-bold">{{ $influencer->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">UPI ID:</span>
                                <span class="fw-semibold text-primary font-monospace">{{ $influencer->upi_id ?: 'Not provided' }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top">
                                <span class="fw-bold">Total Unpaid Amount:</span>
                                <span class="fw-bold text-success fs-5">₹{{ number_format($unpaidTotal, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bank UTR / Transaction Reference Number</label>
                            <input type="text" name="payout_reference" class="form-control" placeholder="e.g. UTR1234567890 / IMPS / GPay Ref" required>
                            <div class="form-text">Enter the bank/UPI transfer reference for the influencer's records.</div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="selectAllComm" checked disabled>
                            <label class="form-check-label small" for="selectAllComm">
                                Settling all <strong>{{ $unpaidCommissions->count() }}</strong> eligible pending orders (₹{{ number_format($unpaidTotal, 2) }})
                            </label>
                            @foreach($unpaidCommissions as $uc)
                                <input type="hidden" name="commission_ids[]" value="{{ $uc->id }}">
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if($unpaidCommissions->isNotEmpty())
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Confirm & Mark Paid
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
