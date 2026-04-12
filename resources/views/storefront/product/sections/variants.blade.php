<div class="sf-pdp-info-block">
    <label class="variant-label">Select Variant</label>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        @foreach ($product->variants as $index => $v)
            @php 
                $p = $variantPrices[$v->id]; 
                $vImg = $v->images->first()?->url() ?? '';
            @endphp
            <button type="button" 
                    class="variant-btn sf-variant-btn {{ $index === 0 && (! $v->track_inventory || $v->stock_qty > 0) ? 'active' : '' }} {{ ($v->track_inventory && $v->stock_qty <= 0) ? 'disabled' : '' }}" 
                    data-id="{{ $v->id }}" 
                    data-price="{{ $p['display'] }}" 
                    data-compare="{{ $p['compare'] ?? '' }}"
                    data-name="{{ $v->title }}"
                    data-stock="{{ $v->track_inventory ? $v->stock_qty : 999 }}" 
                    data-track="{{ $v->track_inventory ? '1' : '0' }}"
                    data-img="{{ $vImg }}"
                    @if($v->track_inventory && $v->stock_qty <= 0) disabled @endif>
                {{ $v->title }}
            </button>
        @endforeach
    </div>
    <input type="hidden" name="variant_id" id="hidden_variant_id" value="{{ $product->variants->first()?->id }}">
</div>
