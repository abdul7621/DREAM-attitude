@extends('layouts.account')
@section('title', 'My Addresses')
@section('account-content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <h1 style="color:white;font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin:0;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-geo-alt" style="color:var(--color-gold);"></i>Addresses
    </h1>
    @if ($addresses->count() < 5)
        <a href="{{ route('account.addresses.create') }}" class="sf-btn-primary" style="width:auto;padding:0 20px;height:36px;display:inline-flex;align-items:center;gap:6px;font-size:11px;text-decoration:none;margin-top:0;">
            <i class="bi bi-plus-lg"></i>Add Address
        </a>
    @endif
</div>

@if ($addresses->isEmpty())
    <div class="sf-account-card" style="text-align:center;padding:48px 20px;color:var(--color-text-muted);">
        <i class="bi bi-geo-alt" style="font-size:32px;display:block;margin-bottom:12px;color:var(--color-gold);"></i>
        No saved addresses. <a href="{{ route('account.addresses.create') }}" style="text-decoration:none;color:var(--color-gold);font-weight:600;">Add your first address →</a>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        @foreach ($addresses as $address)
            <div class="sf-account-card" style="{{ $address->is_default ? 'border-color:var(--color-gold);' : '' }}">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <span style="background:var(--color-bg-elevated);color:var(--color-text-secondary);padding:3px 8px;border-radius:var(--radius-sm);font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">{{ $address->label }}</span>
                        @if ($address->is_default)
                            <span style="background:var(--color-gold);color:#0a0a0a;padding:3px 8px;border-radius:var(--radius-sm);font-size:10px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Default</span>
                        @endif
                    </div>
                    <div style="position:relative;">
                        <button type="button" style="background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:16px;padding:4px;" onclick="this.nextElementSibling.classList.toggle('sf-dropdown-visible')">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div class="sf-address-dropdown" style="display:none;position:absolute;right:0;top:100%;background:var(--color-bg-elevated);border:1px solid var(--color-border);border-radius:var(--radius-md);min-width:150px;z-index:10;overflow:hidden;">
                            <a href="{{ route('account.addresses.edit', $address) }}" style="display:block;padding:10px 16px;font-size:12px;color:white;text-decoration:none;transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.05)'" onmouseleave="this.style.background='transparent'">
                                <i class="bi bi-pencil" style="margin-right:6px;color:var(--color-gold);"></i>Edit
                            </a>
                            @if (!$address->is_default)
                                <form action="{{ route('account.addresses.default', $address) }}" method="post">
                                    @csrf
                                    <button type="submit" style="display:block;width:100%;padding:10px 16px;font-size:12px;color:white;background:none;border:none;text-align:left;cursor:pointer;transition:background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.05)'" onmouseleave="this.style.background='transparent'">
                                        <i class="bi bi-star" style="margin-right:6px;color:var(--color-gold);"></i>Set Default
                                    </button>
                                </form>
                            @endif
                            <div style="border-top:1px solid var(--color-border);"></div>
                            <form action="{{ route('account.addresses.destroy', $address) }}" method="post" onsubmit="return confirm('Delete this address?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="display:block;width:100%;padding:10px 16px;font-size:12px;color:var(--color-error);background:none;border:none;text-align:left;cursor:pointer;transition:background 0.2s;" onmouseenter="this.style.background='rgba(197,48,48,0.1)'" onmouseleave="this.style.background='transparent'">
                                    <i class="bi bi-trash" style="margin-right:6px;"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <p style="color:white;font-weight:500;font-size:14px;margin-bottom:6px;">{{ $address->name }}</p>
                <p style="color:var(--color-text-muted);font-size:13px;margin-bottom:4px;">{{ $address->address_line1 }}@if($address->address_line2), {{ $address->address_line2 }}@endif</p>
                <p style="color:var(--color-text-muted);font-size:13px;margin-bottom:4px;">{{ $address->city }}, {{ $address->state }} — {{ $address->postal_code }}</p>
                <p style="color:var(--color-text-muted);font-size:13px;margin:0;"><i class="bi bi-telephone" style="color:var(--color-gold);font-size:11px;margin-right:4px;"></i>{{ $address->phone }}</p>
            </div>
        @endforeach
    </div>
@endif

<script>
// Simple dropdown toggle
document.addEventListener('click', function(e) {
    document.querySelectorAll('.sf-address-dropdown').forEach(d => {
        if (!d.previousElementSibling.contains(e.target)) {
            d.style.display = 'none';
            d.classList.remove('sf-dropdown-visible');
        }
    });
});
document.querySelectorAll('.sf-address-dropdown').forEach(d => {
    d.previousElementSibling.addEventListener('click', function(e) {
        e.stopPropagation();
        const isVisible = d.style.display === 'block';
        document.querySelectorAll('.sf-address-dropdown').forEach(dd => dd.style.display = 'none');
        d.style.display = isVisible ? 'none' : 'block';
    });
});
</script>
@endsection
