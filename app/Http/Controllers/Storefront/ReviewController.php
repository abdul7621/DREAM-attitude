<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'rating'        => 'required|integer|between:1,5',
            'body'          => 'nullable|string|max:2000',
            'hair_type'     => 'nullable|string|max:100',
            'skin_type'     => 'nullable|string|max:100',
            'images'        => 'nullable|array|max:3',
            'images.*'      => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Auto verify purchase
        $verified = false;
        if (Auth::check()) {
            $verified = \App\Models\Order::where('user_id', Auth::id())
                ->whereIn('order_status', ['delivered', 'completed', 'shipped'])
                ->whereHas('items', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();
        }

        // Upload review attachments
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('reviews', 'public');
                $uploadedImages[] = $path;
            }
        }

        Review::query()->create([
            'product_id'    => $product->id,
            'user_id'       => Auth::id(),
            'reviewer_name' => $data['reviewer_name'],
            'email'         => Auth::user()?->email,
            'rating'        => $data['rating'],
            'body'          => $data['body'] ?? null,
            'hair_type'     => $data['hair_type'] ?? null,
            'skin_type'     => $data['skin_type'] ?? null,
            'images'        => !empty($uploadedImages) ? $uploadedImages : null,
            'is_approved'   => false, // moderation
            'verified_purchase' => $verified,
        ]);

        return redirect()->route('product.show', $product)
            ->with('success', 'Review submitted! It will appear after approval.');
    }

    public function vote(Request $request, Review $review): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Login required'], 401);
        }

        $userId = Auth::id();
        $vote = ReviewVote::where('user_id', $userId)
            ->where('review_id', $review->id)
            ->first();

        if ($vote) {
            $vote->delete();
            $review->decrement('helpful_count');
            return response()->json([
                'voted' => false,
                'helpful_count' => $review->helpful_count
            ]);
        } else {
            ReviewVote::create([
                'user_id' => $userId,
                'review_id' => $review->id,
                'session_id' => session()->getId(),
            ]);
            $review->increment('helpful_count');
            return response()->json([
                'voted' => true,
                'helpful_count' => $review->helpful_count
            ]);
        }
    }
}
