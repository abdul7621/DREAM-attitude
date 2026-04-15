@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">CMS Pages</h1>
        <a class="btn btn-primary" href="{{ route('admin.pages.create') }}">Add Page</a>
    </div>
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-striped mb-0">
            <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($pages as $p)
                <tr>
                    <td>{{ $p->title }}</td>
                    <td>{{ $p->slug }}</td>
                    <td>
                        <span class="badge {{ $p->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $p->is_active ? 'Active' : 'Draft' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-info" href="{{ route('page.show', $p->slug) }}" target="_blank">View</a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.pages.edit', $p) }}">Edit</a>
                        <form action="{{ route('admin.pages.destroy', $p) }}" method="post" class="d-inline" onsubmit="return confirm('Delete page?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No pages created yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $pages->links() }}</div>
@endsection
