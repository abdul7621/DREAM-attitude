<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly PaymentManager $paymentManager
    ) {}

    public function create(): View|RedirectResponse
    {
        $lines = $this->cart->linesWithPricing();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => __('Your cart is empty.')]);
        }

        $postal = old('postal_code', '');
        $totals = $this->cart->computeTotals($postal);
        $activeGateways = $this->paymentManager->activeGateways();

        return view('storefront.checkout', compact('lines', 'totals', 'activeGateways'));
    }

    public function store(Request $request): View|RedirectResponse
    {
        $lines = $this->cart->linesWithPricing();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => __('Your cart is empty.')]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:128'],
            'state' => ['required', 'string', 'max:128'],
            'postal_code' => ['required', 'string', 'max:16'],
            'country' => ['nullable', 'string', 'max:8'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['required', \Illuminate\Validation\Rule::in(\App\Models\PaymentMethod::active()->pluck('name')->toArray())],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if (empty($data['email'])) {
            $data['email'] = 'guest_' . time() . '_' . \Illuminate\Support\Str::random(4) . '@noemail.com';
        }

        $currentUser = \Illuminate\Support\Facades\Auth::user();

        // ── Customer Identity Layer ─────────────────────────────────────
        if (!$currentUser || $currentUser->phone !== $data['phone']) {
            $existingUser = null;
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($data, &$existingUser) {
                    $existingUser = \App\Models\User::where('phone', $data['phone'])
                        ->lockForUpdate()
                        ->first();

                    if (!$existingUser) {
                        $existingUser = \App\Models\User::where('email', $data['email'])
                            ->lockForUpdate()
                            ->first();
                    }

                    if (!$existingUser) {
                        $existingUser = \App\Models\User::create([
                            'name' => $data['customer_name'] ?? 'Guest',
                            'phone' => $data['phone'],
                            'email' => $data['email'],
                            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                        ]);
                    }
                });

                if ($existingUser) {
                    \Illuminate\Support\Facades\Auth::login($existingUser);
                    $this->cart->mergeOnLogin($existingUser);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Customer Identity Layer failed, proceeding as pure guest checkout: " . $e->getMessage());
                // Don't throw, allow the checkout to proceed without a registered user
            }
        }
        // ────────────────────────────────────────────────────────────────

        $cart = $this->cart->getCart();

        try {
            if ($data['payment_method'] === 'cod') {
                $order = $this->orders->createCodOrder($cart, $data);
                return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
            }

            $gateway = $this->paymentManager->driver($data['payment_method']);
            $order = $this->orders->createPendingOnlineOrder($cart, $data);
            
            $paymentData = $gateway->createOrder($order);

            return view('storefront.checkout-pay', [
                'order' => $order,
                'gateway' => $data['payment_method'],
                'paymentData' => $paymentData,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Checkout failed:", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // For online orders: stock was NOT deducted (deduction happens at finalization),
            // so no stock restore is needed. Just mark the order as abandoned.
            if (isset($order) && $order->order_status === \App\Models\Order::ORDER_STATUS_AWAITING_PAYMENT) {
                $order->update([
                    'payment_status' => \App\Models\Order::PAYMENT_STATUS_FAILED,
                    'order_status' => \App\Models\Order::ORDER_STATUS_ABANDONED,
                    'notes' => 'Gateway init failed: ' . mb_substr($e->getMessage(), 0, 200),
                ]);
            }
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
