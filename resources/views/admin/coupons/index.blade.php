@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Coupons</h1>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">+ New Coupon</a>
</div>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr>
        <th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Limit</th><th>Active</th><th>Expires</th><th></th>
    </tr></thead>
    <tbody>
    @forelse ($coupons as $c)
        <tr>
            <td><code>{{ $c->code }}</code></td>
            <td>{{ $c->type }}</td>
            <td>{{ $c->type === 'percent' ? $c->value.'%' : '₹'.$c->value }}</td>
            <td>{{ $c->used_count }}</td>
            <td>{{ $c->usage_limit ?? '∞' }}</td>
            <td>{!! $c->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
            <td>{{ $c->ends_at?->format('d M Y') ?? '—' }}</td>
            <td class="text-end">
                <a href="{{ route('admin.coupons.edit', $c) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="{{ route('admin.coupons.destroy', $c) }}" method="post" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Del</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-3">No coupons yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $coupons->links() }}</div>
@endsection
