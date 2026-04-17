@php
    $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
    $subtext = ($product->meta['buy_now_subtext'] ?? null) ?: ($copy['buy_now_subtext'] ?? '');
    
    // Volume Pricing: decode from meta
    $volumePricingRaw = $product->meta['volume_pricing'] ?? null;
    $volumePricing = [];
    if (is_array($volumePricingRaw)) {
        $volumePricing = $volumePricingRaw;
    } elseif (is_string($volumePricingRaw) && !empty($volumePricingRaw)) {
        $volumePricing = json_decode($volumePricingRaw, true) ?: [];
    }
    
    // Get the base variant price for volume pricing calculations
    $baseVariantPrice = $selectedVariant ? (float)($variantPrices[$selectedVariant->id]['display'] ?? $selectedVariant->price_retail) : 0;
@endphp

{{-- Volume Pricing (Bundle Deals) — admin controlled via meta[volume_pricing] --}}
@if(!empty($volumePricing))
<div class="sf-pdp-info-block" id="volumePricingBlock">
    <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--color-text-muted); margin-bottom: 12px; font-weight: 600;">Choose Your Bundle</div>
    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        <style>
            .sf-bundle-option {
                border: 2px solid var(--color-border);
                border-radius: var(--radius-sm);
                padding: 10px 12px;
                text-align: center;
                transition: all 0.2s;
                background: transparent;
            }
            .sf-bundle-option.active {
                border-color: var(--color-gold);
                background: rgba(201,168,76,0.05);
            }
        </style>
        @foreach($volumePricing as $idx => $bundle)
        @php
            $bundleQty = (int) ($bundle['qty'] ?? 1);
            $bundleDiscount = (float) ($bundle['discount_pct'] ?? 0);
            $bundlePrice = $baseVariantPrice * $bundleQty * (1 - $bundleDiscount / 100);
        @endphp
        <label style="flex: 1; min-width: 100px; cursor: pointer;">
            <input type="radio" name="bundle_qty" value="{{ $bundleQty }}" class="d-none bundle-qty-radio" {{ $idx === 0 ? 'checked' : '' }}
                   onchange="document.querySelector('[name=qty]').value = this.value; document.querySelectorAll('.sf-bundle-option').forEach(e => e.classList.remove('active')); this.nextElementSibling.classList.add('active');">
            <div class="sf-bundle-option {{ $idx === 0 ? 'active' : '' }}">
                @if(!empty($bundle['badge']))
                    <div style="font-size: 10px; font-weight: 700; color: var(--color-success); text-transform: uppercase; margin-bottom: 4px;">{{ $bundle['badge'] }}</div>
                @endif
                <div style="font-size: 13px; font-weight: 600; color: var(--color-text-primary);">{{ $bundle['label'] ?? $bundleQty.' Pack' }}</div>
                <div style="font-size: 14px; font-weight: 700; color: var(--color-gold); margin-top: 4px;">
                    {{ config('commerce.currency_symbol', '₹') }}{{ number_format($bundlePrice, 0) }}
                </div>
                @if($bundleDiscount > 0)
                    <div style="font-size: 10px; color: var(--color-success); margin-top: 2px;">Save {{ $bundleDiscount }}%</div>
                @endif
            </div>
        </label>
        @endforeach
    </div>
</div>
@endif

<div class="sf-pdp-info-block">
    <div style="display: flex; gap: 16px;">
        @if(empty($volumePricing))
        <div class="sf-qty">
            <button type="button" onclick="const inpv=this.nextElementSibling; if(inpv.value>1) inpv.value--;"><i class="bi bi-dash"></i></button>
            <input type="number" name="qty" value="1" min="1" max="9999" required readonly>
            <button type="button" onclick="const inpv=this.previousElementSibling; inpv.value++;"><i class="bi bi-plus"></i></button>
        </div>
        @else
        {{-- Volume pricing selected — qty is driven by bundle selection --}}
        <input type="hidden" name="qty" value="1">
        @endif
        <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
            {{-- Fix #7: Buy Now = DOMINANT CTA (solid fill, primary) --}}
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <button type="button" class="sf-pdp-add" id="buyNowBtn" onclick="
                    if (typeof fbq === 'function') {
                        var p = document.getElementById('priceLabel') ? document.getElementById('priceLabel').innerText.replace(/[^0-9.]/g, '') : 0;
                        fbq('track', 'AddToCart', { value: parseFloat(p), currency: 'INR', content_ids: [document.getElementById('hidden_variant_id').value], content_type: 'product' });
                        fbq('track', 'InitiateCheckout', { value: parseFloat(p), currency: 'INR', num_items: document.querySelector('[name=qty]').value });
                    }
                    document.getElementById('redirectInput').value='checkout'; 
                    setTimeout(() => document.getElementById('productForm').submit(), 150);
                " style="margin-top:0;">Buy Now</button>
                @if($subtext)
                    <div style="text-align: center; color: var(--color-text-muted); font-size: 11px;">{{ $subtext }}</div>
                @endif
            </div>
            {{-- Fix #7: Add to Cart = SECONDARY (outlined) --}}
            <button type="submit" class="sf-pdp-buy" id="addToCartBtn" style="margin-top:0;"><i class="bi bi-bag-plus me-1"></i> Add to Cart</button>
        </div>
    </div>
</div>

