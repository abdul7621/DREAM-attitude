@extends('layouts.account')
@section('title', 'Profile')
@section('account-content')
<h1 style="color:white;font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-person" style="color:var(--color-gold);"></i>Profile
</h1>

{{-- Personal Information --}}
<div class="sf-account-card">
    <div style="font-weight:600;color:white;font-size:14px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--color-border);">Personal Information</div>
    <form action="{{ route('account.profile.update') }}" method="post">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:16px;">
            <div>
                <label class="sf-label">Name</label>
                <input type="text" name="name" class="sf-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Email</label>
                <input type="email" name="email" class="sf-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                @error('email') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Phone</label>
                <input type="text" name="phone" class="sf-input @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                @error('phone') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 32px;height:42px;font-size:12px;">Save Changes</button>
        </div>
    </form>
</div>

{{-- Change Password --}}
<div class="sf-account-card">
    <div style="font-weight:600;color:white;font-size:14px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--color-border);">Change Password</div>
    <form action="{{ route('account.password.update') }}" method="post">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:16px;">
            <div>
                <label class="sf-label">Current Password</label>
                <input type="password" name="current_password" class="sf-input @error('current_password') is-invalid @enderror" required>
                @error('current_password') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div style="display:none;">{{-- spacer --}}</div>
            <div>
                <label class="sf-label">New Password</label>
                <input type="password" name="password" class="sf-input @error('password') is-invalid @enderror" required>
                @error('password') <div style="color:var(--color-error);font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="sf-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="sf-input" required>
            </div>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 32px;height:42px;font-size:12px;background:var(--color-bg-elevated);color:var(--color-gold);border:1px solid var(--color-gold);">Update Password</button>
        </div>
    </form>
</div>
@endsection
