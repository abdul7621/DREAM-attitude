@extends('layouts.account')
@section('title', 'My Orders')
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-receipt me-2"></i>My Orders</h1>
@if ($orders->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-bag fs-1 d-block mb-2"></i>
            You haven't placed any orders yet. <a href="{{ route('home') }}" class="fw-semibold">Start shopping →</a>
        </div>
    </div>
@else
<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr>
        <th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th>
    </tr></thead>
    <tbody>
    @foreach ($orders as $order)
        <tr>
            <td class="fw-semibold">{{ $order->order_number }}</td>
            <td>{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
            <td>{{ $order->order_items_count ?? '—' }}</td>
            <td>₹{{ number_format($order->grand_total, 2) }}</td>
            <td><span class="badge bg-{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['color'] ?? 'secondary' }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span></td>
            <td><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endif
@endsection
