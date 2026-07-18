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

        $activeGateways = $this->paymentManager->activeGateways();
        $defaultPaymentMethod = collect($activeGateways)->firstWhere('is_default', true)?->name ?? 'phonepe';

        $postal = old('postal_code', '');
        $paymentMethod = old('payment_method', $defaultPaymentMethod);
        $country = old('country', 'IN');
        // Zero-Trust Security: We DO NOT use $val['state'] for shipping cost calculation!
        // The backend determines shipping internally inside CartService using the postal code & payment method.
        $totals = $this->cart->computeTotals($postal, $paymentMethod, $country);

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

            $country = strtoupper(trim($request->input('country', 'IN')));

            $phoneRule = ($country === 'IN')
                ? ['required', 'regex:/^(?:(?:\+|00)?91[\-\s]?)?[6-9]\d{9}$/']
                : ['required', 'string', 'min:7', 'max:20'];

            $postalCodeRule = ($country === 'IN')
                ? ['required', 'regex:/^[1-9][0-9]{5}$/']
                : ['required', 'string', 'min:3', 'max:16'];

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'customer_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => $phoneRule,
                'address_line1' => ['required', 'string', 'max:255'],
                'address_line2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:128'],
                'state' => ['required', 'string', 'max:128'],
                'postal_code' => $postalCodeRule,
                'country' => ['required', 'string', 'max:8'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'payment_method' => ['required', \Illuminate\Validation\Rule::in(\App\Models\PaymentMethod::active()->pluck('name')->toArray())],
                'idempotency_key' => ['nullable', 'string', 'max:64'],
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $data = $validator->validated();

            // Reject COD for international orders
            if ($data['country'] !== 'IN' && $data['payment_method'] === 'cod') {
                return back()->withErrors(['payment_method' => 'Cash on Delivery (COD) is not available for international orders.'])->withInput();
            }

            // Resolve country code and country name
            $countryNames = [
                'IN' => 'India', 'US' => 'United States', 'GB' => 'United Kingdom', 'AE' => 'United Arab Emirates',
                'CA' => 'Canada', 'AU' => 'Australia', 'SG' => 'Singapore', 'MY' => 'Malaysia',
                'DE' => 'Germany', 'FR' => 'France', 'AF' => 'Afghanistan', 'AO' => 'Angola',
                'AL' => 'Albania', 'AD' => 'Andorra', 'AR' => 'Argentina', 'AM' => 'Armenia',
                'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BI' => 'Burundi', 'BE' => 'Belgium',
                'BJ' => 'Benin', 'BF' => 'Burkina Faso', 'BD' => 'Bangladesh', 'BG' => 'Bulgaria',
                'BH' => 'Bahrain', 'BS' => 'Bahamas', 'BA' => 'Bosnia and Herzegovina', 'BY' => 'Belarus',
                'BZ' => 'Belize', 'BO' => 'Bolivia', 'BR' => 'Brazil', 'BB' => 'Barbados',
                'BN' => 'Brunei', 'BT' => 'Bhutan', 'BW' => 'Botswana', 'CF' => 'Central African Republic',
                'CH' => 'Switzerland', 'CL' => 'Chile', 'CN' => 'China', 'CM' => 'Cameroon',
                'CD' => 'Congo, Democratic Republic', 'CG' => 'Congo, Republic', 'CO' => 'Colombia',
                'KM' => 'Comoros', 'CV' => 'Cape Verde', 'CR' => 'Costa Rica', 'CU' => 'Cuba',
                'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DJ' => 'Djibouti', 'DM' => 'Dominica',
                'DK' => 'Denmark', 'DO' => 'Dominican Republic', 'DZ' => 'Algeria', 'EC' => 'Ecuador',
                'EG' => 'Egypt', 'ER' => 'Eritrea', 'ES' => 'Spain', 'EE' => 'Estonia',
                'ET' => 'Ethiopia', 'FI' => 'Finland', 'FJ' => 'Fiji', 'GA' => 'Gabon',
                'GE' => 'Georgia', 'GH' => 'Ghana', 'GN' => 'Guinea', 'GM' => 'Gambia',
                'GQ' => 'Equatorial Guinea', 'GR' => 'Greece', 'GT' => 'Guatemala', 'GY' => 'Guyana',
                'HN' => 'Honduras', 'HR' => 'Croatia', 'HT' => 'Haiti', 'HU' => 'Hungary',
                'ID' => 'Indonesia', 'IE' => 'Ireland', 'IR' => 'Iran', 'IQ' => 'Iraq',
                'IS' => 'Iceland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica',
                'JO' => 'Jordan', 'JP' => 'Japan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya',
                'KG' => 'Kyrgyzstan', 'KH' => 'Cambodia', 'KR' => 'South Korea', 'KW' => 'Kuwait',
                'LA' => 'Laos', 'LB' => 'Lebanon', 'LY' => 'Libya', 'LK' => 'Sri Lanka',
                'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia', 'MA' => 'Morocco',
                'MD' => 'Moldova', 'MG' => 'Madagascar', 'MV' => 'Maldives', 'MX' => 'Mexico',
                'ML' => 'Mali', 'MT' => 'Malta', 'MM' => 'Myanmar', 'MN' => 'Mongolia',
                'MZ' => 'Mozambique', 'MW' => 'Malawi', 'NA' => 'Namibia', 'NG' => 'Nigeria',
                'NI' => 'Nicaragua', 'NL' => 'Netherlands', 'NO' => 'Norway', 'NP' => 'Nepal',
                'NZ' => 'New Zealand', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PA' => 'Panama',
                'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'KP' => 'North Korea',
                'PT' => 'Portugal', 'PY' => 'Paraguay', 'QA' => 'Qatar', 'RO' => 'Romania',
                'RU' => 'Russia', 'SA' => 'Saudi Arabia', 'SD' => 'Sudan', 'SE' => 'Sweden',
                'SG' => 'Singapore', 'SI' => 'Slovenia', 'SK' => 'Slovakia', 'SN' => 'Senegal',
                'SO' => 'Somalia', 'SR' => 'Suriname', 'SS' => 'South Sudan', 'SV' => 'El Salvador',
                'SY' => 'Syria', 'SZ' => 'Swaziland', 'TD' => 'Chad', 'TG' => 'Togo',
                'TH' => 'Thailand', 'TJ' => 'TJK', 'TL' => 'East Timor', 'TM' => 'Turkmenistan',
                'TN' => 'Tunisia', 'TR' => 'Turkey', 'TT' => 'Trinidad and Tobago', 'TW' => 'Taiwan',
                'TZ' => 'Tanzania', 'UA' => 'Ukraine', 'UG' => 'Uganda', 'UY' => 'Uruguay',
                'UZ' => 'Uzbekistan', 'VC' => 'Saint Vincent and Grenadines', 'VE' => 'Venezuela',
                'VN' => 'Vietnam', 'YE' => 'Yemen', 'ZA' => 'South Africa', 'ZM' => 'Zambia',
                'ZW' => 'Zimbabwe'
            ];

            $data['country_code'] = $country;
            $data['country_name'] = $countryNames[$country] ?? $country;

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
        $paymentMethod = $request->input('payment_method');
        $country = $request->input('country', 'IN');

        $totals = $this->cart->computeTotals($postalCode, $paymentMethod, $country);

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
