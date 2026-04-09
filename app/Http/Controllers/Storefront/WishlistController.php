<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Full page wishlist view.
     */
    public function index(): View
    {
        $wishlists = Wishlist::where('user_id', Auth::id())
            ->with(['product.variants', 'product.images'])
            ->latest()
            ->get();

        return view('storefront.account.wishlist', compact('wishlists'));
    }

    /**
     * Toggle wishlist (API call from product cards / product page).
     * Server = single source of truth. Returns JSON with new state.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        $userId = Auth::id();
        $productId = $request->input('product_id');

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'wishlisted' => false,
                'message' => 'Removed from wishlist.',
            ]);
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'wishlisted' => true,
            'message' => 'Added to wishlist.',
        ]);
    }

    /**
     * Remove from wishlist (from wishlist page).
     */
    public function destroy(Wishlist $wishlist): RedirectResponse
    {
        abort_unless($wishlist->user_id === Auth::id(), 403);

        $wishlist->delete();

        return back()->with('success', 'Removed from wishlist.');
    }

    /**
     * API: return list of product IDs in user's wishlist.
     * Used to hydrate heart icons on page load.
     */
    public function apiIds(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([]);
        }

        $ids = Wishlist::where('user_id', Auth::id())
            ->pluck('product_id');

        return response()->json($ids);
    }
}
