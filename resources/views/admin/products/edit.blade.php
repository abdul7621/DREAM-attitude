@extends('layouts.admin')

@section('title', 'Edit '.$product->name)

@section('content')
    <h1 class="h4 mb-3">Edit product</h1>
    <form action="{{ route('admin.products.update', $product) }}" method="post" enctype="multipart/form-data" class="bg-white p-3 rounded shadow-sm">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug (optional)</label>
                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Master SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Short description</label>
                <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" class="form-control" maxlength="512">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tags (comma)</label>
                <input type="text" name="tags" value="{{ old('tags', $product->tags ? implode(',', $product->tags) : '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status *</label>
                <select name="status" class="form-select" required>
                    @foreach (['draft'=>'draft','active'=>'active','archived'=>'archived'] as $k=>$v)
                        <option value="{{ $k }}" @selected(old('status', $product->status)==$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="f1" @checked(old('is_featured', $product->is_featured))>
                    <label class="form-check-label" for="f1">Featured</label>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_bestseller" value="1" id="f2" @checked(old('is_bestseller', $product->is_bestseller))>
                    <label class="form-check-label" for="f2">Bestseller</label>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h2 class="h6">Product Meta Data</h2>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Ingredients</label>
                <textarea name="meta[ingredients]" rows="2" class="form-control">{{ old('meta.ingredients', $product->meta['ingredients'] ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">How to Use</label>
                <textarea name="meta[how_to_use]" rows="2" class="form-control">{{ old('meta.how_to_use', $product->meta['how_to_use'] ?? '') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">FAQ</label>
                <textarea name="meta[faq]" rows="3" class="form-control">{{ old('meta.faq', $product->meta['faq'] ?? '') }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Offer Message</label>
                <input type="text" name="meta[offer_message]" class="form-control" value="{{ old('meta.offer_message', $product->meta['offer_message'] ?? '') }}" placeholder="e.g. Extra 10% OFF via UPI">
            </div>
            <div class="col-md-6">
                <label class="form-label">Urgency Message</label>
                <input type="text" name="meta[urgency_message]" class="form-control" value="{{ old('meta.urgency_message', $product->meta['urgency_message'] ?? '') }}" placeholder="e.g. Only {stock} left in stock!">
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 mb-0">Custom Layout Engine</h2>
            <div class="form-check form-switch shadow-sm px-4 py-2 border rounded bg-white">
                <input class="form-check-input ms-0 me-2" type="checkbox" name="use_custom_layout" value="1" id="useCustomLayoutBtn" style="cursor:pointer; width: 2.5em;" @checked(old('use_custom_layout', !empty($product->layout_config)))>
                <label class="form-check-label fw-bold" for="useCustomLayoutBtn" style="cursor:pointer; padding-top:2px;">Override Theme Layout</label>
            </div>
        </div>
        <div class="border rounded p-3 bg-light {{ old('use_custom_layout', !empty($product->layout_config)) ? '' : 'd-none' }}" id="layoutConfigWrapper">
            <label class="form-label fw-bold text-dark">Layout Configuration (JSON Array) *</label>
            <textarea name="layout_config" rows="6" class="form-control font-monospace" style="font-size: 0.85rem;" placeholder='[{"key":"gallery","enabled":true},{"key":"title_price","enabled":true},{"key":"buy_buttons","enabled":true}]'>{{ old('layout_config', !empty($product->layout_config) ? (is_array($product->layout_config) ? json_encode($product->layout_config, JSON_PRETTY_PRINT) : $product->layout_config) : '') }}</textarea>
            <small class="text-secondary mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Requires: <code>gallery</code>, <code>title_price</code>, <code>buy_buttons</code></small>
        </div>

        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0">Variants</h2>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addVariant">Add variant</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="variantTable">
                <thead><tr>
                    <th>ID</th><th>Title *</th><th>SKU</th><th>Retail *</th><th>Reseller</th><th>Bulk</th><th>Compare</th><th>Stock *</th><th>Wt(g)</th><th>Track</th><th>Active</th>
                </tr></thead>
                <tbody>
                @foreach (old('variants', $product->variants->map(fn ($v) => $v->toArray())->all()) as $i => $row)
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="variants[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}" readonly tabindex="-1"></td>
                        <td><input type="text" class="form-control form-control-sm" name="variants[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" required></td>
                        <td><input type="text" class="form-control form-control-sm" name="variants[{{ $i }}][sku]" value="{{ $row['sku'] ?? '' }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $i }}][price_retail]" value="{{ $row['price_retail'] ?? '' }}" required></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $i }}][price_reseller]" value="{{ $row['price_reseller'] ?? '' }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $i }}][price_bulk]" value="{{ $row['price_bulk'] ?? '' }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[{{ $i }}][compare_at_price]" value="{{ $row['compare_at_price'] ?? '' }}"></td>
                        <td><input type="number" class="form-control form-control-sm" name="variants[{{ $i }}][stock_qty]" value="{{ $row['stock_qty'] ?? 0 }}" required></td>
                        <td><input type="number" class="form-control form-control-sm" name="variants[{{ $i }}][weight_grams]" value="{{ $row['weight_grams'] ?? '' }}"></td>
                        <td class="text-center"><input type="hidden" name="variants[{{ $i }}][track_inventory]" value="0"><input type="checkbox" name="variants[{{ $i }}][track_inventory]" value="1" @checked($row['track_inventory'] ?? true)></td>
                        <td class="text-center"><input type="hidden" name="variants[{{ $i }}][is_active]" value="0"><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked($row['is_active'] ?? true)></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @error('variants')<div class="text-danger small">{{ $message }}</div>@enderror

        <hr class="my-4">
        <h2 class="h6">Images</h2>
        <div class="row g-2 mb-3">
            @foreach ($product->images as $img)
                <div class="col-6 col-md-3">
                    <div class="border rounded p-2">
                        <img src="{{ asset('storage/'.$img->path) }}" class="img-fluid mb-2" alt="">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_image_ids[]" value="{{ $img->id }}" id="rm{{ $img->id }}">
                            <label class="form-check-label small" for="rm{{ $img->id }}">Remove</label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mb-3">
            <label class="form-label">Add images</label>
            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-link">Back</a>
    </form>

    <template id="variantRowTpl">
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="variants[__I__][id]" value="" readonly tabindex="-1"></td>
            <td><input type="text" class="form-control form-control-sm" name="variants[__I__][title]" required></td>
            <td><input type="text" class="form-control form-control-sm" name="variants[__I__][sku]"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[__I__][price_retail]" required></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[__I__][price_reseller]"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[__I__][price_bulk]"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[__I__][compare_at_price]"></td>
            <td><input type="number" class="form-control form-control-sm" name="variants[__I__][stock_qty]" value="0" required></td>
            <td><input type="number" class="form-control form-control-sm" name="variants[__I__][weight_grams]"></td>
            <td class="text-center"><input type="hidden" name="variants[__I__][track_inventory]" value="0"><input type="checkbox" name="variants[__I__][track_inventory]" value="1" checked></td>
            <td class="text-center"><input type="hidden" name="variants[__I__][is_active]" value="0"><input type="checkbox" name="variants[__I__][is_active]" value="1" checked></td>
        </tr>
    </template>
@endsection

@push('scripts')
<script>
(function () {
    const tbody = document.querySelector('#variantTable tbody');
    const tpl = document.getElementById('variantRowTpl').innerHTML;
    let idx = tbody.querySelectorAll('tr').length;
    document.getElementById('addVariant').addEventListener('click', function () {
        const html = tpl.replace(/__I__/g, idx++);
        tbody.insertAdjacentHTML('beforeend', html);
    });

    const toggleBtn = document.getElementById('useCustomLayoutBtn');
    const wrap = document.getElementById('layoutConfigWrapper');
    if (toggleBtn && wrap) {
        toggleBtn.addEventListener('change', function() {
            if (this.checked) wrap.classList.remove('d-none');
            else wrap.classList.add('d-none');
        });
    }
})();
</script>
@endpush
