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
                <label class="form-label">Short description</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}" class="form-control" maxlength="512">
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
                <label class="form-label">Images</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
@endsection
