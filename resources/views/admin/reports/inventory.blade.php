@extends('layouts.admin')
@section('title', 'Inventory Report')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Inventory Report</h1>
</div>

<ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
    <li class="nav-item m-1" role="presentation">
        <button class="nav-link active text-danger fw-semibold" id="oos-tab" data-bs-toggle="tab" data-bs-target="#oos" type="button" role="tab"><i class="bi bi-x-circle me-1"></i> Out of Stock ({{ $outOfStock->total() }})</button>
    </li>
    <li class="nav-item m-1" role="presentation">
        <button class="nav-link text-warning fw-semibold" id="low-tab" data-bs-toggle="tab" data-bs-target="#low" type="button" role="tab"><i class="bi bi-exclamation-triangle me-1"></i> Low Stock ({{ $lowStock->total() }})</button>
    </li>
    <li class="nav-item m-1" role="presentation">
        <button class="nav-link text-success fw-semibold" id="instock-tab" data-bs-toggle="tab" data-bs-target="#instock" type="button" role="tab"><i class="bi bi-check-circle me-1"></i> In Stock ({{ $inStock->total() }})</button>
    </li>
</ul>

<div class="tab-content" id="inventoryTabsContent">
    {{-- ── Out of Stock ────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="oos" role="tabpanel">
        <div class="card border-danger">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-center">Stock Level</th></tr></thead>
                    <tbody>
                    @forelse ($outOfStock as $v)
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-decoration-none">{{ $v->product->name }}</a>
                                <div class="small text-muted">{{ $v->title }}</div>
                            </td>
                            <td class="small">{{ $v->sku ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $v->stock_qty }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No out-of-stock products.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($outOfStock->hasPages())
                <div class="card-footer py-2">{{ $outOfStock->appends(request()->except('oos_page'))->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ── Low Stock ────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="low" role="tabpanel">
        <div class="card border-warning">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-center">Stock Level (≤ {{ $threshold }})</th></tr></thead>
                    <tbody>
                    @forelse ($lowStock as $v)
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-decoration-none">{{ $v->product->name }}</a>
                                <div class="small text-muted">{{ $v->title }}</div>
                            </td>
                            <td class="small">{{ $v->sku ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $v->stock_qty }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No low stock products.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($lowStock->hasPages())
                <div class="card-footer py-2">{{ $lowStock->appends(request()->except('low_page'))->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ── In Stock ─────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="instock" role="tabpanel">
        <div class="card border-success">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-center">Stock Level</th></tr></thead>
                    <tbody>
                    @forelse ($inStock as $v)
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-decoration-none">{{ $v->product->name }}</a>
                                <div class="small text-muted">{{ $v->title }}</div>
                            </td>
                            <td class="small">{{ $v->sku ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $v->stock_qty }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No products currently tracked in stock.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($inStock->hasPages())
                <div class="card-footer py-2">{{ $inStock->appends(request()->except('stock_page'))->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
