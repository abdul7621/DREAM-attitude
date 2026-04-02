@extends('layouts.admin')
@section('title', 'Returns')
@section('content')
<h1 class="h4 mb-3">Return Requests</h1>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr>
        <th>ID</th><th>Order</th><th>Customer</th><th>Status</th><th>Resolution</th><th>Credit</th><th>Requested</th><th></th>
    </tr></thead>
    <tbody>
    @forelse ($returns as $r)
        <tr>
            <td>#{{ $r->id }}</td>
            <td><a href="{{ route('admin.orders.show', $r->order) }}">{{ $r->order->order_number }}</a></td>
            <td>{{ $r->order->customer_name }}</td>
            <td><span class="badge bg-{{ match($r->status) {'requested'=>'warning','approved'=>'info','received'=>'primary','closed'=>'success',default=>'secondary'} }}">{{ $r->status }}</span></td>
            <td>{{ $r->resolution ?? '—' }}</td>
            <td>{{ $r->store_credit_amount ? '₹'.$r->store_credit_amount : '—' }}</td>
            <td>{{ $r->created_at->format('d M Y') }}</td>
            <td><a href="{{ route('admin.returns.show', $r) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-3">No return requests.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $returns->links() }}</div>
@endsection
