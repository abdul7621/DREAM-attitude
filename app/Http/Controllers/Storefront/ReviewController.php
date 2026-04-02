<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
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
        ]);

        Review::query()->create([
            'product_id'    => $product->id,
            'user_id'       => Auth::id(),
            'reviewer_name' => $data['reviewer_name'],
            'email'         => Auth::user()?->email,
            'rating'        => $data['rating'],
            'body'          => $data['body'] ?? null,
            'images'        => null,
            'is_approved'   => false, // goes to moderation
            'verified_purchase' => false,
        ]);

        return redirect()->route('product.show', $product)
            ->with('success', 'Review submitted! It will appear after approval.');
    }
}
