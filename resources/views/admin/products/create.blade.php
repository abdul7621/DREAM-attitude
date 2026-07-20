@extends('layouts.admin')

@section('title', 'Add product')

@section('content')
    <h1 class="h4 mb-3">Add product</h1>
    <form action="{{ route('admin.products.store') }}" method="post" enctype="multipart/form-data" class="bg-white p-3 rounded shadow-sm">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug (optional)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Master SKU</label>
                <input type="text" name="sku" value="{{ old('sku') }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Short description <span class="text-danger">*</span> <span class="text-muted fw-normal">(100-150 chars ideal)</span></label>
                <input type="text" name="short_description" id="shortDescInput" value="{{ old('short_description') }}" class="form-control" maxlength="160" placeholder="e.g. A lightweight argan oil shampoo that controls frizz and restores natural shine.">
                <div class="form-text"><span id="shortDescCount">0</span>/160 characters — Keep it crisp.</div>
            </div>
            <div class="col-12">
                <label class="form-label">Description (HTML ok)</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" value="{{ old('brand') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tags (comma)</label>
                <input type="text" name="tags" value="{{ old('tags') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    @foreach (['draft'=>'draft','active'=>'active','archived'=>'archived'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status','draft')==$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="f1" @checked(old('is_featured'))>
                    <label class="form-check-label" for="f1">Featured</label>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_bestseller" value="1" id="f2" @checked(old('is_bestseller'))>
                    <label class="form-check-label" for="f2">Bestseller</label>
                </div>
            </div>
            <hr>
            <div class="col-md-6">
                <label class="form-label">Variant title</label>
                <input type="text" name="variant_title" value="{{ old('variant_title','Default') }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Variant SKU</label>
                <input type="text" name="variant_sku" value="{{ old('variant_sku') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Retail price *</label>
                <input type="number" step="0.01" name="price_retail" value="{{ old('price_retail') }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Reseller price</label>
                <input type="number" step="0.01" name="price_reseller" value="{{ old('price_reseller') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bulk price</label>
                <input type="number" step="0.01" name="price_bulk" value="{{ old('price_bulk') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Compare-at (MRP)</label>
                <input type="number" step="0.01" name="compare_at_price" value="{{ old('compare_at_price') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Stock qty *</label>
                <input type="number" name="stock_qty" value="{{ old('stock_qty',0) }}" class="form-control" required min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Weight (g)</label>
                <input type="number" name="weight_grams" value="{{ old('weight_grams') }}" class="form-control" min="0">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="track_inventory" value="1" id="ti" @checked(old('track_inventory', true))>
                    <label class="form-check-label" for="ti">Track inventory</label>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">Product Images</label>
                <div class="sf-drag-drop-zone border border-2 border-dashed border-primary rounded p-4 text-center bg-light position-relative" id="dragDropZone" style="cursor: pointer; transition: all 0.2s ease;">
                    <input type="file" name="images[]" id="imagesInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" multiple accept="image/*" style="cursor: pointer; z-index: 2;">
                    <div class="py-3 text-muted">
                        <i class="bi bi-cloud-arrow-up-fill text-primary fs-1 mb-2"></i>
                        <p class="mb-1 fw-bold">Drag & Drop Images here or click to browse</p>
                        <p class="small text-secondary mb-0">Supports JPG, PNG, WEBP, AVIF (Max 100MB each)</p>
                    </div>
                </div>
                {{-- Live Previews --}}
                <div class="row g-2 mt-2" id="liveImagePreviewContainer"></div>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function(){
    var input = document.getElementById('shortDescInput');
    var count = document.getElementById('shortDescCount');
    if(input && count) {
        count.textContent = input.value.length;
        input.addEventListener('input', function() { count.textContent = this.value.length; });
    }
})();

(function () {
    const zone = document.getElementById('dragDropZone');
    const input = document.getElementById('imagesInput');
    const previewContainer = document.getElementById('liveImagePreviewContainer');

    if (zone && input && previewContainer) {
        // Dragover highlight
        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, function (e) {
                e.preventDefault();
                zone.style.borderColor = 'var(--bs-success)';
                zone.style.background = '#e9f5ec';
            }, false);
        });

        // Dragleave reset
        zone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            zone.style.borderColor = '';
            zone.style.background = '';
        }, false);

        // Handle drop
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.style.borderColor = '';
            zone.style.background = '';
            if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        }, false);

        // Trigger on selection change
        input.addEventListener('change', function () {
            previewContainer.innerHTML = '';
            const files = Array.from(input.files);
            
            if (files.length > 0) {
                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const col = document.createElement('div');
                            col.className = 'col-6 col-md-3';
                            col.innerHTML = `
                                <div class="border rounded p-2 bg-white position-relative shadow-sm" style="height: 100%;">
                                    <img src="${e.target.result}" class="img-fluid rounded mb-1" style="height: 120px; width: 100%; object-fit: cover;">
                                    <div class="small text-truncate fw-semibold text-dark">${file.name}</div>
                                    <div class="small text-secondary">${(file.size / 1024).toFixed(0)} KB</div>
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 10px;" onclick="window.removeSelectedFile(${index})">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            `;
                            previewContainer.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });

        // Global helper to remove selected file
        window.removeSelectedFile = function (indexToRemove) {
            const dt = new DataTransfer();
            const files = input.files;
            for (let i = 0; i < files.length; i++) {
                if (i !== indexToRemove) {
                    dt.items.add(files[i]);
                }
            }
            input.files = dt.files;
            // Trigger change event to redraw
            input.dispatchEvent(new Event('change'));
        };
    }
})();
</script>
@endpush
