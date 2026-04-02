@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<h1 class="h4 mb-3">Reviews Moderation</h1>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="table-light"><tr>
        <th>Product</th><th>By</th><th>Rating</th><th>Review</th><th>Approved</th><th></th>
    </tr></thead>
    <tbody>
    @forelse ($reviews as $r)
        <tr>
            <td>{{ $r->product->name }}</td>
            <td>{{ $r->reviewer_name }}</td>
            <td>{{ str_repeat('★', $r->rating) }}</td>
            <td>{{ \Illuminate\Support\Str::limit($r->body, 80) }}</td>
            <td>{!! $r->is_approved ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>' !!}</td>
            <td class="text-end">
                <form action="{{ route('admin.reviews.update', $r) }}" method="post" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_approved" value="{{ $r->is_approved ? 0 : 1 }}">
                    <button class="btn btn-sm btn-outline-{{ $r->is_approved ? 'secondary' : 'success' }}">
                        {{ $r->is_approved ? 'Hide' : 'Approve' }}
                    </button>
                </form>
                <form action="{{ route('admin.reviews.destroy', $r) }}" method="post" class="d-inline" onsubmit="return confirm('Delete review?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Del</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-3">No reviews yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
