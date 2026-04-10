<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstamojoDriver implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $authToken;
    private string $env;
    private string $baseUrl;

    public function __construct(protected PaymentMethod $method)
    {
        $this->apiKey = $this->method->getConfigValue('api_key', '');
        $this->authToken = $this->method->getConfigValue('auth_token', '');
        $this->env = $this->method->getConfigValue('env', 'TEST');
        
        $this->baseUrl = $this->env === 'PROD' 
            ? 'https://www.instamojo.com/api/1.1' 
            : 'https://test.instamojo.com/api/1.1';
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    public function createOrder(Order $order): array
    {
        if (empty($this->apiKey) || empty($this->authToken)) {
            throw new Exception("Instamojo is not configured");
        }

        $transactionId = 'IM_' . $order->order_number . '_' . time();

        $payload = [
            'purpose' => 'Order #' . $order->order_number,
            'amount' => round($order->grand_total, 2),
            'buyer_name' => $order->customer_name,
            'phone' => $order->phone,
            'email' => $order->email ?? 'guest@example.com',
            'redirect_url' => route('payments.verify'),
            'allow_repeated_payments' => false,
        ];

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'X-Auth-Token' => $this->authToken,
            ])->asForm()->post($this->baseUrl . '/payment-requests/', $payload);

            if ($response->failed() || !$response->json('success')) {
                throw new Exception('Instamojo order failed: ' . $response->body());
            }

            $responseData = $response->json();
            $paymentUrl = $responseData['payment_request']['longurl'];
            $requestId = $responseData['payment_request']['id'];

            // Save TXN ID
            $order->update(['metadata' => array_merge((array)$order->metadata, ['gateway_order_id' => $requestId])]);

            return [
                'provider_order_id' => $requestId,
                'payment_url' => $paymentUrl
            ];
            
        } catch (Exception $e) {
            Log::error("Instamojo order creation failed", ['error' => $e->getMessage(), 'order' => $order->id]);
            throw $e;
        }
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $paymentId = $requestData['payment_id'] ?? '';
        $paymentRequestId = $requestData['payment_request_id'] ?? $order->metadata['gateway_order_id'] ?? '';
        
        if (empty($paymentId) || empty($paymentRequestId)) {
            return false;
        }

        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'X-Auth-Token' => $this->authToken,
        ])->get($this->baseUrl . "/payment-requests/{$paymentRequestId}/{$paymentId}/");

        if ($response->successful()) {
            $data = $response->json();
            if (($data['success'] ?? false) && ($data['payment_request']['payment']['status'] ?? '') === 'Credit') {
                return true;
            }
        }

        return false;
    }

    public function refund(Order $order, float $amount): array
    {
        $paymentId = $order->metadata['gateway_payment_id'] ?? null;

        if (!$paymentId) {
            throw new Exception("Original payment ID missing for refund");
        }

        $payload = [
            'payment_id' => $paymentId,
            'type' => 'RFD',
            'body' => 'Customer requested refund',
            'refund_amount' => round($amount, 2)
        ];

        $response = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'X-Auth-Token' => $this->authToken,
        ])->asForm()->post($this->baseUrl . '/refunds/', $payload);

        if ($response->failed()) {
            throw new Exception("Instamojo Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
