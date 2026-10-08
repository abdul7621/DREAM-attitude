@extends('layouts.admin')
@section('title', 'Edit Influencer — ' . $influencer->name)
@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.influencers.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="h4 mb-0">Edit Influencer: {{ $influencer->name }}</h1>
        <p class="text-muted small mb-0">Update profile details, assigned coupon, or commission rates.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4">
            <form action="{{ route('admin.influencers.update', $influencer) }}" method="POST">
                @csrf
                @method('PUT')

                <h6 class="text-uppercase fw-bold text-muted small mb-3"><i class="bi bi-person-badge me-1"></i> Account & Personal Information</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Influencer Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $influencer->name) }}" required>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Instagram / Social Handle</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" name="instagram_handle" class="form-control" value="{{ old('instagram_handle', $influencer->instagram_handle) }}" placeholder="username">
                        </div>
                        @error('instagram_handle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Login Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $influencer->user?->email) }}" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">New Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Optional new password">
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone / WhatsApp Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $influencer->phone) }}">
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">UPI ID (For Payouts)</label>
                        <input type="text" name="upi_id" class="form-control" value="{{ old('upi_id', $influencer->upi_id) }}">
                        @error('upi_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="text-uppercase fw-bold text-muted small mb-3"><i class="bi bi-percent me-1"></i> Coupon & Commission Configuration</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assign Coupon Code *</label>
                        <select name="coupon_id" class="form-select" required>
                            @foreach($coupons as $coupon)
                                <option value="{{ $coupon->id }}" {{ old('coupon_id', $influencer->coupon_id) == $coupon->id ? 'selected' : '' }}>
                                    {{ $coupon->code }} ({{ $coupon->type === 'percent' ? $coupon->value . '% OFF' : '₹' . $coupon->value . ' FLAT OFF' }})
                                </option>
                            @endforeach
                        </select>
                        @error('coupon_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Commission Type *</label>
                        <select name="commission_type" class="form-select" required>
                            <option value="percent" {{ old('commission_type', $influencer->commission_type) === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="flat" {{ old('commission_type', $influencer->commission_type) === 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Commission Value *</label>
                        <input type="number" step="0.01" min="0" name="commission_value" class="form-control" value="{{ old('commission_value', $influencer->commission_value) }}" required>
                        @error('commission_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $influencer->is_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="isActive">
                        Account Active
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Update Influencer
                    </button>
                    <a href="{{ route('admin.influencers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
