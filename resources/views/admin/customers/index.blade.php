@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Customers</h1>
</div>

{{-- Search --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, email, phone…" value="{{ request('q') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
<div class="table-responsive">
<table class="table table-sm table-hover mb-0 align-middle">
    <thead class="table-light"><tr>
        <th>Name</th><th>Email</th><th>Phone</th><th class="text-center">Orders</th><th class="text-end">Total Spent</th><th>Joined</th><th></th>
    </tr></thead>
    <tbody>
    @forelse ($customers as $c)
        <tr>
            <td class="fw-semibold">{{ $c->name }}</td>
            <td class="small">{{ $c->email }}</td>
            <td class="small">{{ $c->phone ?? '—' }}</td>
            <td class="text-center">{{ $c->orders_count }}</td>
            <td class="text-end fw-semibold">₹{{ number_format($c->orders_sum_grand_total ?? 0, 0) }}</td>
            <td class="small text-muted">{{ $c->created_at ? $c->created_at->format('d M Y') : '—' }}</td>
            <td><a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-eye"></i></a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-3">No customers yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $customers->links() }}</div>
@endsection
