<section class="sf-reviews mt-5">
    <h2 class="fs-3 fw-bold mb-4">Customer Reviews</h2>

    @if ($reviewCount > 0)
        <div class="sf-review-summary d-flex align-items-center gap-3 mb-5 p-4 bg-light rounded" style="max-width: 400px;">
            <span class="avg-rating display-4 fw-bold text-dark">{{ number_format($avgRating, 1) }}</span>
            <div>
                <div class="stars text-warning fs-5">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                    @endfor
                </div>
                <span class="count text-muted small">Based on {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</span>
            </div>
        </div>

        <div class="row g-4">
        @foreach ($reviews as $review)
            <div class="col-md-6 col-lg-4">
                <div class="sf-review-card h-100 p-4 border rounded shadow-sm">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="review-stars text-warning mb-1" style="font-size: 0.9rem;">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <span class="review-author fw-bold text-dark d-block">{{ $review->reviewer_name }}</span>
                            @if ($review->verified_purchase)
                                <span class="verified-badge small text-success fw-semibold"><i class="bi bi-patch-check-fill"></i> Verified Purchase</span>
                            @endif
                        </div>
                        <span class="review-date text-muted small">{{ $review->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="review-body text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">{{ $review->body }}</p>
                </div>
            </div>
        @endforeach
        </div>
    @else
        <p class="text-muted p-4 bg-light rounded text-center">No reviews yet. Be the first to review!</p>
    @endif

    {{-- Review Form --}}
    <div class="card mt-5 border-0 shadow-sm">
        <div class="card-header bg-dark text-white fw-semibold py-3 fs-5"><i class="bi bi-pencil-square me-2"></i> Write a Review</div>
        <div class="card-body p-4 bg-light pt-4">
            <form action="{{ route('reviews.store', $product) }}" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Your Name</label>
                        <input type="text" name="reviewer_name" class="form-control" required value="{{ auth()->user()?->name ?? old('reviewer_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required value="{{ auth()->user()?->email ?? old('email') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Rating</label>
                        <select name="rating" class="form-select" required style="max-width:200px;">
                            <option value="5">★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Your Review</label>
                        <textarea name="body" class="form-control" rows="4" required placeholder="Share your experience…">{{ old('body') }}</textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark px-5 py-2 fw-semibold"><i class="bi bi-send me-2"></i> Submit Review</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
