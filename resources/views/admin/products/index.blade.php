@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <form action="{{ route('admin.products.bulk') }}" method="POST" id="bulk-form">
        @csrf
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Products</h1>
            <div>
                <span id="selected-count" class="badge bg-secondary me-2 d-none">0 Selected</span>
                <select name="action" class="form-select form-select-sm d-inline-block w-auto me-2" id="bulk-action" disabled>
                    <option value="">Bulk Actions</option>
                    <option value="status_active">Mark Active</option>
                    <option value="status_draft">Mark Draft</option>
                    <option value="delete">Delete (Soft)</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary me-3" id="bulk-submit" disabled>Apply</button>
                <a class="btn btn-sm btn-primary" href="{{ route('admin.products.create') }}">Add product</a>
            </div>
        </div>

        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($products as $p)
                    <tr class="{{ $p->trashed() ? 'opacity-50' : '' }}">
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="form-check-input row-checkbox">
                        </td>
                        <td>
                            {{ $p->name }}
                            @if($p->trashed()) <span class="badge bg-warning text-dark ms-1">Archived</span> @endif
                        </td>
                        <td>{{ $p->sku ?? '—' }}</td>
                        <td>{{ $p->category?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.edit', $p) }}">Edit</a>
                            
                            @if(!$p->trashed())
                            <button type="submit" form="del-form-{{ $p->id }}" class="btn btn-sm btn-outline-warning">Archive</button>
                            @else
                            <button type="submit" form="force-form-{{ $p->id }}" class="btn btn-sm btn-danger ms-1">Permanent Delete</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </form>

    @foreach ($products as $p)
        @if(!$p->trashed())
        <form id="del-form-{{ $p->id }}" action="{{ route('admin.products.destroy', $p) }}" method="post" class="d-none" onsubmit="return confirm('Archive this product?');">
            @csrf @method('DELETE')
        </form>
        @else
        <form id="force-form-{{ $p->id }}" action="{{ route('admin.products.forceDestroy', $p->id) }}" method="post" class="d-none" onsubmit="return confirm('WARNING: This will permanently wipe the product, its variants, and images. Continue?');">
            @csrf @method('DELETE')
        </form>
        @endif
    @endforeach

    <div class="mt-3">{{ $products->links() }}</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const bulkAction = document.getElementById('bulk-action');
            const bulkSubmit = document.getElementById('bulk-submit');
            const countBadge = document.getElementById('selected-count');

            function updateState() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const totalCount = checkboxes.length;

                selectAll.checked = checkedCount > 0 && checkedCount === totalCount;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;

                if (checkedCount > 0) {
                    countBadge.textContent = checkedCount + ' Selected';
                    countBadge.classList.remove('d-none');
                    bulkAction.disabled = false;
                    bulkSubmit.disabled = false;
                } else {
                    countBadge.classList.add('d-none');
                    bulkAction.disabled = true;
                    bulkSubmit.disabled = true;
                    bulkAction.value = '';
                }
            }

            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateState();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateState);
            });
            
            document.getElementById('bulk-form').addEventListener('submit', function(e) {
                if(bulkAction.value === 'delete' && !confirm('Are you sure you want to soft delete selected products?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
