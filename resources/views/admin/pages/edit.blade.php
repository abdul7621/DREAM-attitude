@extends('layouts.admin')
@section('title', 'Edit Page')
@section('content')
<h1 class="h4 mb-3">Edit Page</h1>
<div class="card shadow-sm p-4">
<form action="{{ route('admin.pages.update', $page) }}" method="post">
@csrf @method('PUT')
<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control" rows="10">{{ old('content', $page->content) }}</textarea>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">SEO Title</label>
        <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $page->seo_title) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">SEO Description</label>
        <input type="text" name="seo_description" class="form-control" value="{{ old('seo_description', $page->seo_description) }}">
    </div>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }}>
    <label class="form-check-label">Active / Published</label>
</div>
<button type="submit" class="btn btn-primary">Update Page</button>
<a href="{{ route('admin.pages.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
@endsection
