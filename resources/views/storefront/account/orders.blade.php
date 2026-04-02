@extends('layouts.storefront')
@section('title', 'My Orders')
@section('content')
<h1 class="h4 mb-3">My Orders</h1>
@if ($orders->isEmpty())
    <div class="alert alert-info">You haven't placed any orders yet. <a href="{{ route('home') }}">Start shopping →</a></div>
@else
<div class="table-responsive">
<table class="table table-hover">
    <thead class="table-light"><tr>
        <th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th>
    </tr></thead>
    <tbody>
    @foreach ($orders as $order)
        <tr>
            <td>{{ $order->order_number }}</td>
            <td>{{ $order->placed_at?->format('d M Y') ?? '—' }}</td>
            <td>{{ $order->orderItems?->count() ?? '—' }}</td>
            <td>₹{{ number_format($order->grand_total, 2) }}</td>
            <td><span class="badge bg-secondary">{{ $order->order_status }}</span></td>
            <td><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<div class="mt-2">{{ $orders->links() }}</div>
@endif
@endsection
