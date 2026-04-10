<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentManager $paymentManager
    ) {}

    public function verify(Request $request): RedirectResponse
    {
        $pendingId = session('pending_payment_order_id');
        if (! $pendingId) {
            return redirect()->route('cart.index')->withErrors(['payment' => __('Session expired. Please try again.')]);
        }

        $order = Order::query()->whereKey($pendingId)->firstOrFail();

        if ($order->payment_status !== Order::PAYMENT_STATUS_PENDING || $order->order_status !== Order::ORDER_STATUS_AWAITING_PAYMENT) {
            return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
        }

        $gateway = $this->paymentManager->driver($order->payment_method);

        try {
            $verified = $gateway->verifyPayment($request->all(), $order);
            if (!$verified) {
                throw new \Exception('Payment verification failed');
            }

            $this->orders->finalizeOnlinePayment($order, $request->all());
        } catch (\Exception $e) {
            \App\Models\AuditLog::log('payment_failed', $order, [], [
                'reason' => 'verification_failed_or_other_error',
                'gateway' => $order->payment_method,
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->route('checkout.create')
                ->with('error', 'Payment failed. Please try again or use Cash on Delivery.')
                ->withInput();
        }

        return redirect()->route('order.success', ['orderNumber' => $order->fresh()->order_number]);
    }
}
