@extends('layouts.admin')

@section('title', 'Navigation Menus')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Menus</h1>
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> Add Menu</a>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Menu Name</th>
                    <th>Location Key</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td class="ps-3 fw-medium">{{ $menu->name }}</td>
                    <td><span class="badge bg-secondary">{{ $menu->location }}</span></td>
                    <td>
                        @if($menu->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end pe-3">
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Manage Items</a>
                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this menu?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No menus found. Get started by creating the 'header' menu.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
