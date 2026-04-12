@extends('layouts.admin')
@section('title', $user->name . ' — Customer')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">
            {{ $user->name }}
            <span class="badge bg-{{ $segmentBadge['color'] }} ms-2" style="font-size:.7rem;">{{ $segmentBadge['label'] }}</span>
        </h1>
        <span class="small text-muted">Customer since {{ $user->created_at?->format('d M Y') ?? '—' }}</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($user->phone)
            <a href="tel:{{ $user->phone }}" class="btn btn-sm btn-outline-primary" title="Call"><i class="bi bi-telephone-outbound"></i> Call</a>
            <a href="https://wa.me/91{{ preg_replace('/^(\+?91)/', '', (string)$user->phone) }}" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp"><i class="bi bi-whatsapp"></i> WhatsApp</a>
        @endif
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- ── KPI Cards ──────────────────────────────────────── --}}
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Total Orders</div>
                <div class="h3 fw-bold mb-0">{{ $orderCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Total Spent (LTV)</div>
                <div class="h3 fw-bold text-success mb-0">₹{{ number_format($totalSpent, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Avg Order Value</div>
                <div class="h3 fw-bold mb-0">₹{{ number_format($avgOrder, 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-muted small">Reviews</div>
                <div class="h3 fw-bold mb-0">{{ $reviews->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Left Column ────────────────────────────────────── --}}
    <div class="col-lg-8">
        {{-- Orders --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-receipt me-1"></i> Orders ({{ $orderCount }})</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Order</th><th class="text-end">Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    @forelse ($user->orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-semibold">{{ $order->order_number }}</a></td>
                            <td class="text-end">₹{{ number_format($order->grand_total, 0) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->payment_method === 'cod' ? 'warning text-dark' : 'primary' }}" style="font-size:.65rem;">{{ strtoupper($order->payment_method) }}</span>
                            </td>
                            <td><span class="badge bg-{{ $order->statusColor() }}" style="font-size:.65rem;">{{ $order->statusLabel() }}</span></td>
                            <td class="small text-muted">{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Reviews --}}
        @if ($reviews->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-star-half me-1"></i> Reviews ({{ $reviews->count() }})</div>
            <ul class="list-group list-group-flush">
                @foreach ($reviews as $review)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-warning" style="font-size:.8rem;">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <span class="small">{{ $review->product?->name ?? 'Unknown' }}</span>
                            </div>
                            <span class="badge bg-{{ $review->is_approved ? 'success' : 'warning text-dark' }}" style="font-size:.6rem;">
                                {{ $review->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        @if ($review->body)
                            <p class="small text-muted mb-0 mt-1">{{ \Illuminate\Support\Str::limit($review->body, 150) }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Returns --}}
        @if ($returns->isNotEmpty())
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-arrow-return-left me-1"></i> Returns ({{ $returns->count() }})</div>
            <ul class="list-group list-group-flush">
                @foreach ($returns as $rr)
                    <li class="list-group-item d-flex justify-content-between">
                        <div>
                            <span class="small fw-semibold">Order #{{ $rr->order?->order_number ?? 'N/A' }}</span>
                            <span class="small text-muted ms-1">{{ $rr->reason }}</span>
                        </div>
                        <span class="badge bg-{{ $rr->status === 'approved' ? 'success' : ($rr->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($rr->status) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- ── Right Column ───────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-person me-1"></i> Contact Info</div>
            <div class="card-body">
                <p class="mb-2"><i class="bi bi-envelope text-muted me-2"></i> {{ $user->email }}</p>
                <p class="mb-2"><i class="bi bi-telephone text-muted me-2"></i> {{ $user->phone ?? 'Not provided' }}</p>
                <p class="mb-0"><i class="bi bi-calendar text-muted me-2"></i> Joined {{ $user->created_at?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-tags me-1"></i> Tags</div>
            <div class="card-body">
                <form action="{{ route('admin.customers.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-2">
                        @foreach($user->tags as $tag)
                            <span class="badge bg-secondary mb-1" style="font-size: .75rem;">{{ $tag->tag_name }}</span>
                        @endforeach
                    </div>

                    <label class="form-label small text-muted">Comma-separated tags</label>
                    <input type="text" name="tags" class="form-control form-control-sm mb-2" value="{{ $user->tags->pluck('tag_name')->implode(', ') }}">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Save Tags</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
