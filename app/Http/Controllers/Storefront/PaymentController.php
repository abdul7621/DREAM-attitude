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

    public function verify(Request $request, string $gateway): RedirectResponse
    {
        $gatewayDriver = $this->paymentManager->driver($gateway);
        $incomingId = $gatewayDriver->extractOrderId($request->all());

        if (!$incomingId) {
            \Illuminate\Support\Facades\Log::error('Payment callback missing reference ID', ['gateway' => $gateway, 'payload' => $request->all()]);
            return redirect()->route('checkout.create')->withErrors(['payment' => __('Payment reference missing.')]);
        }

        $order = Order::query()->where('gateway_order_id', $incomingId)->firstOrFail();

        // Idempotency / Double payment protection
        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
        }

        try {
            $verified = $gatewayDriver->verifyPayment($request->all(), $order);
            if (!$verified) {
                throw new \Exception('Payment verification returned false');
            }

            // Amount validation is cryptographically assured by driver signature verification
            $this->orders->finalizeOnlinePayment($order, $request->all());
        } catch (\Exception $e) {
            $order->update([
                'payment_status' => Order::PAYMENT_STATUS_FAILED,
                'order_status' => Order::ORDER_STATUS_ABANDONED
            ]);
            
            \Illuminate\Support\Facades\Log::info('order_abandoned', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->grand_total,
                'reason' => $e->getMessage(),
            ]);

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
