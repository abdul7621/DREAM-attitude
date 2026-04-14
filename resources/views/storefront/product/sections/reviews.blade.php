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
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:16px;margin-bottom:32px;">
                @foreach ($reviews as $review)
                    <div style="background:var(--color-bg-surface);border:1px solid var(--color-border);border-radius:var(--radius-md);padding:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                            <div>
                                <div style="color:var(--color-gold);font-size:14px;margin-bottom:4px;">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <span style="color:var(--color-text-primary);font-size:13px;font-weight:500;">{{ $review->reviewer_name }}</span>
                                @if ($review->verified_purchase)
                                    <span style="color:var(--color-success);font-size:11px;margin-left:8px;"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                @endif
                            </div>
                            <span style="color:var(--color-text-muted);font-size:11px;">{{ $review->created_at->format('d M Y') }}</span>
                        </div>
                        <p style="color:var(--color-text-secondary);font-size:14px;line-height:1.6;margin:0;">"{{ $review->body }}"</p>
                    </div>
                @endforeach
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
