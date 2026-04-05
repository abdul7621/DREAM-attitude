@extends('layouts.admin')

@section('title', 'Add Menu')

@section('content')
<div class="mb-3 d-flex align-items-center gap-2">
    <a href="{{ route('admin.menus.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    <h1 class="h4 mb-0">Create Menu</h1>
</div>

<form action="{{ route('admin.menus.store') }}" method="POST">
    @csrf
    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Menu Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                <div class="form-text">For your own reference. (e.g. "Main Header", "Footer Legal")</div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Location Key</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" required pattern="^[a-zA-Z0-9_\-]+$">
                <div class="form-text">Used by the frontend to fetch the menu. Recommended: <code>header</code>, <code>footer</code>, <code>topbar</code>.</div>
            </div>

            <div class="mb-4 form-check form-switch border p-3 rounded bg-light d-flex align-items-center">
                <input class="form-check-input mt-0 me-2" type="checkbox" role="switch" name="is_active" id="isActive" value="1" checked style="width: 2.5em; height: 1.25em;">
                <label class="form-check-label ms-1" for="isActive"><strong>Menu is Active</strong><br><small class="text-muted">If disabled, it will not render on the storefront.</small></label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Menu</button>
        </div>
    </div>
</form>
@endsection
