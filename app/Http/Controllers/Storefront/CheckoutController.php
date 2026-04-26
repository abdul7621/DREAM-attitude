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
        $paymentMethod = old('payment_method', '');
        // Zero-Trust Security: We DO NOT use $val['state'] for shipping cost calculation!
        // The backend determines shipping internally inside CartService using the postal code & payment method.
        $totals = $this->cart->computeTotals($postal, $paymentMethod);
        $activeGateways = $this->paymentManager->activeGateways();

        return view('storefront.checkout', compact('lines', 'totals', 'activeGateways'));
    }

    public function store(Request $request): View|RedirectResponse
    {
        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey && \Illuminate\Support\Facades\Cache::has('checkout_idemp_' . $idempotencyKey)) {
            $existingOrderNumber = \Illuminate\Support\Facades\Cache::get('checkout_idemp_' . $idempotencyKey);
            return redirect()->route('order.success', ['orderNumber' => $existingOrderNumber]);
        }

        $lockKey = 'checkout_' . $request->ip() . '_' . session()->getId();
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 15);

        try {
            if (!$lock->block(3)) {
                return back()->withErrors(['checkout' => 'Please wait, your previous request is still processing.'])->withInput();
            }

            $lines = $this->cart->linesWithPricing();
            if ($lines->isEmpty()) {
                return redirect()->route('cart.index')->withErrors(['cart' => __('Your cart is empty.')]);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'customer_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['required', 'regex:/^(?:(?:\+|00)?91[\-\s]?)?[6-9]\d{9}$/'],
                'address_line1' => ['required', 'string', 'max:255'],
                'address_line2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:128'],
                'state' => ['required', 'string', 'max:128'],
                'postal_code' => ['required', 'string', 'max:16'],
                'country' => ['nullable', 'string', 'max:8'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'payment_method' => ['required', \Illuminate\Validation\Rule::in(\App\Models\PaymentMethod::active()->pluck('name')->toArray())],
                'idempotency_key' => ['nullable', 'string', 'max:64'],
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
                            
                            if (!str_contains($data['email'], '@noemail.com')) {
                                session()->flash('account_created_email', $data['email']);
                                try {
                                    \Illuminate\Support\Facades\Password::sendResetLink(['email' => $data['email']]);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning("Could not send password reset to new guest user: " . $e->getMessage());
                                }
                            } else {
                                session()->flash('account_created_phone', $data['phone']);
                            }
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
                    if (!\Illuminate\Support\Facades\Auth::check()) {
                        $guestToken = \Illuminate\Support\Str::random(32);
                        $order->update(['guest_token' => $guestToken]);
                        session(['guest_order_token_' . $order->order_number => $guestToken]);
                    }
                    if ($idempotencyKey) {
                        \Illuminate\Support\Facades\Cache::put('checkout_idemp_' . $idempotencyKey, $order->order_number, 600); // 10 minutes cache
                    }
                    return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
                }

                $gateway = $this->paymentManager->driver($data['payment_method']);
                $order = $this->orders->createPendingOnlineOrder($cart, $data);
                if (!\Illuminate\Support\Facades\Auth::check()) {
                    $guestToken = \Illuminate\Support\Str::random(32);
                    $order->update(['guest_token' => $guestToken]);
                    session(['guest_order_token_' . $order->order_number => $guestToken]);
                }
                
                if ($idempotencyKey) {
                    \Illuminate\Support\Facades\Cache::put('checkout_idemp_' . $idempotencyKey, $order->order_number, 600);
                }
                
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
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return back()->withErrors(['checkout' => 'Please wait, your previous request is still processing.'])->withInput();
        } finally {
            $lock?->release();
        }
    }

    /**
     * AJAX: Return real-time shipping cost for a given postal code.
     * Called from checkout page JS when user enters pincode.
     */
    public function shippingQuote(Request $request): \Illuminate\Http\JsonResponse
    {
        $postalCode = trim($request->input('postal_code', ''));

        if (strlen($postalCode) !== 6 || !ctype_digit($postalCode)) {
            return response()->json(['shipping' => '0.00', 'label' => 'FREE']);
        }

        $paymentMethod = $request->input('payment_method');

        $totals = $this->cart->computeTotals($postalCode, $paymentMethod);

        $shipping = (float) ($totals['shipping'] ?? 0);
        $label    = $shipping > 0
            ? '₹' . number_format($shipping, 2)
            : 'FREE';

        return response()->json([
            'shipping'   => $totals['shipping'],
            'grand'      => $totals['grand'],
            'label'      => $label,
            'is_free'    => $shipping === 0.0,
        ]);
    }
}
