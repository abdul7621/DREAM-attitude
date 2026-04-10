<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayDriver implements PaymentGatewayInterface
{
    private string $keyId;
    private string $keySecret;

    public function __construct(protected PaymentMethod $method)
    {
        $this->keyId = (string) ($this->method->getConfigValue('key_id') ?? config('commerce.razorpay.key', ''));
        $this->keySecret = (string) ($this->method->getConfigValue('key_secret') ?? config('commerce.razorpay.secret', ''));
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    public function createOrder(Order $order): array
    {
        if (empty($this->keyId) || empty($this->keySecret)) {
            throw new Exception("Razorpay goes unconfigured.");
        }

        // Amount in paise
        $amountPaise = (int) round($order->grand_total * 100);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->acceptJson()
                ->asJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountPaise,
                    'currency' => 'INR',
                    'receipt' => substr($order->order_number, 0, 40),
                    'payment_capture' => 1,
                ]);

            if ($response->failed()) {
                throw new Exception('Razorpay order failed: ' . $response->body());
            }

            $rzpOrder = $response->json();
            
            // Save gateway reference ID on order
            $order->update(['gateway_order_id' => $rzpOrder['id']]);
            
            return [
                'provider_order_id' => $rzpOrder['id'],
                'key' => $this->keyId,
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

    public function extractOrderId(array $requestData): ?string
    {
        return $requestData['razorpay_order_id'] ?? null;
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $rzpOrderId = $requestData['razorpay_order_id'] ?? '';
        $rzpPaymentId = $requestData['razorpay_payment_id'] ?? '';
        $rzpSignature = $requestData['razorpay_signature'] ?? '';

        if (!$rzpOrderId || !$rzpPaymentId || !$rzpSignature) {
            return false;
        }

        $payload = $rzpOrderId . '|' . $rzpPaymentId;
        $expected = hash_hmac('sha256', $payload, $this->keySecret);

        return hash_equals($expected, $rzpSignature);
    }

    public function refund(Order $order, float $amount): array
    {
        // Refund API implementation
        $amountPaise = (int) round($amount * 100);
        $paymentId = $order->metadata['gateway_payment_id'] ?? null;

        if (!$paymentId) {
            throw new Exception("Payment ID missing for refund");
        }

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->acceptJson()
            ->asJson()
            ->post("https://api.razorpay.com/v1/payments/{$paymentId}/refund", [
                'amount' => $amountPaise,
            ]);

        if ($response->failed()) {
            throw new Exception("Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
