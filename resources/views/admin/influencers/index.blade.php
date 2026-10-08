@extends('layouts.admin')
@section('title', 'Influencer Management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="bi bi-stars text-warning me-2"></i>Influencer Management</h1>
        <p class="text-muted small mb-0">Track influencer referral sales, coupon usage, and commission payouts.</p>
    </div>
    <a href="{{ route('admin.influencers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Influencer
    </a>
</div>

{{-- Overall KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Total Influencers</div>
                <div class="h3 fw-bold text-dark mb-0">{{ $overallStats['total_influencers'] }} <span class="badge bg-success-subtle text-success fs-6 fw-normal">{{ $overallStats['active_influencers'] }} Active</span></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Total Sales Generated</div>
                <div class="h3 fw-bold text-primary mb-0">₹{{ number_format($overallStats['total_sales_generated'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Commission Paid</div>
                <div class="h3 fw-bold text-success mb-0">₹{{ number_format($overallStats['total_commission_paid'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Pending Payouts</div>
                <div class="h3 fw-bold text-danger mb-0">₹{{ number_format($overallStats['total_commission_pending'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters & Search --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.influencers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Search by name, email, phone, Instagram, or coupon...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark px-3">Filter</button>
                @if(request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.influencers.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Influencers Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Influencer</th>
                    <th>Assigned Coupon</th>
                    <th>Commission Rate</th>
                    <th class="text-center">Orders</th>
                    <th class="text-end">Sales Generated</th>
                    <th class="text-end">Unpaid Balance</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($influencers as $inf)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                {{ strtoupper(substr($inf->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.influencers.show', $inf) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $inf->name }}
                                </a>
                                <div class="small text-muted">
                                    {{ $inf->user?->email }}
                                    @if($inf->instagram_handle)
                                        · <span class="text-primary"><i class="bi bi-instagram"></i> {{ '@' . $inf->instagram_handle }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($inf->coupon)
                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                <i class="bi bi-tag-fill text-warning me-1"></i>{{ $inf->coupon->code }}
                            </span>
                            <div class="small text-muted mt-1">
                                ({{ $inf->coupon->type === 'percent' ? $inf->coupon->value . '% OFF' : '₹' . $inf->coupon->value . ' FLAT' }})
                            </div>
                        @else
                            <span class="text-muted small">None</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">
                            {{ $inf->commission_type === 'percent' ? $inf->commission_value . '%' : '₹' . $inf->commission_value }}
                        </span>
                        <span class="small text-muted">/ order</span>
                    </td>
                    <td class="text-center fw-semibold">
                        {{ $inf->total_orders }}
                    </td>
                    <td class="text-end fw-bold text-dark">
                        ₹{{ number_format($inf->total_sales ?? 0, 2) }}
                    </td>
                    <td class="text-end">
                        <span class="fw-bold {{ ($inf->unpaid_balance ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                            ₹{{ number_format($inf->unpaid_balance ?? 0, 2) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($inf->is_active)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.influencers.show', $inf) }}">
                                        <i class="bi bi-eye me-2 text-primary"></i> View Performance
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.influencers.edit', $inf) }}">
                                        <i class="bi bi-pencil me-2 text-dark"></i> Edit Details
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.influencers.destroy', $inf) }}" method="POST" onsubmit="return confirm('Deactivate this influencer account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-slash-circle me-2"></i> Deactivate
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No influencers found. Click "+ New Influencer" to add one.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($influencers->hasPages())
        <div class="card-footer py-2">
            {{ $influencers->links() }}
        </div>
    @endif
</div>
@endsection
