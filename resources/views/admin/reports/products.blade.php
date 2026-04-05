@extends('layouts.admin')
@section('title', 'Product Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Product Report</h1>
</div>

<div class="row g-4 mb-4">
    {{-- ── Top Selling Products (Revenue) ────────────────────── --}}
    <div class="col-md-6">
        <div class="card mb-4 border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold">Top 80% Revenue Drivers (Pareto)</h5>
                <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge-fill"></i> Strategic Insight</span>
            </div>
            <div class="card-body">
                <div class="alert alert-primary border-0 rounded-3">
                    <h6 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow"></i> The 80-20 Rule</h6>
                    <p class="mb-0 small">Only <strong>{{ $paretoPercent }}%</strong> of your total catalog ({{ $paretoProducs->count() }} out of {{ $totalCatalogSize }} products) generated <strong>{{ $paretoRevenuePercent }}%</strong> of your total recorded revenue. Focus your ad spend here.</p>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $item)
                            <tr>
                                <td>{{ $item->product_name_snapshot }}</td>
                                <td class="text-end text-success fw-bold">₹{{ number_format($item->total_revenue) }}</td>
                            </tr>
                            @endforeach
                            @if($paretoProducs->count() > 20)
                            <tr>
                                <td colspan="2" class="text-center text-muted small py-2 bg-light">...and {{ $paretoProducs->count() - 20 }} more products making up the top 80%.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> {{-- ── Revenue Per Product (All Time) ──────────────────────── --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-bar-chart me-1"></i> Revenue per Product
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Product ID</th>
                                <th>Name</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deadProducts as $product)
                            <tr>
                                <td class="ps-4 text-muted">#{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">Run Sale</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No dead stock found. Great job!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($deadProducts->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $deadProducts->withQueryString()->links() }}
                </div>
            @endif
        </div>
        
        <div class="card mb-4 border-0 shadow-sm border-top border-danger border-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-arrow-return-left"></i> Top Refunded/Returned Products</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Product Name</th>
                                <th class="text-center">Units Lost</th>
                                <th class="text-end pe-4">Revenue Lost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refundedProducts as $item)
                            <tr>
                                <td class="ps-4">{{ $item->product_name_snapshot }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $item->returned_qty }}</span></td>
                                <td class="text-end pe-4 text-danger fw-bold">-₹{{ number_format($item->returned_revenue) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No refunds tracked yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
