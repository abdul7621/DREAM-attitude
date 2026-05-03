@extends('layouts.admin')
@section('title', 'Landing Pages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0" style="font-weight: 700;">Landing Pages</h1>
    <a href="{{ route('admin.landing-pages.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Landing Page
    </a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Title</th>
                    <th>URL</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $pg)
                <tr>
                    <td class="ps-4 fw-medium">{{ $pg->title }}</td>
                    <td>
                        <a href="{{ route('landing.show', $pg->slug) }}" target="_blank" class="text-decoration-none">
                            /offer/{{ $pg->slug }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:11px;"></i>
                        </a>
                    </td>
                    <td>
                        @if($pg->original_price)
                            <del class="text-muted">₹{{ number_format($pg->original_price) }}</del>
                        @endif
                        <strong>₹{{ number_format($pg->offer_price) }}</strong>
                    </td>
                    <td>
                        @if($pg->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.landing-pages.edit', $pg) }}" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                        <form action="{{ route('admin.landing-pages.destroy', $pg) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this landing page?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No landing pages yet. Create your first one!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
