<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PricingService $pricing
    ) {}

    public function index(): View
    {
        $lines = $this->cart->linesWithPricing();
        $totals = $this->cart->computeTotals('');

        return view('storefront.cart', compact('lines', 'totals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:9999'],
            'redirect' => ['nullable', 'string', 'in:checkout'],
        ]);

        $qty = (int) $data['qty'];
        $this->cart->add((int) $data['variant_id'], $qty);

        $variant = ProductVariant::query()->with('product')->findOrFail((int) $data['variant_id']);
        $unit = (float) $this->pricing->unitPriceForCustomer($variant, auth()->user(), max(1, $qty));

        $redirectUrl = (isset($data['redirect']) && $data['redirect'] === 'checkout') 
            ? route('checkout.create') 
            : url()->previous();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Added to cart.'),
                'total_items' => $this->cart->linesWithPricing()->sum(fn($row) => $row['item']->qty),
                'analytics' => [
                    'currency' => config('commerce.currency', 'INR'),
                    'value' => round($unit * $qty, 2),
                    'items' => [[
                        'item_id' => $variant->sku ?: 'v'.$variant->id,
                        'item_name' => $variant->product->name,
                        'price' => $unit,
                        'quantity' => $qty,
                    ]],
                ]
            ]);
        }

        return redirect($redirectUrl)
            ->with('status', __('Added to cart.'))
            ->with('analytics_add_to_cart', [
                'currency' => config('commerce.currency', 'INR'),
                'value' => round($unit * $qty, 2),
                'items' => [[
                    'item_id' => $variant->sku ?: 'v'.$variant->id,
                    'item_name' => $variant->product->name,
                    'price' => $unit,
                    'quantity' => $qty,
                ]],
            ]);
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $this->cart->updateQty($item, (int) $data['qty']);

        return redirect()->route('cart.index')->with('status', __('Cart updated.'));
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        $this->cart->remove($item);

        return redirect()->route('cart.index')->with('status', __('Item removed.'));
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->cart->applyCouponCode($data['code']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['coupon' => $e->getMessage()]);
        }

        return redirect()->route('cart.index')->with('status', __('Coupon applied.'));
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->cart->removeCoupon();

        return redirect()->route('cart.index')->with('status', __('Coupon removed.'));
    }
}
