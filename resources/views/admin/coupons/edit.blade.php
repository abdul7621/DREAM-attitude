@extends('layouts.admin')
@section('title', 'Edit Coupon')
@section('content')
<h1 class="h4 mb-3">Edit Coupon</h1>
<div class="card shadow-sm p-4" style="max-width:600px">
<form action="{{ route('admin.coupons.update', $coupon) }}" method="post">
@csrf @method('PUT')
<div class="mb-3">
    <label class="form-label">Code *</label>
    <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $coupon->code) }}" required>
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label">Type *</label>
        <select name="type" class="form-select" required>
            <option value="flat" {{ $coupon->type=='flat'?'selected':'' }}>Flat (₹)</option>
            <option value="percent" {{ $coupon->type=='percent'?'selected':'' }}>Percent (%)</option>
        </select>
    </div>
    <div class="col-6">
        <label class="form-label">Value *</label>
        <input type="number" name="value" class="form-control" value="{{ old('value', $coupon->value) }}" step="0.01" min="0" required>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label">Min Subtotal (₹)</label>
        <input type="number" name="min_subtotal" class="form-control" value="{{ old('min_subtotal', $coupon->min_subtotal) }}" step="0.01">
    </div>
    <div class="col-6">
        <label class="form-label">Max Discount (₹)</label>
        <input type="number" name="max_discount" class="form-control" value="{{ old('max_discount', $coupon->max_discount) }}" step="0.01">
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label">Usage Limit</label>
        <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1">
    </div>
    <div class="col-6">
        <label class="form-label">Expires At</label>
        <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d')) }}">
    </div>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $coupon->is_active ? 'checked' : '' }}>
    <label class="form-check-label" for="isActive">Active</label>
</div>
<button type="submit" class="btn btn-primary">Update Coupon</button>
<a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
@endsection
