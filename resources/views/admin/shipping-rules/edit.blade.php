@extends('layouts.admin')
@section('title', 'Edit Shipping Rule')
@section('content')
<h1 class="h4 mb-3">Edit Shipping Rule</h1>
<div class="card shadow-sm p-4" style="max-width:600px">
<form action="{{ route('admin.shipping-rules.update', $shippingRule) }}" method="post">
@csrf @method('PUT')
<div class="mb-3">
    <label class="form-label">Name *</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $shippingRule->name) }}" required>
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label">Type *</label>
        <select name="type" class="form-select" required>
            <option value="flat" {{ $shippingRule->type==='flat'?'selected':'' }}>Flat Rate</option>
            <option value="weight" {{ $shippingRule->type==='weight'?'selected':'' }}>Weight Based</option>
            <option value="pincode" {{ $shippingRule->type==='pincode'?'selected':'' }}>Pincode Based</option>
        </select>
    </div>
    <div class="col-6">
        <label class="form-label">Priority</label>
        <input type="number" name="priority" class="form-control" value="{{ old('priority', $shippingRule->priority) }}" min="0">
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Config (JSON)</label>
    <textarea name="config[raw_json]" class="form-control" rows="4">{{ json_encode($shippingRule->config, JSON_PRETTY_PRINT) }}</textarea>
    <div class="form-text">e.g. {"amount":49} for flat, {"bands":[[0,500,49],[500,1000,29]]} for weight.</div>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $shippingRule->is_active ? 'checked' : '' }}>
    <label class="form-check-label" for="isActive">Active</label>
</div>
<button type="submit" class="btn btn-primary">Update Rule</button>
<a href="{{ route('admin.shipping-rules.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
@endsection
