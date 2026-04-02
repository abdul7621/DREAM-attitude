@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Categories</h1>
        <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">Add category</a>
    </div>
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-striped mb-0">
            <thead><tr><th>Name</th><th>Slug</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @foreach ($categories as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->slug }}</td>
                    <td>{{ $c->is_active ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.categories.edit', $c) }}">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $c) }}" method="post" class="d-inline" onsubmit="return confirm('Delete?');">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
