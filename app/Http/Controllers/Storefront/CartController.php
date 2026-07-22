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

    public function data(): \Illuminate\Http\JsonResponse
    {
        $lines = $this->cart->linesWithPricing();
        $totals = $this->cart->computeTotals('');
        
        $items = [];
        foreach ($lines as $row) {
            $compareAt = $row['variant']->compare_at_price;
            $unitPrice = (float) $row['unit_price'];
            $showMrp = $compareAt && (float) $compareAt > $unitPrice;
            $lineCompareTotal = $compareAt ? ($compareAt * $row['item']->qty) : null;

            $items[] = [
                'item_id' => $row['item']->id,
                'variant_id' => $row['variant']->id,
                'name' => $row['product']->name,
                'variant' => $row['variant']->title,
                'url' => route('product.show', $row['product']->slug),
                'qty' => $row['item']->qty,
                'unit_price' => $unitPrice,
                'unit_price_formatted' => '₹' . number_format($unitPrice, 0),
                'line_total' => (float) $row['line_total'],
                'line_total_formatted' => '₹' . number_format((float) $row['line_total'], 0),
                'line_compare_total' => $lineCompareTotal ? (float) $lineCompareTotal : null,
                'line_compare_total_formatted' => ($showMrp && $lineCompareTotal) ? '₹' . number_format((float) $lineCompareTotal, 0) : null,
                'image' => $row['product']->primaryImage() ? asset('storage/'.$row['product']->primaryImage()->path) : 'https://placehold.co/100',
            ];
        }

        return response()->json([
            'success' => true,
            'count' => collect($lines)->sum(fn($row) => $row['item']->qty),
            'subtotal' => (float) $totals['subtotal'],
            'subtotal_formatted' => '₹' . number_format((float) $totals['subtotal'], 2),
            'currency' => config('commerce.currency', 'INR'),
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
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
            $dataResponse = $this->data()->getData(true);
            $dataResponse['status'] = 'success';
            $dataResponse['message'] = __('Added to cart.');
            $dataResponse['analytics'] = [
                'currency' => config('commerce.currency', 'INR'),
                'value' => round($unit * $qty, 2),
                'items' => [[
                    'item_id' => $variant->sku ?: 'v'.$variant->id,
                    'item_name' => $variant->product->name,
                    'price' => $unit,
                    'quantity' => $qty,
                ]],
            ];
            return response()->json($dataResponse);
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

    public function update(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $this->cart->updateQty($item, (int) $data['qty']);

        if ($request->wantsJson()) {
            return $this->data();
        }

        return redirect()->route('cart.index')->with('status', __('Cart updated.'));
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->cart->remove($item);

        if ($request->wantsJson()) {
            return $this->data();
        }

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

    public function capture(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'guest_phone' => ['required', 'string', 'min:10', 'max:15'],
            'lead_source' => ['nullable', 'string', 'max:64'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $phone = preg_replace('/[^0-9]/', '', $data['guest_phone']);
        // Basic normalization for India (assume 10 digits if 10, otherwise just take last 10)
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }
        $hash = hash('sha256', $phone);

        // Check abuse: Has this phone completed an order?
        $hasOrdered = \App\Models\Order::where('phone', 'like', "%{$phone}%")->exists();

        if (isset($data['variant_id'])) {
            $this->cart->add((int) $data['variant_id'], (int) ($data['qty'] ?? 1));
        }

        $cartModel = $this->cart->getCart();
        if ($cartModel) {
            $cartModel->update([
                'guest_phone' => $phone,
                'lead_source' => $data['lead_source'] ?? 'capture_modal',
                'captured_at' => now(),
            ]);

            // Auto-apply coupon if configured and not abused
            $engine = config('commerce.conversion_engine.capture_offer', []);
            if (!empty($engine['offer_coupon_code']) && !$hasOrdered) {
                try {
                    $this->cart->applyCouponCode($engine['offer_coupon_code']);
                    $cartModel->update(['offer_claimed' => $engine['offer_coupon_code']]);
                    session(['offer_unlocked_freeship' => true]);
                } catch (\Exception $e) {
                    // Ignore coupon errors silently during capture
                }
            }
        }

        // Tag visitor
        $visitorId = request()->cookie('da_vid');
        if ($visitorId) {
            \App\Models\Visitor::where('visitor_uuid', $visitorId)->update([
                'normalized_phone' => $phone,
                'phone_hash' => $hash,
                'first_capture_source' => $data['lead_source'] ?? 'capture_modal',
                'last_capture_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'redirect' => route('checkout.create')
        ]);
    }

    public function captureLog(Request $request): \Illuminate\Http\JsonResponse
    {
        $action = $request->input('action');
        \Illuminate\Support\Facades\Log::info("Capture Engine Log: {$action}");
        
        try {
            app(\App\Services\AnalyticsTracker::class)->trackEvent('capture_'.$action, []);
        } catch (\Exception $e) {
            // silent
        }
        
        return response()->json(['success' => true]);
    }
}
