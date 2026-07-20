<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()
            ->with(['product', 'user'])
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'is_approved' => 'nullable|boolean',
            'seller_reply' => 'nullable|string|max:2000',
        ]);

        $updateData = [];
        if ($request->has('is_approved')) {
            $updateData['is_approved'] = (bool) $data['is_approved'];
        }
        if ($request->has('seller_reply')) {
            $updateData['seller_reply'] = $data['seller_reply'];
        }

        $review->update($updateData);

        Cache::forget('dashboard_kpi');

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        Cache::forget('dashboard_kpi');

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }
}
