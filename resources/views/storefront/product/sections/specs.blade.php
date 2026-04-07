@if (!empty($product->meta['ingredients']) || !empty($product->meta['how_to_use']))
    <div class="mb-4 pt-3 border-top">
        <h6 class="fw-bold fs-5 mb-3"><i class="bi bi-list-ul me-2"></i> Details & Specifications</h6>
        <div class="accordion" id="specsAccordion">
            @if (!empty($product->meta['ingredients']))
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 shadow-none fw-semibold bg-transparent text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIngredients">
                            Ingredients
                        </button>
                    </h2>
                    <div id="collapseIngredients" class="accordion-collapse collapse" data-bs-parent="#specsAccordion">
                        <div class="accordion-body px-0 text-secondary small" style="line-height: 1.6;">
                            {!! nl2br(e($product->meta['ingredients'])) !!}
                        </div>
                    </div>
                </div>
            @endif
            @if (!empty($product->meta['how_to_use']))
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-0 shadow-none fw-semibold bg-transparent text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHowToUse">
                            How to Use
                        </button>
                    </h2>
                    <div id="collapseHowToUse" class="accordion-collapse collapse" data-bs-parent="#specsAccordion">
                        <div class="accordion-body px-0 text-secondary small" style="line-height: 1.6;">
                            {!! nl2br(e($product->meta['how_to_use'])) !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
