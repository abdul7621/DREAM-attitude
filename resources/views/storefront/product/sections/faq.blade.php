@if (!empty($product->meta['faq']))
    <div class="sf-section-content mb-5" style="max-width: 800px; margin: 0 auto; background: var(--color-bg-surface); padding: 32px; border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <h2 class="sf-section-title" style="font-size: 1.5rem; text-align: left; margin-bottom: 24px;"><i class="bi bi-question-circle me-2 text-gold"></i> Frequently Asked Questions</h2>
        <div class="text-secondary" style="line-height: 1.8; font-size: 1rem;">
            @if(is_array($product->meta['faq']))
                @foreach($product->meta['faq'] as $faqItem)
                    <div class="mb-3">
                        <strong class="text-primary d-block mb-1">Q: {{ $faqItem['q'] ?? '' }}</strong>
                        <div class="text-muted">A: {!! nl2br(e($faqItem['a'] ?? '')) !!}</div>
                    </div>
                @endforeach
            @else
                {!! nl2br(e($product->meta['faq'])) !!}
            @endif
        </div>
    </div>
@endif
