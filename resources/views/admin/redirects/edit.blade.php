@extends('layouts.admin')
@section('title', 'Edit Redirect')
@section('content')
<h1 class="h4 mb-3">Edit Redirect</h1>
<div class="card shadow-sm p-4" style="max-width:500px">
<form action="{{ route('admin.redirects.update', $redirect) }}" method="post">
@csrf @method('PUT')
<div class="mb-3">
    <label class="form-label">From Path *</label>
    <input type="text" name="from_path" class="form-control" value="{{ old('from_path', $redirect->from_path) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">To Path *</label>
    <input type="text" name="to_path" class="form-control" value="{{ old('to_path', $redirect->to_path) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">HTTP Code</label>
    <select name="http_code" class="form-select">
        <option value="301" {{ $redirect->http_code === 301 ? 'selected' : '' }}>301 — Permanent</option>
        <option value="302" {{ $redirect->http_code === 302 ? 'selected' : '' }}>302 — Temporary</option>
    </select>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $redirect->is_active ? 'checked' : '' }}>
    <label class="form-check-label">Active</label>
</div>
<button type="submit" class="btn btn-primary">Update</button>
<a href="{{ route('admin.redirects.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
@endsection
