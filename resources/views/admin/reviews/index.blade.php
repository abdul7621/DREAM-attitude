@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<h1 class="h4 mb-3">Reviews Moderation</h1>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
    <thead class="table-light"><tr>
        <th>Product</th><th>By</th><th>Rating</th><th>Review details</th><th>Approved</th><th></th>
    </tr></thead>
    <tbody>
    @forelse ($reviews as $r)
        <tr>
            <td>
                <div class="fw-semibold">{{ $r->product->name }}</div>
                @if($r->hair_type || $r->skin_type)
                    <div class="mt-1 small">
                        @if($r->hair_type) <span class="badge bg-light text-secondary border">Hair: {{ $r->hair_type }}</span> @endif
                        @if($r->skin_type) <span class="badge bg-light text-secondary border">Skin: {{ $r->skin_type }}</span> @endif
                    </div>
                @endif
            </td>
            <td>{{ $r->reviewer_name }}</td>
            <td style="color: var(--color-gold);">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</td>
            <td>
                <div style="font-size: 14px; font-style: italic;">"{{ $r->body }}"</div>
                @if(!empty($r->images))
                    <div class="d-flex gap-1 mt-2">
                        @foreach($r->images as $img)
                            <a href="{{ asset('storage/' . $img) }}" target="_blank">
                                <img src="{{ asset('storage/' . $img) }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($r->seller_reply)
                    <div class="mt-2 p-2 rounded bg-light border-start border-3 border-warning small">
                        <strong>Reply:</strong> {{ $r->seller_reply }}
                    </div>
                @endif
                <div class="mt-2">
                    <button class="btn btn-sm btn-link p-0 text-decoration-none small text-secondary" type="button" onclick="this.nextElementSibling.classList.toggle('d-none');">
                        <i class="bi bi-reply"></i> {{ $r->seller_reply ? 'Edit Reply' : 'Add Reply' }}
                    </button>
                    <form action="{{ route('admin.reviews.update', $r) }}" method="post" class="d-none mt-2" style="max-width: 400px;">
                        @csrf
                        @method('PATCH')
                        <div class="input-group input-group-sm">
                            <input type="text" name="seller_reply" value="{{ $r->seller_reply }}" class="form-control" placeholder="Write reply...">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </td>
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
