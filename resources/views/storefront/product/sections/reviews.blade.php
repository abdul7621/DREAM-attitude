<section class="sf-section" style="border-top:1px solid var(--color-border);">
    <div class="sf-container">

        {{-- Review Header & Rating Breakdown --}}
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <h2 class="sf-section-title mb-3">Customer Reviews</h2>
                @if ($reviewCount > 0)
                    <div class="d-flex align-items-center gap-3">
                        <span class="display-5 fw-bold" style="color:var(--color-gold);">{{ number_format($avgRating, 1) }}</span>
                        <div>
                            <div style="color:var(--color-gold);font-size:18px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <div class="text-muted small">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-muted small">No reviews yet. Be the first to review!</p>
                @endif
            </div>

            <div class="col-md-8">
                @if ($reviewCount > 0)
                    @php
                        $stars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                        foreach ($reviews as $r) {
                            if (isset($stars[$r->rating])) $stars[$r->rating]++;
                        }
                    @endphp
                    <div class="d-flex flex-column gap-2" style="max-width: 450px;">
                        @foreach ($stars as $star => $count)
                            @php $pct = ($count / $reviewCount) * 100; @endphp
                            <div class="d-flex align-items-center gap-3" style="font-size: 13px;">
                                <span style="width: 50px;" class="text-nowrap">{{ $star }} star</span>
                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background: rgba(0,0,0,0.05);">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: var(--color-gold); border-radius: 4px;"></div>
                                </div>
                                <span style="width: 40px;" class="text-end text-muted">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Review Cards --}}
        @if ($reviewCount > 0)
            <div class="sf-review-slider mb-5" id="productReviewSlider" style="overflow-x: auto; padding-bottom: 15px;">
                <div class="sf-review-track d-flex gap-3" id="productReviewTrack" style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;">
                    @foreach ($reviews as $review)
                        <div class="sf-review-slide" style="flex: 0 0 350px; scroll-snap-align: start;">
                            <div class="sf-review-card h-100 p-4 border rounded bg-white shadow-sm d-flex flex-column" style="border-color: var(--color-border) !important;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="sf-review-stars" style="color:var(--color-gold); font-size: 14px;">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    @if ($review->verified_purchase)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center gap-1 py-1 px-2" style="font-size: 10px; border-radius: 12px;">
                                            <i class="bi bi-patch-check-fill"></i> Verified Buy
                                        </span>
                                    @endif
                                </div>

                                {{-- User Metadata (Hair/Skin Type) --}}
                                @if ($review->hair_type || $review->skin_type)
                                    <div class="mb-3 d-flex flex-wrap gap-2">
                                        @if ($review->hair_type)
                                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 10px; border-radius: 8px;">Hair: {{ $review->hair_type }}</span>
                                        @endif
                                        @if ($review->skin_type)
                                            <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 10px; border-radius: 8px;">Skin: {{ $review->skin_type }}</span>
                                        @endif
                                    </div>
                                @endif

                                <p class="sf-review-text flex-grow-1 text-secondary mb-3" style="font-size: 14px; line-height: 1.6; font-style: italic;">"{{ $review->body }}"</p>

                                {{-- Attached Photos Gallery --}}
                                @if (!empty($review->images))
                                    <div class="d-flex gap-2 mb-3">
                                        @foreach ($review->images as $path)
                                            <img src="{{ asset('storage/' . $path) }}" alt="Review Image" class="rounded border review-thumb" style="width: 48px; height: 48px; object-fit: cover; cursor: pointer;" onclick="openReviewLightbox('{{ asset('storage/' . $path) }}')">
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Seller Response Block --}}
                                @if ($review->seller_reply)
                                    <div class="p-3 bg-light rounded border mb-3" style="font-size: 12px; border-color: rgba(201,168,76,0.2) !important; border-left: 3px solid var(--color-gold) !important;">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-chat-left-dots-fill text-warning me-1"></i> Store Reply:</div>
                                        <p class="mb-0 text-muted">{{ $review->seller_reply }}</p>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top" style="border-color: rgba(0,0,0,0.05) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="sf-review-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-gold); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                                            {{ strtoupper(mb_substr($review->reviewer_name, 0, 1)) }}
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-semibold small" style="color: var(--color-text-primary);">{{ $review->reviewer_name }}</div>
                                            <div class="text-muted" style="font-size: 10px;">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>

                                    {{-- Helpful vote button --}}
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-0 d-flex align-items-center gap-1 helpful-vote-btn" onclick="voteReview({{ $review->id }}, this)" style="font-size: 12px;">
                                        <i class="bi bi-hand-thumbs-up"></i> Helpful (<span class="helpful-count">{{ $review->helpful_count }}</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-5 px-4 mb-5 rounded border" style="background: var(--color-bg-surface); border-color: var(--color-border) !important;">
                <i class="bi bi-chat-square-text text-muted mb-3" style="font-size:32px;"></i>
                <p class="text-muted small mb-0">No reviews yet. Be the first to share your thoughts!</p>
            </div>
        @endif

        {{-- Write a Review Form --}}
        <div class="card shadow-sm border p-4 rounded" style="background: var(--color-bg-elevated); border-color: var(--color-border) !important;">
            <h3 class="h6 fw-bold mb-4 text-uppercase" style="letter-spacing: 0.5px; color: var(--color-text-primary);">
                <i class="bi bi-pencil-square text-warning me-2"></i>Write a Review
            </h3>

            <form action="{{ route('reviews.store', $product) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Your Name *</label>
                        <input type="text" name="reviewer_name" class="form-control" required value="{{ auth()->user()?->name ?? old('reviewer_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Email Address *</label>
                        <input type="email" name="email" class="form-control" required value="{{ auth()->user()?->email ?? old('email') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Rating *</label>
                        <select name="rating" class="form-select" required>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Hair & Skin Type options --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Hair Type (Optional)</label>
                        <input type="text" name="hair_type" class="form-control" placeholder="e.g. Oily, Dry, Fine, Thick, Curly" value="{{ old('hair_type') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Skin Type (Optional)</label>
                        <input type="text" name="skin_type" class="form-control" placeholder="e.g. Sensitive, Combination, Dry, Oily" value="{{ old('skin_type') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Your Review *</label>
                    <textarea name="body" rows="4" class="form-control" required style="resize:vertical;" placeholder="Tell us how you liked the product..."></textarea>
                </div>

                {{-- Image uploads field --}}
                <div class="mb-4">
                    <label class="form-label small fw-bold">Add Photos (Optional)</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <div class="form-text small">Select up to 3 photos (Max 2MB each).</div>
                </div>

                <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-send me-1"></i>Submit Review
                </button>
            </form>
        </div>

    </div>
</section>

{{-- Image Lightbox overlay --}}
<div class="sf-review-lightbox-overlay" id="reviewLightbox" onclick="this.style.display='none';" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
    <img src="" id="reviewLightboxImg" class="img-fluid rounded" style="max-height: 85vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
</div>

<script>
function openReviewLightbox(src) {
    var lb = document.getElementById('reviewLightbox');
    var img = document.getElementById('reviewLightboxImg');
    if (lb && img) {
        img.src = src;
        lb.style.display = 'flex';
    }
}

function voteReview(reviewId, btn) {
    fetch('/reviews/' + reviewId + '/vote', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        var countSpan = btn.querySelector('.helpful-count');
        if (countSpan) countSpan.textContent = data.helpful_count;
        if (data.voted) {
            btn.classList.add('voted');
            btn.style.color = 'var(--color-success)';
        } else {
            btn.classList.remove('voted');
            btn.style.color = '';
        }
    })
    .catch(() => {});
}

document.addEventListener("DOMContentLoaded", function() {
    var slider = document.getElementById('productReviewSlider');
    if (!slider) return;
    var track = document.getElementById('productReviewTrack');
    if (!track) return;
    var timer = null;
    function autoSlide() {
        var maxScroll = track.scrollWidth - track.clientWidth;
        if (maxScroll <= 0) return;
        if (track.scrollLeft + 10 >= maxScroll) { track.scrollTo({ left: 0, behavior: 'smooth' }); }
        else {
            var item = track.querySelector('.sf-review-slide');
            if(!item) return;
            var itemWidth = item.getBoundingClientRect().width;
            var style = window.getComputedStyle(track);
            var gap = parseFloat(style.gap) || 0;
            track.scrollBy({ left: itemWidth + gap, behavior: 'smooth' });
        }
    }
    function start() { stop(); timer = setInterval(autoSlide, 4500); }
    function stop() { if(timer) { clearInterval(timer); timer = null; } }
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('touchstart', stop, {passive: true});
    slider.addEventListener('touchend', start, {passive: true});
    start();
});
</script>
