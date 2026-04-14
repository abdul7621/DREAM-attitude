@extends('layouts.account')
@section('title', $address ? 'Edit Address' : 'Add Address')
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-geo-alt" style="color:var(--color-gold);"></i>{{ $address ? 'Edit Address' : 'Add Address' }}
</h1>

<div class="sf-account-card">
    <form action="{{ $address ? route('account.addresses.update', $address) : route('account.addresses.store') }}" method="post">
        @csrf
        @if ($address) @method('PUT') @endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
            <div>
                <label class="sf-label">Label *</label>
                <select name="label" class="sf-input" required>
                    @foreach (['Home', 'Office', 'Other'] as $lbl)
                        <option value="{{ $lbl }}" @selected(old('label', $address?->label) === $lbl)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('label') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Full Name *</label>
                <input type="text" name="name" class="sf-input" value="{{ old('name', $address?->name) }}" required>
                @error('name') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Phone *</label>
                <input type="text" name="phone" class="sf-input" value="{{ old('phone', $address?->phone) }}" required>
                @error('phone') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">PIN Code *</label>
                <input type="text" name="postal_code" id="addr_postal_code" class="sf-input" value="{{ old('postal_code', $address?->postal_code) }}" maxlength="6" inputmode="numeric" required>
                @error('postal_code') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">City *</label>
                <input type="text" name="city" id="addr_city" class="sf-input" value="{{ old('city', $address?->city) }}" required>
                @error('city') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">State *</label>
                <input type="text" name="state" id="addr_state" class="sf-input" value="{{ old('state', $address?->state) }}" required>
                @error('state') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr;gap:16px;margin-top:16px;">
            <div>
                <label class="sf-label">Address Line 1 *</label>
                <input type="text" name="address_line1" class="sf-input" value="{{ old('address_line1', $address?->address_line1) }}" placeholder="House/Flat No., Building Name" required>
                @error('address_line1') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Address Line 2</label>
                <input type="text" name="address_line2" class="sf-input" value="{{ old('address_line2', $address?->address_line2) }}" placeholder="Street/Area/Landmark (Optional)">
            </div>
        </div>
        <div style="margin-top:16px;display:flex;align-items:center;gap:8px;">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $address?->is_default)) style="accent-color:var(--color-gold);width:16px;height:16px;">
            <label for="is_default" style="color:var(--color-text-secondary);font-size:13px;cursor:pointer;">Set as default address</label>
        </div>
        <div style="margin-top:24px;display:flex;gap:12px;align-items:center;">
            <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 32px;height:42px;font-size:12px;">{{ $address ? 'Update' : 'Save' }} Address</button>
            <a href="{{ route('account.addresses.index') }}" style="color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-decoration:none;">Cancel</a>
        </div>
    </form>
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
