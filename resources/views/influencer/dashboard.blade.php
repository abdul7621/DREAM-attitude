@extends('influencer.layout')
@section('title', 'Partner Dashboard — ' . $influencer->name)
@section('content')

{{-- Welcome & Referral Sharing Card --}}
<div class="inf-card mb-4" style="background: linear-gradient(135deg, #181818 0%, #0F0F0F 100%); border-color: var(--color-border-gold);">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">
                    <i class="bi bi-patch-check-fill me-1"></i> VERIFIED PARTNER
                </span>
                <span class="text-muted small">Commission Rate: <strong class="text-warning">{{ $influencer->commission_type === 'percent' ? $influencer->commission_value . '%' : '₹' . $influencer->commission_value }}</strong> on all sales</span>
            </div>
            <h2 class="h3 fw-bold text-white mb-1" style="font-family:'Playfair Display', serif;">
                Hello, {{ $influencer->name }}! ✨
            </h2>
            <p class="text-muted small mb-3">
                Share your exclusive coupon code or referral link with your audience. You will earn commission on every successful delivered order.
            </p>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                {{-- Promo Code Copy Badge --}}
                <div class="d-flex align-items-center bg-black border border-warning border-opacity-50 rounded-3 p-2 px-3">
                    <div>
                        <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.8px;">Your Coupon Code</div>
                        <div class="text-warning fw-bold font-monospace fs-5">{{ $stats['coupon_code'] }}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning ms-3 rounded-pill" id="btnCopyCode" onclick="copyToClipboard('{{ $stats['coupon_code'] }}', 'btnCopyCode')">
                        <i class="bi bi-copy me-1"></i> Copy Code
                    </button>
                </div>

                {{-- WhatsApp Share Action --}}
                @php
                    $waText = "Hey! Use my exclusive discount coupon code *" . $stats['coupon_code'] . "* to get " . $stats['discount_text'] . " on Dream Attitude luxury products: " . $stats['referral_url'];
                    $waUrl = "https://wa.me/?text=" . urlencode($waText);
                @endphp
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 rounded-3 px-3 py-2 fw-semibold">
                    <i class="bi bi-whatsapp"></i> Share on WhatsApp
                </a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bg-black bg-opacity-60 p-3 rounded-3 border border-secondary border-opacity-25">
                <label class="text-muted small fw-semibold mb-1 d-block"><i class="bi bi-link-45deg text-warning"></i> Your Direct Referral Link</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control text-white bg-dark border-secondary font-monospace" value="{{ $stats['referral_url'] }}" readonly id="refLinkInput">
                    <button class="btn btn-outline-warning" type="button" id="btnCopyLink" onclick="copyToClipboard('{{ $stats['referral_url'] }}', 'btnCopyLink')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <div class="form-text text-muted small mt-2" style="font-size: 0.75rem;">
                    When users click your link and checkout, their discount and your commission apply automatically.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4 Core KPI Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Referred Orders</div>
            <div class="inf-stat-val">{{ $stats['total_orders'] }}</div>
            <div class="text-muted small">Successful customer checkouts</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Total Sales (GMV)</div>
            <div class="inf-stat-val text-primary" style="color: #60a5fa !important;">₹{{ number_format($stats['total_sales'], 2) }}</div>
            <div class="text-muted small">Gross sales value generated</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Total Commission</div>
            <div class="inf-stat-val text-success" style="color: #4ade80 !important;">₹{{ number_format($stats['total_earned'], 2) }}</div>
            <div class="text-muted small">Lifetime earnings credited</div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="inf-stat-card">
            <div class="inf-stat-label">Available Payout</div>
            <div class="inf-stat-val text-warning" style="color: #facc15 !important;">₹{{ number_format($stats['pending_payout'], 2) }}</div>
            <div class="text-muted small">Paid so far: ₹{{ number_format($stats['paid_earnings'], 2) }}</div>
        </div>
    </div>
</div>

{{-- Recent Referral Orders Section --}}
<div class="inf-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-receipt text-warning me-2"></i>Recent Referral Orders</h5>
            <span class="text-muted small">Live commissions tracked from your audience purchases.</span>
        </div>
        <a href="{{ route('influencer.orders') }}" class="inf-btn-outline btn-sm">
            View All Orders <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="inf-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order Reference</th>
                    <th class="text-end">Sale Amount</th>
                    <th class="text-center">Rate</th>
                    <th class="text-end">Your Commission</th>
                    <th class="text-center">Order Status</th>
                    <th class="text-center">Commission Payout</th>
                </tr>
            </thead>
            <tbody>
            @forelse($recentCommissions as $comm)
                <tr>
                    <td class="text-muted small">{{ $comm->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        {{-- Mask order number for customer privacy --}}
                        <span class="fw-semibold font-monospace text-warning">
                            @if($comm->order)
                                ORD-***{{ substr($comm->order->order_number, -6) }}
                            @else
                                ORD-***{{ $comm->order_id }}
                            @endif
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
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 px-2 py-1">Placed / Processing</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @if($comm->status === 'paid')
                            <span class="inf-badge-paid"><i class="bi bi-check-circle me-1"></i>Paid</span>
                        @elseif($comm->status === 'approved')
                            <span class="inf-badge-approved">Approved (Payout Ready)</span>
                        @elseif($comm->status === 'pending')
                            <span class="inf-badge-pending">Pending Delivery</span>
                        @elseif($comm->status === 'cancelled')
                            <span class="inf-badge-cancelled">Cancelled</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-bag-x" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        <p class="mt-2 mb-0">No referral orders yet. Share your coupon <strong>{{ $stats['coupon_code'] }}</strong> to start earning!</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
