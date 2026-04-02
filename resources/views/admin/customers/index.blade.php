@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<h1 class="h4 mb-3">Customers</h1>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th>
    </tr></thead>
    <tbody>
    @forelse ($customers as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->email }}</td>
            <td>{{ $c->phone ?? '—' }}</td>
            <td>{{ $c->orders_count }}</td>
            <td>{{ $c->created_at->format('d M Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-3">No customers yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $customers->links() }}</div>
@endsection
