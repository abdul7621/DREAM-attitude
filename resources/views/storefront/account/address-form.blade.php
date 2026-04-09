@extends('layouts.account')
@section('title', $address ? 'Edit Address' : 'Add Address')
@section('account-content')
<h1 class="h4 fw-bold mb-4"><i class="bi bi-geo-alt me-2"></i>{{ $address ? 'Edit Address' : 'Add Address' }}</h1>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ $address ? route('account.addresses.update', $address) : route('account.addresses.store') }}" method="post">
            @csrf
            @if ($address) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Label *</label>
                    <select name="label" class="form-select @error('label') is-invalid @enderror" required>
                        @foreach (['Home', 'Office', 'Other'] as $lbl)
                            <option value="{{ $lbl }}" @selected(old('label', $address?->label) === $lbl)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $address?->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $address?->phone) }}" required>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">PIN Code *</label>
                    <input type="text" name="postal_code" id="addr_postal_code" class="form-control @error('postal_code') is-invalid @enderror" value="{{ old('postal_code', $address?->postal_code) }}" maxlength="6" inputmode="numeric" required>
                    @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" id="addr_city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $address?->city) }}" required>
                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">State *</label>
                    <input type="text" name="state" id="addr_state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $address?->state) }}" required>
                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Address Line 1 *</label>
                    <input type="text" name="address_line1" class="form-control @error('address_line1') is-invalid @enderror" value="{{ old('address_line1', $address?->address_line1) }}" placeholder="House/Flat No., Building Name" required>
                    @error('address_line1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $address?->address_line2) }}" placeholder="Street/Area/Landmark (Optional)">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_default" value="0">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $address?->is_default))>
                        <label class="form-check-label" for="is_default">Set as default address</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $address ? 'Update' : 'Save' }} Address</button>
                    <a href="{{ route('account.addresses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const pin = document.getElementById('addr_postal_code');
    const city = document.getElementById('addr_city');
    const state = document.getElementById('addr_state');
    if (!pin || !city || !state) return;
    pin.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 6) {
            fetch('https://api.postalpincode.in/pincode/' + this.value)
                .then(r => r.json())
                .then(d => {
                    if (d[0].Status === 'Success') {
                        city.value = d[0].PostOffice[0].District;
                        state.value = d[0].PostOffice[0].State;
                    }
                }).catch(() => {});
        }
    });
})();
</script>
@endpush
@endsection
