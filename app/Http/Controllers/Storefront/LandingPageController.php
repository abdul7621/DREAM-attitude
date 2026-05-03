<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * Show the landing page (standalone — no storefront layout).
     */
    public function show(string $slug)
    {
        $page = LandingPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Load product details for display
        $productIds = collect($page->products)->pluck('product_id')->unique();
        $products = Product::with(['variants', 'images'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return view('storefront.landing', compact('page', 'products'));
    }

    /**
     * Add all combo products to cart and redirect to checkout.
     */
    public function buy(Request $request, string $slug)
    {
        $page = LandingPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Add each product to cart
        foreach ($page->products as $item) {
            $this->cart->add((int) $item['variant_id'], (int) ($item['qty'] ?? 1));
        }

        // Fire Meta Pixel AddToCart via session flash
        $flashData = [
            'currency' => config('commerce.currency', 'INR'),
            'value'    => (float) $page->offer_price,
            'items'    => collect($page->products)->map(fn($p) => [
                'item_id'  => 'v' . $p['variant_id'],
                'item_name' => $page->title,
                'price'    => (float) $page->offer_price,
                'quantity' => $p['qty'] ?? 1,
            ])->toArray(),
        ];

        return redirect()->route('checkout.create')
            ->with('status', 'Kit added to cart!')
            ->with('analytics_add_to_cart', $flashData);
    }
}
