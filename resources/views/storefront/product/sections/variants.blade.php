<div class="mb-4">
    <label class="form-label fw-semibold mb-2">Select Variant</label>
    <div class="d-flex flex-wrap gap-2 sf-variant-selector">
        @foreach ($product->variants as $index => $v)
            @php $p = $variantPrices[$v->id]; @endphp
            <button type="button" 
                    class="btn sf-variant-btn {{ $index === 0 && (! $v->track_inventory || $v->stock_qty > 0) ? 'active' : '' }}" 
                    data-id="{{ $v->id }}" 
                    data-price="{{ $p['display'] }}" 
                    data-compare="{{ $p['compare'] ?? '' }}"
                    data-stock="{{ $v->track_inventory ? $v->stock_qty : 999 }}" 
                    data-track="{{ $v->track_inventory ? '1' : '0' }}"
                    @if($v->track_inventory && $v->stock_qty <= 0) disabled @endif>
                {{ $v->title }}
            </button>
        @endforeach
    </div>
    {{-- Hidden input to store selected variant for the form --}}
    <input type="hidden" name="variant_id" id="hidden_variant_id" value="{{ $product->variants->first()?->id }}">
</div>

<style>
.sf-variant-btn {
    border: 2px solid #e5e7eb;
    background: #fff;
    color: #374151;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
}
.sf-variant-btn:hover:not(:disabled) {
    border-color: #9ca3af;
    background: #f9fafb;
}
.sf-variant-btn.active {
    border-color: var(--sf-primary, #000);
    color: var(--sf-primary, #000);
    background-color: var(--sf-primary-light, #f8f9fa);
    box-shadow: 0 0 0 1px var(--sf-primary, #000);
}
.sf-variant-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    text-decoration: line-through;
}
</style>
