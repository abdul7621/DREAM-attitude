<style>
.sf-review-header-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    margin-bottom: 40px;
}
@media (min-width: 768px) {
    .sf-review-header-grid {
        grid-template-columns: 280px 1fr;
    }
}
.sf-star-row {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    margin-bottom: 8px;
}
.sf-star-label-text {
    width: 50px;
    color: var(--color-text-secondary);
    white-space: nowrap;
}
.sf-star-bar-bg {
    flex-grow: 1;
    height: 8px;
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}
.sf-star-bar-fill {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    background-color: var(--color-gold);
    border-radius: 4px;
}
.sf-star-count-text {
    width: 30px;
    text-align: right;
    color: var(--color-text-muted);
}
.sf-review-form-container {
    background: var(--color-bg-elevated);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 24px;
    margin-top: 32px;
}
.sf-review-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.review-thumb:hover {
    opacity: 0.85;
    transform: scale(1.03);
}
.review-thumb {
    transition: all 0.2s ease;
}
</style>

<section class="sf-section" style="border-top:1px solid var(--color-border);">
    <div class="sf-container">

        {{-- Review Header & Rating Breakdown --}}
        <div class="sf-review-header-grid">
            <div>
                <h2 class="sf-section-title" style="margin-bottom: 12px;">Customer Reviews</h2>
                @if ($reviewCount > 0)
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="color:var(--color-gold);font-size:36px;font-weight:700;">{{ number_format($avgRating, 1) }}</span>
                        <div>
                            <div style="color:var(--color-gold);font-size:16px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <div style="color:var(--color-text-muted);font-size:12px;margin-top:2px;">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</div>
                        </div>
                    </div>
                @else
                    <p style="color:var(--color-text-muted);font-size:13px;">No reviews yet. Be the first to review!</p>
                @endif
            </div>

            <div>
                @if ($reviewCount > 0)
                    @php
                        $stars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                        foreach ($reviews as $r) {
                            if (isset($stars[$r->rating])) $stars[$r->rating]++;
                        }
                    @endphp
                    <div style="max-width: 450px; display: flex; flex-direction: column; gap: 4px;">
                        @foreach ($stars as $star => $count)
                            @php $pct = ($count / $reviewCount) * 100; @endphp
                            <div class="sf-star-row">
                                <span class="sf-star-label-text">{{ $star }} star</span>
                                <div class="sf-star-bar-bg">
                                    <div class="sf-star-bar-fill" style="width: {{ $pct }}%;"></div>
                                </div>
                                <span class="sf-star-count-text">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Review Cards Slider --}}
        @if ($reviewCount > 0)
            <div class="sf-review-slider" id="productReviewSlider" style="margin-bottom:32px;">
                <div class="sf-review-track" id="productReviewTrack">
                    @foreach ($reviews as $review)
                        <div class="sf-review-slide">
                            <div class="sf-review-card" style="box-shadow:none; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:20px; display:flex; flex-direction:column; justify-content:space-between; height:100%;">
                                
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                        <div class="sf-review-stars" style="color:var(--color-gold); font-size:14px;">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                            @endfor
                                        </div>
                                        @if ($review->verified_purchase)
                                            <span style="color:#25d366; font-size:11px; font-weight:600; display:flex; align-items:center; gap:4px;">
                                                <i class="bi bi-patch-check-fill"></i> Verified Buy
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Hair/Skin Metadata --}}
                                    @if ($review->hair_type || $review->skin_type)
                                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                                            @if ($review->hair_type)
                                                <span style="background:var(--color-bg-elevated); color:var(--color-text-secondary); border:1px solid var(--color-border); font-size:10px; padding:2px 8px; border-radius:4px;">Hair: {{ $review->hair_type }}</span>
                                            @endif
                                            @if ($review->skin_type)
                                                <span style="background:var(--color-bg-elevated); color:var(--color-text-secondary); border:1px solid var(--color-border); font-size:10px; padding:2px 8px; border-radius:4px;">Skin: {{ $review->skin_type }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <p class="sf-review-text" style="margin:0 0 16px 0; font-size:14px; font-style:italic; line-height:1.6; color:var(--color-text-secondary);">"{{ $review->body }}"</p>

                                    {{-- Review Images Gallery --}}
                                    @if (!empty($review->images))
                                        <div style="display:flex; gap:8px; margin-bottom:16px;">
                                            @foreach ($review->images as $path)
                                                <img src="{{ asset('storage/' . $path) }}" alt="Review Photo" class="review-thumb" style="width:48px; height:48px; object-fit:cover; border-radius:4px; border:1px solid var(--color-border); cursor:pointer;" onclick="openReviewLightbox('{{ asset('storage/' . $path) }}')">
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Seller Reply --}}
                                    @if ($review->seller_reply)
                                        <div style="background:var(--color-bg-elevated); border-left:3px solid var(--color-gold); padding:10px 14px; border-radius:0 4px 4px 0; margin-bottom:16px; font-size:12px;">
                                            <div style="font-weight:600; color:var(--color-text-primary); margin-bottom:4px;"><i class="bi bi-chat-left-dots-fill" style="color:var(--color-gold); margin-right:4px;"></i>Store Reply:</div>
                                            <p style="margin:0; color:var(--color-text-secondary); line-height:1.5;">{{ $review->seller_reply }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto; padding-top:12px; border-top:1px solid var(--color-border);">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="sf-review-avatar" style="width:28px; height:28px; font-size:11px;">{{ strtoupper(mb_substr($review->reviewer_name, 0, 1)) }}</div>
                                        <div style="text-align:left;">
                                            <div class="sf-reviewer-name" style="font-size:12px; font-weight:600; color:var(--color-text-primary);">{{ $review->reviewer_name }}</div>
                                            <div style="font-size:10px; color:var(--color-text-muted);">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    
                                    {{-- Upvote --}}
                                    <button type="button" onclick="voteReview({{ $review->id }}, this)" style="background:none; border:none; color:var(--color-text-muted); cursor:pointer; font-size:12px; display:flex; align-items:center; gap:4px; padding:0; transition:color 0.2s;">
                                        <i class="bi bi-hand-thumbs-up"></i> Helpful (<span class="helpful-count">{{ $review->helpful_count }}</span>)
                                    </button>
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
        <div class="sf-review-form-container">
            <h3 style="color:var(--color-text-primary);font-size:16px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;display:flex;align-items:center;">
                <i class="bi bi-pencil-square" style="color:var(--color-gold);margin-right:8px;"></i>Write a Review
            </h3>

            <form action="{{ route('reviews.store', $product) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="sf-review-form-grid">
                    <div>
                        <label class="sf-label">Your Name *</label>
                        <input type="text" name="reviewer_name" class="sf-input" required value="{{ auth()->user()?->name ?? old('reviewer_name') }}">
                    </div>
                    <div>
                        <label class="sf-label">Email Address *</label>
                        <input type="email" name="email" class="sf-input" required value="{{ auth()->user()?->email ?? old('email') }}">
                    </div>
                    <div>
                        <label class="sf-label">Rating *</label>
                        <select name="rating" class="sf-input" required style="appearance:auto;-webkit-appearance:auto; background:#fff;">
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Hair & Skin optional details --}}
                <div class="sf-review-form-grid">
                    <div>
                        <label class="sf-label">Hair Type (Optional)</label>
                        <input type="text" name="hair_type" class="sf-input" placeholder="e.g. Oily, Dry, Fine, Thick, Curly" value="{{ old('hair_type') }}">
                    </div>
                    <div>
                        <label class="sf-label">Skin Type (Optional)</label>
                        <input type="text" name="skin_type" class="sf-input" placeholder="e.g. Sensitive, Combination, Dry, Oily" value="{{ old('skin_type') }}">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="sf-label">Your Review *</label>
                    <textarea name="body" rows="4" class="sf-input" required style="resize:vertical;min-height:100px;line-height:1.6;" placeholder="Tell us how you liked the product..."></textarea>
                </div>

                <div style="margin-bottom:24px;">
                    <label class="sf-label">Add Photos (Optional)</label>
                    <input type="file" name="images[]" class="sf-input" multiple accept="image/*" style="padding: 8px 12px; background: #fff; height: auto;">
                    <div style="font-size:11px; color:var(--color-text-muted); margin-top:4px;">Select up to 3 photos (Max 2MB each).</div>
                </div>

                <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 32px;height:42px;font-size:12px;">
                    <i class="bi bi-send" style="margin-right:6px;"></i>Submit Review
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
            btn.style.color = '#25d366';
        } else {
            btn.style.color = 'var(--color-text-muted)';
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
