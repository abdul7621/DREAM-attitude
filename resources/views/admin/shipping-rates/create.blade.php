@extends('layouts.admin')
@section('title', 'Create Country Shipping Rate')
@section('content')
<h1 class="h4 mb-3">Create Country Shipping Rate</h1>
<div class="card shadow-sm p-4">
    <form action="{{ route('admin.shipping-rates.store') }}" method="post">
        @csrf

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Country Code (ISO 3-Letter) *</label>
                <input type="text" name="country_code" class="form-control" value="{{ old('country_code') }}" required maxlength="3" placeholder="e.g. USA, GBR, IND">
                <div class="form-text">Must be a 3-character ISO country code.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Region/State *</label>
                <input type="text" name="region_state" class="form-control" value="{{ old('region_state', '*') }}" required placeholder="e.g. *, NY, California">
                <div class="form-text">Use <code>*</code> for all states.</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Zip/Postal Code *</label>
                <input type="text" name="zip_postal_code" class="form-control" value="{{ old('zip_postal_code', '*') }}" required placeholder="e.g. *, 90210">
                <div class="form-text">Use <code>*</code> for all postal codes.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Weight (kg and above) *</label>
                <input type="number" step="0.0001" name="weight" class="form-control" value="{{ old('weight', '0.0000') }}" required>
                <div class="form-text">The minimum weight threshold in kg for this rate to apply.</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label">Shipping Price (INR) *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price') }}" required placeholder="e.g. 2500.00">
                <div class="form-text">The flat shipping charge in INR for this weight tier.</div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Create Rate</button>
            <a href="{{ route('admin.shipping-rates.index') }}" class="btn btn-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
