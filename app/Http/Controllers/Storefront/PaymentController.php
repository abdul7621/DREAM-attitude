<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly RazorpayService $razorpay
    ) {}

    public function razorpayVerify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $pendingId = session('pending_razorpay_order_id');
        if (! $pendingId) {
            return redirect()->route('cart.index')->withErrors(['payment' => __('Session expired. Please try again.')]);
        }

        $order = Order::query()->whereKey($pendingId)->firstOrFail();

        if ($order->payment_status !== Order::PAYMENT_STATUS_PENDING || $order->order_status !== Order::ORDER_STATUS_AWAITING_PAYMENT) {
            return redirect()->route('order.success', ['orderNumber' => $order->order_number]);
        }

        if ($order->razorpay_order_id && $order->razorpay_order_id !== $data['razorpay_order_id']) {
            \App\Models\AuditLog::log('payment_failed', $order, [], [
                'reason' => 'mismatch_order_id',
                'gateway' => 'razorpay',
                'expected' => $order->razorpay_order_id,
                'received' => $data['razorpay_order_id'],
            ]);
            abort(422);
        }

        try {
            $this->orders->finalizeRazorpayPayment(
                $order,
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $data['razorpay_signature'],
                $this->razorpay
            );
        } catch (\Exception $e) {
            \App\Models\AuditLog::log('payment_failed', $order, [], [
                'reason' => 'signature_verification_failed_or_other_error',
                'gateway' => 'razorpay',
                'error_message' => $e->getMessage(),
                'payment_id' => $data['razorpay_payment_id'] ?? null
            ]);
            throw $e;
        }

        return redirect()->route('order.success', ['orderNumber' => $order->fresh()->order_number]);
    }
}
