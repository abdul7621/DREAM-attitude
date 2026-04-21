@extends('layouts.admin')
@section('title', 'Shipping Rule Logic Builder')
@section('content')
<h1 class="h4 mb-3">Create Shipping Rule</h1>
<div class="card shadow-sm p-4">
<form action="{{ route('admin.shipping-rules.store') }}" method="post">
@csrf 

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <label class="form-label">Rule Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Free COD for Gujarat">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Priority <small class="text-muted">(Higher executes first)</small></label>
        <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0">
    </div>
    <div class="col-md-3 mb-3 d-flex align-items-end">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActive">Rule is Active</label>
        </div>
    </div>
</div>

<hr>
<h5 class="mb-3 text-primary"><i class="bi bi-funnel"></i> Conditions</h5>
<p class="text-muted small">If ALL conditions match, the action is applied. If you leave this blank, the action applies to EVERY order.</p>

<div id="conditions-container">
    @php
        $conditions = old('conditions', []);
    @endphp
    @foreach($conditions as $i => $cond)
        <div class="row g-2 mb-2 condition-row">
            <div class="col-md-3">
                <select name="conditions[{{$i}}][type]" class="form-select" required>
                    <option value="state" {{ ($cond['type']??'')=='state' ? 'selected':'' }}>State</option>
                    <option value="city" {{ ($cond['type']??'')=='city' ? 'selected':'' }}>City</option>
                    <option value="payment_method" {{ ($cond['type']??'')=='payment_method' ? 'selected':'' }}>Payment Method</option>
                    <option value="order_value" {{ ($cond['type']??'')=='order_value' ? 'selected':'' }}>Cart Subtotal (₹)</option>
                    <option value="weight" {{ ($cond['type']??'')=='weight' ? 'selected':'' }}>Weight (grams)</option>
                    <option value="pincode_prefix" {{ ($cond['type']??'')=='pincode_prefix' ? 'selected':'' }}>Pincode Prefix (e.g. 395)</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="conditions[{{$i}}][operator]" class="form-select" required>
                    <option value="==" {{ ($cond['operator']??'')=='==' ? 'selected':'' }}>Equals (==)</option>
                    <option value="!=" {{ ($cond['operator']??'')=='!=' ? 'selected':'' }}>Not Equals (!=)</option>
                    <option value="in" {{ ($cond['operator']??'')=='in' ? 'selected':'' }}>In List (comma separated)</option>
                    <option value="not_in" {{ ($cond['operator']??'')=='not_in' ? 'selected':'' }}>Not In List</option>
                    <option value=">" {{ ($cond['operator']??'')=='>' ? 'selected':'' }}>Greater Than (>)</option>
                    <option value="<" {{ ($cond['operator']??'')=='<' ? 'selected':'' }}>Less Than (<)</option>
                    <option value=">=" {{ ($cond['operator']??'')=='>=' ? 'selected':'' }}>Greater or Equal (>=)</option>
                    <option value="<=" {{ ($cond['operator']??'')=='<=' ? 'selected':'' }}>Less or Equal (<=)</option>
                </select>
            </div>
            <div class="col-md-5">
                @php
                    $val = is_array($cond['value']??'') ? implode(', ', $cond['value']??'') : ($cond['value']??'');
                @endphp
                <input type="text" name="conditions[{{$i}}][value]" class="form-control" value="{{ $val }}" required placeholder="e.g. Gujarat OR 2000 OR COD">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-condition"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    @endforeach
</div>
<button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addConditionBtn">+ Add Condition</button>

<hr class="mt-4">
<h5 class="mb-3 text-success"><i class="bi bi-lightning"></i> Action</h5>
<div class="row g-3">
    <div class="col-md-6">
        @php
            $actionType = old('action_type', 'flat');
            $actionValue = old('action_value', '0.00');
        @endphp
        <label class="form-label">Cost Type *</label>
        <select name="action_type" class="form-select" required>
            <option value="flat" {{ $actionType === 'flat' ? 'selected' : '' }}>Flat Rate Price (₹)</option>
            <option value="free" {{ $actionType === 'free' ? 'selected' : '' }}>Free Shipping</option>
            <option value="percentage" {{ $actionType === 'percentage' ? 'selected' : '' }}>Percentage of Subtotal (%)</option>
            <option value="per_kg" {{ $actionType === 'per_kg' ? 'selected' : '' }}>Charge Per KG (₹)</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Cost Value *</label>
        <input type="number" step="0.01" name="action_value" class="form-control" value="{{ $actionValue }}" required>
    </div>
</div>

<div class="mt-5">
    <button type="submit" class="btn btn-primary">Create Rule</button>
    <a href="{{ route('admin.shipping-rules.index') }}" class="btn btn-secondary ms-2">Cancel</a>
</div>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let container = document.getElementById('conditions-container');
    let btn = document.getElementById('addConditionBtn');
    let idx = {{ count($conditions) }};

    btn.addEventListener('click', function() {
        let html = `
        <div class="row g-2 mb-2 condition-row">
            <div class="col-md-3">
                <select name="conditions[${idx}][type]" class="form-select" required>
                    <option value="state">State</option>
                    <option value="city">City</option>
                    <option value="payment_method">Payment Method</option>
                    <option value="order_value">Cart Subtotal (₹)</option>
                    <option value="weight">Weight (grams)</option>
                    <option value="pincode_prefix">Pincode Prefix</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="conditions[${idx}][operator]" class="form-select" required>
                    <option value="==">Equals (==)</option>
                    <option value="!=">Not Equals (!=)</option>
                    <option value="in">In List (comma separated)</option>
                    <option value="not_in">Not In List</option>
                    <option value=">">Greater Than (>)</option>
                    <option value="<">Less Than (<)</option>
                    <option value=">=">Greater or Equal (>=)</option>
                    <option value="<=">Less or Equal (<=)</option>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="conditions[${idx}][value]" class="form-control" required placeholder="Value...">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-condition"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        idx++;
    });

    container.addEventListener('click', function(e) {
        let target = e.target.closest('.remove-condition');
        if(target) {
            target.closest('.condition-row').remove();
        }
    });
});
</script>
@endsection
