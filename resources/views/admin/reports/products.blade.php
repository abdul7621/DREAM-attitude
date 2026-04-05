@extends('layouts.admin')
@section('title', 'Product Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Product Report</h1>
</div>

<div class="row g-4 mb-4">
    {{-- ── Top Selling Products (Revenue) ────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-trophy text-warning me-1"></i> Top Sellers (by Revenue)
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th class="text-center">Sold</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                    @forelse ($topProducts as $tp)
                        <tr>
                            <td class="small">{{ $tp->product_name_snapshot }}</td>
                            <td class="text-center">{{ $tp->total_qty }}</td>
                            <td class="text-end fw-semibold text-success">₹{{ number_format($tp->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No sales data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Revenue Per Product (All Time) ──────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-bar-chart me-1"></i> Revenue per Product
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th class="text-center">Sold</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                    @forelse ($productRevenues as $pr)
                        <tr>
                            <td class="small">{{ $pr->product_name_snapshot }}</td>
                            <td class="text-center">{{ $pr->total_qty }}</td>
                            <td class="text-end">₹{{ number_format($pr->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($productRevenues->hasPages())
                <div class="card-footer py-2">{{ $productRevenues->appends(request()->except('rev_page'))->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Dead Products (No sales in 30 days) ──────────────────── --}}
<div class="card border-danger">
    <div class="card-header bg-danger text-white fw-semibold">
        <i class="bi bi-exclamation-triangle me-1"></i> Dead Products (No sales in last 30 days)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr>
                    <th>ID</th><th>Product Name</th><th>Created At</th>
                </tr></thead>
                <tbody>
                @forelse ($deadProducts as $dp)
                    <tr>
                        <td>#{{ $dp->id }}</td>
                        <td class="fw-semibold">{{ $dp->name }}</td>
                        <td class="small text-muted">{{ $dp->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Great! No dead products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($deadProducts->hasPages())
        <div class="card-footer py-2">{{ $deadProducts->appends(request()->except('page'))->links() }}</div>
    @endif
</div>
@endsection
