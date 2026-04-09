@extends('layouts.account')
@section('title', 'My Returns')
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-arrow-return-left me-2"></i>My Return Requests</h1>
@if ($returns->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-arrow-return-left fs-1 d-block mb-2"></i>
            No return requests yet.
        </div>
    </div>
@else
<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
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
</div>
<div class="mt-3">{{ $returns->links() }}</div>
@endif
@endsection
