<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly RazorpayService $razorpay
    ) {}

    public function create(): View|RedirectResponse
    {
        $lines = $this->cart->linesWithPricing();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => __('Your cart is empty.')]);
        }

        $postal = old('postal_code', '');
        $totals = $this->cart->computeTotals($postal);

        return view('storefront.checkout', compact('lines', 'totals'));
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
            'payment_method' => ['required', 'in:cod,razorpay'],
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

            if (! $this->razorpay->isConfigured()) {
                return back()->withErrors(['payment_method' => __('Online payment is not configured.')])->withInput();
            }

            $order = $this->orders->createRazorpayPendingOrder($cart, $data);

            $amountPaise = (int) round(((float) $order->grand_total) * 100);
            $rz = $this->razorpay->createOrder($amountPaise, $order->order_number);

            $order->update(['razorpay_order_id' => $rz['id']]);

            $key = config('commerce.razorpay.key');

            return view('storefront.checkout-razorpay', [
                'order' => $order,
                'razorpayKey' => $key,
                'amountPaise' => $amountPaise,
                'customerName' => $order->customer_name,
                'customerEmail' => $order->email,
                'customerPhone' => $order->phone,
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
