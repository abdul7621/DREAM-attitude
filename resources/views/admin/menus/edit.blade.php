@extends('layouts.admin')

@section('title', 'Manage Menu: ' . $menu->name)

@section('content')
<div class="mb-3 d-flex align-items-center gap-2">
    <a href="{{ route('admin.menus.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    <h1 class="h4 mb-0">Manage Menu: {{ $menu->name }}</h1>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        {{-- Add Menu Item Form --}}
        <div class="card mb-4">
            <div class="card-header bg-white fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Menu Item</div>
            <div class="card-body">
                <form action="{{ route('admin.menus.items.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Link Label</label>
                        <input type="text" name="label" class="form-control" required placeholder="e.g. Home, Shop, About Us">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Destination URL</label>
                        <input type="text" name="link" class="form-control" placeholder="/shop or https://...">
                        <div class="form-text">Use relative paths (<code>/search</code>) for internal pages.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Parent Item (Nested Menu)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- None (Top Level) --</option>
                            @foreach($menu->parentItems as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="is_external" value="1" id="isExt">
                        <label class="form-check-label" for="isExt">Open in new tab (External Link)</label>
                    </div>

                    <button class="btn btn-primary w-100">Add to Menu</button>
                </form>
            </div>
        </div>

        {{-- Edit Menu Settings Form --}}
        <div class="card">
            <div class="card-header bg-white fw-bold"><i class="bi bi-gear me-1"></i> Menu Settings</div>
            <div class="card-body">
                <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Menu Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $menu->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location Key</label>
                        <input type="text" name="location" class="form-control" value="{{ $menu->location }}" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="menuActive" {{ $menu->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="menuActive">Menu Active</label>
                    </div>
                    <button class="btn btn-secondary w-100">Update Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Menu Items List --}}
        <div class="card">
            <div class="card-header bg-white fw-bold"><i class="bi bi-list-nested me-1"></i> Menu Items</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($menu->parentItems as $item)
                        {{-- Top Level Item --}}
                        <li class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-grip-vertical text-muted me-2"></i>
                                    <strong>{{ $item->label }}</strong>
                                    <span class="text-muted small ms-2">{{ $item->link }}</span>
                                    @if($item->is_external) <span class="badge bg-light text-dark border ms-1">External</span> @endif
                                    <div class="small text-muted mt-1"><i class="bi bi-sort-numeric-down"></i> Sort: {{ $item->sort_order }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.menus.items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this link?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>

                            {{-- Child Items --}}
                            @if($item->children->count() > 0)
                                <ul class="list-group mt-3 ms-4">
                                    @foreach($item->children as $child)
                                        <li class="list-group-item bg-light border-0 mb-1 rounded d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                                {{ $child->label }} <span class="text-muted small ms-2">{{ $child->link }}</span>
                                                <span class="small text-muted ms-2">[Sort: {{ $child->sort_order }}]</span>
                                            </div>
                                            <form action="{{ route('admin.menus.items.destroy', $child) }}" method="POST" onsubmit="return confirm('Delete this child link?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center text-muted">
                            No menu items added yet. Use the form on the left to add your first link.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
