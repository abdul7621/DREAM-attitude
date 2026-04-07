@if (!empty($product->meta['faq']))
    <div class="mb-4 pt-3 border-top">
        <h6 class="fw-bold fs-5 mb-3"><i class="bi bi-question-circle me-2"></i> Frequently Asked Questions</h6>
        <div class="text-secondary small" style="line-height: 1.6;">
            {!! nl2br(e($product->meta['faq'])) !!}
        </div>
    </div>
@endif
