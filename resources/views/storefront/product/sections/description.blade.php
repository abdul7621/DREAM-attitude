@if ($product->description)
    <div class="sf-section-content mb-5" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-surface); padding: 32px; border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <h2 class="sf-section-title" style="font-size: 1.5rem; text-align: left; margin-bottom: 24px;"><i class="bi bi-card-text me-2 text-gold"></i> Product Details</h2>
        <div class="text-secondary" style="line-height: 1.8; font-size: 1rem;">{!! $product->description !!}</div>
    </div>
@endif
