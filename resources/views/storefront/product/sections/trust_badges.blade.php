@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
    $deliveryPromise = ($product->meta['delivery_promise'] ?? null) ?: ($copy['delivery_promise'] ?? 'Fast Delivery');
@endphp
<div class="sf-trust-row" style="margin-top: 16px;">
    <span><i class="bi bi-shield-check"></i> Secure Checkout</span>
    <span><i class="bi bi-truck"></i> {{ $deliveryPromise }}</span>
    <span><i class="bi bi-patch-check"></i> Genuine Quality</span>
    @if($codEnabled ?? true)
        <span><i class="bi bi-cash"></i> COD Available</span>
    @endif
</div>
