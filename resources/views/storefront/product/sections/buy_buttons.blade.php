@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
    $subtext = ($product->meta['buy_now_subtext'] ?? null) ?: ($copy['buy_now_subtext'] ?? '');
@endphp
<div class="mb-4">
    <div class="row g-2">
        <div class="col-3">
            <input type="number" name="qty" value="1" min="1" max="9999" class="form-control text-center h-100" style="min-height: 48px; border-radius: 8px; font-weight: bold; border-color: #e5e7eb;" required>
        </div>
        <div class="col-9 d-flex gap-2">
            <x-sf-button type="submit" variant="outline" size="lg" icon="bi-bag-plus" class="flex-grow-1 sf-btn-cart" id="addToCartBtn" style="border-width: 2px;">
                Add to Cart
            </x-sf-button>
            <div class="flex-grow-1 d-flex flex-column gap-1">
                <x-sf-button type="button" variant="primary" size="lg" icon="bi-lightning-charge-fill" class="w-100" id="buyNowBtn" onclick="document.getElementById('redirectInput').value='checkout'; document.getElementById('productForm').submit();">
                    Buy Now
                </x-sf-button>
                @if($subtext)
                    <div class="text-center text-muted small" style="font-size: 0.75rem;">{{ $subtext }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
