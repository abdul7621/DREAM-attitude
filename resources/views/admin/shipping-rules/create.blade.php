@extends('layouts.admin')
@section('title', 'New Shipping Rule')
@section('content')
<h1 class="h4 mb-3">New Shipping Rule</h1>
<div class="card shadow-sm p-4" style="max-width:600px">
<form action="{{ route('admin.shipping-rules.store') }}" method="post">
@csrf
<div class="mb-3">
    <label class="form-label">Name *</label>
    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Standard Delivery">
</div>
<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label">Type *</label>
        <select name="type" class="form-select" required id="typeSelect">
            <option value="flat">Flat Rate</option>
            <option value="weight">Weight Based</option>
            <option value="pincode">Pincode Based</option>
        </select>
    </div>
    <div class="col-6">
        <label class="form-label">Priority (lower = higher priority)</label>
        <input type="number" name="priority" class="form-control" value="{{ old('priority',0) }}" min="0">
    </div>
</div>

{{-- Flat rate config --}}
<div id="config-flat" class="mb-3">
    <label class="form-label">Flat Shipping Fee (₹)</label>
    <input type="number" name="config[amount]" class="form-control" value="{{ old('config.amount',0) }}" step="0.01" placeholder="0 = free">
    <div class="form-text">Set 0 for free shipping.</div>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
    <label class="form-check-label" for="isActive">Active</label>
</div>
<button type="submit" class="btn btn-primary">Save Rule</button>
<a href="{{ route('admin.shipping-rules.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</form>
</div>
@endsection
