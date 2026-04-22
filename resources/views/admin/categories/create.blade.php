@extends('layouts.admin')

@section('title', 'Add category')

@section('content')
    <h1 class="h4 mb-3">Add category</h1>
    <form action="{{ route('admin.categories.store') }}" method="post" enctype="multipart/form-data" class="bg-white p-3 rounded shadow-sm col-lg-8">
        @csrf
        <div class="mb-3">
            <label class="form-label">Parent</label>
            <select name="parent_id" class="form-select">
                <option value="">—</option>
                @foreach ($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Category Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">Recommended: 800×600px, Max 2MB. Used on homepage category cards.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control" min="0">
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="a1" @checked(old('is_active', true))>
            <label class="form-check-label" for="a1">Active (Enabled on site)</label>
        </div>
        <div class="mb-4 form-check bg-light p-2 border rounded">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" class="form-check-input ms-1" id="f1" @checked(old('is_featured', false))>
            <label class="form-check-label ms-2 text-primary fw-bold" for="f1"><i class="bi bi-star-fill text-warning"></i> Show on Homepage (Featured)</label>
            <div class="form-text ms-2">Check this to display this category specifically on the storefront homepage blocks.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">SEO title</label>
            <input type="text" name="seo_title" value="{{ old('seo_title') }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">SEO description</label>
            <input type="text" name="seo_description" value="{{ old('seo_description') }}" class="form-control" maxlength="512">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Cancel</a>
    </form>
@endsection
