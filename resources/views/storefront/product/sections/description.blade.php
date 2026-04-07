@if ($product->description)
    <div class="mb-4 pt-3 mt-4 border-top">
        <h6 class="fw-bold fs-5 mb-3"><i class="bi bi-card-text me-2"></i> Description</h6>
        <div class="text-secondary" style="line-height: 1.7; font-size: 0.95rem;">{!! $product->description !!}</div>
    </div>
@endif
