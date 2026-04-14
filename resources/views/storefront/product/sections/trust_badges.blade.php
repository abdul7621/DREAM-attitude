@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
    $deliveryPromise = ($product->meta['delivery_promise'] ?? null) ?: ($copy['delivery_promise'] ?? 'Fast Delivery');
@endphp
{{-- Fix #9: Enhanced trust badges with payment method icons — 2x2 grid layout --}}
<div style="margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px;">
        <i class="bi bi-shield-lock-fill" style="color: var(--color-gold); font-size: 18px; flex-shrink: 0;"></i>
        <span style="font-size: 11px; color: var(--color-text-secondary); line-height: 1.3;">Secure<br><strong style="color: var(--color-text-primary);">Checkout</strong></span>
    </div>
    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px;">
        <i class="bi bi-truck" style="color: var(--color-gold); font-size: 18px; flex-shrink: 0;"></i>
        <span style="font-size: 11px; color: var(--color-text-secondary); line-height: 1.3;">{{ $deliveryPromise }}<br><strong style="color: var(--color-text-primary);">Pan India</strong></span>
    </div>
    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px;">
        <i class="bi bi-phone-fill" style="color: var(--color-gold); font-size: 18px; flex-shrink: 0;"></i>
        <span style="font-size: 11px; color: var(--color-text-secondary); line-height: 1.3;">UPI &<br><strong style="color: var(--color-text-primary);">Cards Accepted</strong></span>
    </div>
    @if($codEnabled ?? true)
    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px;">
        <i class="bi bi-cash-coin" style="color: var(--color-gold); font-size: 18px; flex-shrink: 0;"></i>
        <span style="font-size: 11px; color: var(--color-text-secondary); line-height: 1.3;">Cash on<br><strong style="color: var(--color-text-primary);">Delivery (COD)</strong></span>
    </div>
    @else
    <div style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-elevated); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 10px 14px;">
        <i class="bi bi-patch-check-fill" style="color: var(--color-gold); font-size: 18px; flex-shrink: 0;"></i>
        <span style="font-size: 11px; color: var(--color-text-secondary); line-height: 1.3;">100%<br><strong style="color: var(--color-text-primary);">Genuine Quality</strong></span>
    </div>
    @endif
</div>
