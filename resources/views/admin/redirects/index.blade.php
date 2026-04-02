@extends('layouts.admin')
@section('title', 'Redirects')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">URL Redirects</h1>
    <a href="{{ route('admin.redirects.create') }}" class="btn btn-primary btn-sm">+ New Redirect</a>
</div>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr><th>From</th><th>To</th><th>Code</th><th>Active</th><th></th></tr></thead>
    <tbody>
    @forelse ($redirects as $r)
        <tr>
            <td><code>{{ $r->from_path }}</code></td>
            <td><code>{{ $r->to_path }}</code></td>
            <td>{{ $r->http_code }}</td>
            <td>{!! $r->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('admin.redirects.edit', $r) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="{{ route('admin.redirects.destroy', $r) }}" method="post" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Del</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-3">No redirects yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $redirects->links() }}</div>
@endsection
