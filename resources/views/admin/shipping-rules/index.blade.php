@extends('layouts.admin')
@section('title', 'Shipping Rules')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Shipping Rules</h1>
    <a href="{{ route('admin.shipping-rules.create') }}" class="btn btn-primary btn-sm">+ New Rule</a>
</div>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>Priority</th><th>Name</th><th>Type</th><th>Config</th><th>Active</th><th></th></tr></thead>
    <tbody>
    @forelse ($rules as $r)
        <tr>
            <td>{{ $r->priority }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ $r->type }}</td>
            <td><code class="small">{{ json_encode($r->config) }}</code></td>
            <td>{!! $r->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('admin.shipping-rules.edit', $r) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="{{ route('admin.shipping-rules.destroy', $r) }}" method="post" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Del</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-3">No shipping rules yet. Create one to enable checkout.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $rules->links() }}</div>
@endsection
