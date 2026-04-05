@extends('layouts.admin')
@section('title', 'Coupon Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Coupon Report</h1>
</div>

<div class="card">
    <div class="card-header fw-semibold">
        <i class="bi bi-tags me-1"></i> Coupon Usage & Performance
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr>
                <th>Coupon Code</th>
                <th>Type / Value</th>
                <th class="text-center">Times Used</th>
                <th class="text-end">Total Discount Given</th>
                <th class="text-end">Revenue Generated</th>
            </tr></thead>
            <tbody>
            @forelse ($coupons as $coupon)
                <tr>
                    <td class="fw-bold">{{ $coupon->code }}</td>
                    <td class="small">{{ $coupon->type === 'percent' ? $coupon->value . '%' : '₹' . $coupon->value }} OFF</td>
                    <td class="text-center fw-semibold">{{ $coupon->usage_count }}</td>
                    <td class="text-end text-danger fw-semibold">−₹{{ number_format($coupon->total_discount_given ?? 0, 2) }}</td>
                    <td class="text-end text-success fw-bold">₹{{ number_format($coupon->revenue_generated ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No coupons created or used yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($coupons->hasPages())
        <div class="card-footer py-2">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection
