@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
    $subtext = ($product->meta['buy_now_subtext'] ?? null) ?: ($copy['buy_now_subtext'] ?? '');
@endphp
<div class="sf-pdp-info-block">
    <div style="display: flex; gap: 16px;">
        <div class="sf-qty">
            <button type="button" onclick="const inpv=this.nextElementSibling; if(inpv.value>1) inpv.value--;"><i class="bi bi-dash"></i></button>
            <input type="number" name="qty" value="1" min="1" max="9999" required readonly>
            <button type="button" onclick="const inpv=this.previousElementSibling; inpv.value++;"><i class="bi bi-plus"></i></button>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
            {{-- Fix #7: Buy Now = DOMINANT CTA (solid fill, primary) --}}
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <button type="button" class="sf-pdp-add" id="buyNowBtn" onclick="document.getElementById('redirectInput').value='checkout'; document.getElementById('productForm').submit();" style="margin-top:0;">Buy Now</button>
                @if($subtext)
                    <div style="text-align: center; color: var(--color-text-muted); font-size: 11px;">{{ $subtext }}</div>
                @endif
            </div>
            {{-- Fix #7: Add to Cart = SECONDARY (outlined) --}}
            <button type="submit" class="sf-pdp-buy" id="addToCartBtn" style="margin-top:0;"><i class="bi bi-bag-plus me-1"></i> Add to Cart</button>
        </div>
    </div>
</div>
