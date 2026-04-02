@extends('layouts.storefront')
@section('title', 'My Returns')
@section('content')
<h1 class="h4 mb-3">My Return Requests</h1>
@if ($returns->isEmpty())
    <div class="alert alert-info">No return requests yet.</div>
@else
<div class="table-responsive">
<table class="table table-hover">
    <thead class="table-light"><tr>
        <th>Return #</th><th>Order</th><th>Status</th><th>Resolution</th><th>Requested</th>
    </tr></thead>
    <tbody>
    @foreach ($returns as $r)
        <tr>
            <td>#{{ $r->id }}</td>
            <td>{{ $r->order->order_number }}</td>
            <td><span class="badge bg-{{ match($r->status) {'requested'=>'warning','approved'=>'info','received'=>'primary','closed'=>'success',default=>'secondary'} }}">{{ $r->status }}</span></td>
            <td>{{ $r->resolution ?? '—' }}</td>
            <td>{{ $r->created_at->format('d M Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<div class="mt-2">{{ $returns->links() }}</div>
@endif
<a href="{{ route('account.orders') }}" class="btn btn-outline-secondary btn-sm mt-2">← My Orders</a>
@endsection
