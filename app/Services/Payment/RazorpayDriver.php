<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\RazorpayService;
use Exception;
use Illuminate\Support\Facades\Log;

class RazorpayDriver implements PaymentGatewayInterface
{
    public function __construct(protected RazorpayService $razorpayService) {}

    public function getDriverName(): string
    {
        return 'razorpay';
    }

    public function createOrder(Order $order): array
    {
        if (!$this->razorpayService->isConfigured()) {
            throw new Exception("Razorpay is not configured");
        }

        // Amount in paise
        $amountPaise = (int) round($order->grand_total * 100);

        try {
            $rzpOrder = $this->razorpayService->createOrder($amountPaise, $order->order_number);
            
            // Save gateway reference ID on order if supported by schema, or use order_id logic
            $order->update(['metadata' => array_merge((array)$order->metadata, ['razorpay_order_id' => $rzpOrder['id']])]);
            
            return [
                'provider_order_id' => $rzpOrder['id'],
                'key' => config('commerce.razorpay.key'),
                'amount' => $amountPaise,
                'name' => config('app.name'),
                'description' => 'Order #' . $order->order_number,
                'currency' => 'INR',
            ];
        } catch (Exception $e) {
            Log::error("Razorpay order creation failed", ['error' => $e->getMessage(), 'order' => $order->id]);
            throw $e;
        }
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $rzpOrderId = $requestData['razorpay_order_id'] ?? '';
        $rzpPaymentId = $requestData['razorpay_payment_id'] ?? '';
        $rzpSignature = $requestData['razorpay_signature'] ?? '';

        if (!$rzpOrderId || !$rzpPaymentId || !$rzpSignature) {
            return false;
        }

        return $this->razorpayService->verifyPaymentSignature($rzpOrderId, $rzpPaymentId, $rzpSignature);
    }
}
