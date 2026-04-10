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
            'phone' => ['required', 'string', 'max:32'],
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

        $currentUser = \Illuminate\Support\Facades\Auth::user();

        // ── Customer Identity Layer ─────────────────────────────────────
        if (!$currentUser || $currentUser->phone !== $data['phone']) {
            $existingUser = null;
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, &$existingUser) {
                $existingUser = \App\Models\User::where('phone', $data['phone'])
                    ->lockForUpdate()
                    ->first();

                if (!$existingUser && !empty($data['email'])) {
                    $existingUser = \App\Models\User::where('email', $data['email'])
                        ->lockForUpdate()
                        ->first();
                }

                if (!$existingUser) {
                    $existingUser = \App\Models\User::create([
                        'name' => $data['customer_name'],
                        'phone' => $data['phone'],
                        'email' => !empty($data['email']) ? $data['email'] : null,
                        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                    ]);
                }
            });

            \Illuminate\Support\Facades\Auth::login($existingUser);
            $this->cart->mergeOnLogin($existingUser);
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
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
