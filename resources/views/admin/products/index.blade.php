@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Products</h1>
        <a class="btn btn-primary" href="{{ route('admin.products.create') }}">Add product</a>
    </div>
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-striped mb-0">
            <thead><tr><th>Name</th><th>SKU</th><th>Category</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->sku ?? '—' }}</td>
                    <td>{{ $p->category?->name ?? '—' }}</td>
                    <td>{{ $p->status }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.edit', $p) }}">Edit</a>
                        <form action="{{ route('admin.products.destroy', $p) }}" method="post" class="d-inline" onsubmit="return confirm('Delete?');">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $products->links() }}</div>
@endsection
