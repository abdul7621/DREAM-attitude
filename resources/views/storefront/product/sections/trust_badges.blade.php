@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product'));
    $trustTitle = $product->meta['trust_badges_title'] ?: ($copy['trust_badges_title'] ?: 'Secure Checkout');
    $trustText = $product->meta['trust_badges_text'] ?: ($copy['trust_badges_text'] ?: '100% safe & protected payments');
    $deliveryPromise = $product->meta['delivery_promise'] ?: ($copy['delivery_promise'] ?: 'Estimated: 2-5 Business Days');
@endphp
<div class="sf-trust-badges border rounded p-3 mb-4" style="background-color: var(--sf-bg-light, #f8f9fa);">
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-shield-check text-success fs-4 me-3"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $trustTitle }}</div>
            <div class="small text-muted" style="font-size: 0.8rem;">{{ $trustText }}</div>
        </div>
    </div>
    @if($codEnabled ?? true)
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-cash-stack text-success fs-4 me-3"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">Cash on Delivery Available</div>
            <div class="small text-muted" style="font-size: 0.8rem;">Pay when you receive your order</div>
        </div>
    </div>
    @endif
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-truck fs-4 me-3" style="color: var(--sf-primary, #000);"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">Fast Delivery</div>
            <div class="small text-muted" style="font-size: 0.8rem;">{{ $deliveryPromise }}</div>
        </div>
    </div>
    <div class="d-flex align-items-center">
        <i class="bi bi-patch-check-fill fs-4 me-3" style="color: var(--sf-primary, #000);"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">100% Genuine Resource</div>
            <div class="small text-muted" style="font-size: 0.8rem;">Authentic quality guaranteed</div>
        </div>
    </div>
</div>
