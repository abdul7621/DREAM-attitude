<section class="sf-section" style="border-top:1px solid var(--color-border);">
    <div class="sf-container">

        {{-- Review Header --}}
        <div style="margin-bottom:32px;">
            <h2 class="sf-section-title">Customer Reviews</h2>
            @if ($reviewCount > 0)
                <div style="display:flex;align-items:center;gap:12px;margin-top:12px;">
                    <span style="color:var(--color-gold);font-size:28px;font-weight:600;">{{ number_format($avgRating, 1) }}</span>
                    <div>
                        <div style="color:var(--color-gold);font-size:16px;">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <div style="color:var(--color-text-muted);font-size:12px;">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Review Cards --}}
        @if ($reviewCount > 0)
            <div class="sf-review-slider" id="productReviewSlider" style="margin-bottom:32px;">
                <div class="sf-review-track" id="productReviewTrack">
                    @foreach ($reviews as $review)
                        <div class="sf-review-slide">
                            <div class="sf-review-card" style="box-shadow:none;">
                                <div class="sf-review-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <p class="sf-review-text">"{{ $review->body }}"</p>
                                <div class="sf-reviewer-row">
                                    <div class="sf-review-avatar">{{ strtoupper(mb_substr($review->reviewer_name, 0, 1)) }}</div>
                                    <div style="text-align:left;">
                                        <div class="sf-reviewer-name">{{ $review->reviewer_name }}</div>
                                        <div class="sf-reviewer-role">Beauty Enthusiast</div>
                                        @if ($review->verified_purchase)
                                            <div style="color:var(--color-success);font-size:11px;margin-top:2px;">
                                                <i class="bi bi-patch-check-fill"></i> Verified
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div style="text-align:center;padding:32px 20px;background:var(--color-bg-surface);border:1px solid var(--color-border);border-radius:var(--radius-md);margin-bottom:32px;">
                <i class="bi bi-chat-square-text" style="font-size:32px;color:var(--color-gold);display:block;margin-bottom:12px;"></i>
                <p style="color:var(--color-text-muted);font-size:14px;margin:0;">No reviews yet. Be the first to review!</p>
            </div>
        @endif

        {{-- Write a Review Form --}}
        <div style="background:var(--color-bg-elevated);border:1px solid var(--color-border);border-radius:var(--radius-md);padding:24px;">
            <h3 style="color:var(--color-text-primary);font-size:16px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;">
                <i class="bi bi-pencil-square" style="color:var(--color-gold);margin-right:8px;"></i>Write a Review
            </h3>

            <form action="{{ route('reviews.store', $product) }}" method="post">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:16px;">
                    <div>
                        <label class="sf-label">Your Name</label>
                        <input type="text" name="reviewer_name" class="sf-input" required value="{{ auth()->user()?->name ?? old('reviewer_name') }}">
                    </div>
                    <div>
                        <label class="sf-label">Email</label>
                        <input type="email" name="email" class="sf-input" required value="{{ auth()->user()?->email ?? old('email') }}">
                    </div>
                    <div>
                        <label class="sf-label">Rating</label>
                        <select name="rating" class="sf-input" required style="appearance:auto;-webkit-appearance:auto;">
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:20px;">
                    <label class="sf-label">Your Review</label>
                    <textarea name="body" rows="4" class="sf-input" required style="resize:vertical;min-height:100px;line-height:1.6;" placeholder="Share your experience...">{{ old('body') }}</textarea>
                </div>
                <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 32px;height:42px;font-size:12px;">
                    <i class="bi bi-send" style="margin-right:6px;"></i>Submit Review
                </button>
            </form>
        </div>

    </div>
</section>

<script>
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
