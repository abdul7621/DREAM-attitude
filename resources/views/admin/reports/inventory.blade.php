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
                    <thead class="table-light">
                        <tr>
                            <th style="width: 54px;">Item</th>
                            <th>Product & Size</th>
                            <th style="width: 100px;">SKU</th>
                            <th class="text-end" style="width: 90px;">MRP</th>
                            <th class="text-end" style="width: 100px;">Retail Price</th>
                            <th class="text-center" style="width: 100px;">Stock</th>
                            <th class="text-end" style="width: 70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($outOfStock as $v)
                        <tr>
                            {{-- Product Image --}}
                            <td class="pe-0">
                                @if($v->thumbnail_url)
                                    <img src="{{ $v->thumbnail_url }}" alt="{{ $v->product->name }}" class="rounded border shadow-xs" style="width: 44px; height: 44px; object-fit: cover; background: #fff;" loading="lazy">
                                @else
                                    <div class="rounded border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 44px; height: 44px;">
                                        <i class="bi bi-image" style="font-size: 1.15rem; color: #adb5bd;"></i>
                                    </div>
                                @endif
                            </td>
                            {{-- Product Name & Volume Badge --}}
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $v->product->name }}
                                </a>
                                @if($badge = $v->volume_badge)
                                    <div class="mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-tag-fill me-1" style="font-size: 0.65rem;"></i>{{ $badge }}
                                        </span>
                                    </div>
                                @elseif($v->title && !in_array(strtolower(trim($v->title)), ['default', 'default title', 'default-title']))
                                    <div class="mt-1">
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                            {{ $v->title }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            {{-- SKU --}}
                            <td class="small"><code>{{ $v->sku ?: '—' }}</code></td>
                            {{-- MRP --}}
                            <td class="text-end">
                                @if($v->mrp > (float)$v->price_retail)
                                    <span class="text-decoration-line-through text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @else
                                    <span class="text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @endif
                            </td>
                            {{-- Price --}}
                            <td class="text-end">
                                <span class="fw-semibold text-dark">₹{{ number_format((float) $v->price_retail, 2) }}</span>
                                @if($v->discount_percent > 0)
                                    <div><span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">{{ $v->discount_percent }}% OFF</span></div>
                                @endif
                            </td>
                            {{-- Stock Level --}}
                            <td class="text-center">
                                <span class="badge bg-danger fs-6 px-2 py-1">{{ $v->stock_qty }}</span>
                            </td>
                            {{-- Action --}}
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Product">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No out-of-stock products.</td></tr>
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
                    <thead class="table-light">
                        <tr>
                            <th style="width: 54px;">Item</th>
                            <th>Product & Size</th>
                            <th style="width: 100px;">SKU</th>
                            <th class="text-end" style="width: 90px;">MRP</th>
                            <th class="text-end" style="width: 100px;">Retail Price</th>
                            <th class="text-center" style="width: 120px;">Stock (≤ {{ $threshold }})</th>
                            <th class="text-end" style="width: 70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($lowStock as $v)
                        <tr>
                            {{-- Product Image --}}
                            <td class="pe-0">
                                @if($v->thumbnail_url)
                                    <img src="{{ $v->thumbnail_url }}" alt="{{ $v->product->name }}" class="rounded border shadow-xs" style="width: 44px; height: 44px; object-fit: cover; background: #fff;" loading="lazy">
                                @else
                                    <div class="rounded border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 44px; height: 44px;">
                                        <i class="bi bi-image" style="font-size: 1.15rem; color: #adb5bd;"></i>
                                    </div>
                                @endif
                            </td>
                            {{-- Product Name & Volume Badge --}}
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $v->product->name }}
                                </a>
                                @if($badge = $v->volume_badge)
                                    <div class="mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-tag-fill me-1" style="font-size: 0.65rem;"></i>{{ $badge }}
                                        </span>
                                    </div>
                                @elseif($v->title && !in_array(strtolower(trim($v->title)), ['default', 'default title', 'default-title']))
                                    <div class="mt-1">
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                            {{ $v->title }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            {{-- SKU --}}
                            <td class="small"><code>{{ $v->sku ?: '—' }}</code></td>
                            {{-- MRP --}}
                            <td class="text-end">
                                @if($v->mrp > (float)$v->price_retail)
                                    <span class="text-decoration-line-through text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @else
                                    <span class="text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @endif
                            </td>
                            {{-- Price --}}
                            <td class="text-end">
                                <span class="fw-semibold text-dark">₹{{ number_format((float) $v->price_retail, 2) }}</span>
                                @if($v->discount_percent > 0)
                                    <div><span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">{{ $v->discount_percent }}% OFF</span></div>
                                @endif
                            </td>
                            {{-- Stock Level --}}
                            <td class="text-center">
                                <span class="badge bg-warning text-dark fs-6 px-2 py-1">{{ $v->stock_qty }}</span>
                            </td>
                            {{-- Action --}}
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Product">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No low stock products.</td></tr>
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
                    <thead class="table-light">
                        <tr>
                            <th style="width: 54px;">Item</th>
                            <th>Product & Size</th>
                            <th style="width: 100px;">SKU</th>
                            <th class="text-end" style="width: 90px;">MRP</th>
                            <th class="text-end" style="width: 100px;">Retail Price</th>
                            <th class="text-center" style="width: 100px;">Stock</th>
                            <th class="text-end" style="width: 70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($inStock as $v)
                        <tr>
                            {{-- Product Image --}}
                            <td class="pe-0">
                                @if($v->thumbnail_url)
                                    <img src="{{ $v->thumbnail_url }}" alt="{{ $v->product->name }}" class="rounded border shadow-xs" style="width: 44px; height: 44px; object-fit: cover; background: #fff;" loading="lazy">
                                @else
                                    <div class="rounded border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 44px; height: 44px;">
                                        <i class="bi bi-image" style="font-size: 1.15rem; color: #adb5bd;"></i>
                                    </div>
                                @endif
                            </td>
                            {{-- Product Name & Volume Badge --}}
                            <td>
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $v->product->name }}
                                </a>
                                @if($badge = $v->volume_badge)
                                    <div class="mt-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-tag-fill me-1" style="font-size: 0.65rem;"></i>{{ $badge }}
                                        </span>
                                    </div>
                                @elseif($v->title && !in_array(strtolower(trim($v->title)), ['default', 'default title', 'default-title']))
                                    <div class="mt-1">
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 0.75rem;">
                                            {{ $v->title }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            {{-- SKU --}}
                            <td class="small"><code>{{ $v->sku ?: '—' }}</code></td>
                            {{-- MRP --}}
                            <td class="text-end">
                                @if($v->mrp > (float)$v->price_retail)
                                    <span class="text-decoration-line-through text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @else
                                    <span class="text-muted small">₹{{ number_format($v->mrp, 2) }}</span>
                                @endif
                            </td>
                            {{-- Price --}}
                            <td class="text-end">
                                <span class="fw-semibold text-dark">₹{{ number_format((float) $v->price_retail, 2) }}</span>
                                @if($v->discount_percent > 0)
                                    <div><span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">{{ $v->discount_percent }}% OFF</span></div>
                                @endif
                            </td>
                            {{-- Stock Level --}}
                            <td class="text-center">
                                <span class="badge bg-success fs-6 px-2 py-1">{{ $v->stock_qty }}</span>
                            </td>
                            {{-- Action --}}
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $v->product_id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Product">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No products currently tracked in stock.</td></tr>
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
