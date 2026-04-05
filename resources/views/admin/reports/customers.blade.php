@extends('layouts.admin')
@section('title', 'Customer Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Customer Report</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Total Customers</div>
                <div class="h3 fw-bold mb-0">{{ $totalCustomers }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Repeat Customers (>1 order)</div>
                <div class="h3 fw-bold text-success mb-0">{{ $repeatCustomers }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">New Customers (1 order)</div>
                <div class="h3 fw-bold text-primary mb-0">{{ $newCustomers }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── Top Customers by LTV ──────────────────────────────── --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-star text-warning me-1"></i> Top Customers by LTV (Lifetime Value)
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Customer</th><th class="text-center">Orders</th><th class="text-end">LTV (Revenue)</th></tr></thead>
                    <tbody>
                    @forelse ($topCustomers as $tc)
                        <tr>
                            <td>
                                <a href="{{ route('admin.customers.show', $tc) }}" class="fw-semibold text-decoration-none">{{ $tc->name }}</a>
                                <div class="small text-muted">{{ $tc->email }}</div>
                            </td>
                            <td class="text-center">{{ $tc->orders_count }}</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($tc->lifetime_value, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No customer data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── COD Heavy Risk Flag ──────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-shield-exclamation me-1"></i> COD Risk Flag
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light"><tr><th>Customer</th><th>Failed/Cancelled</th><th>COD %</th></tr></thead>
                        <tbody>
                        @forelse ($codHeavyCustomers as $ch)
                            @php
                                $codPercent = $ch->total_orders > 0 ? round(($ch->cod_orders / $ch->total_orders) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $ch) }}" class="fw-semibold text-decoration-none">{{ $ch->name }}</a>
                                </td>
                                <td class="text-center text-danger fw-semibold">{{ $ch->failed_orders }} / {{ $ch->total_orders }}</td>
                                <td class="text-center">{{ $codPercent }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No risky customers identified.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
