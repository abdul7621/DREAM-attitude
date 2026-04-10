<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeDriver implements PaymentGatewayInterface
{
    private string $appId;
    private string $secretKey;
    private string $env;
    private string $baseUrl;

    public function __construct(protected PaymentMethod $method)
    {
        $this->appId = (string) ($this->method->getConfigValue('app_id') ?? '');
        $this->secretKey = (string) ($this->method->getConfigValue('secret_key') ?? '');
        $this->env = (string) ($this->method->getConfigValue('env') ?? 'TEST');
        
        $this->baseUrl = $this->env === 'PROD' 
            ? 'https://api.cashfree.com/pg' 
            : 'https://sandbox.cashfree.com/pg';
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    public function createOrder(Order $order): array
    {
        if (empty($this->appId) || empty($this->secretKey)) {
            throw new Exception("Cashfree is not configured");
        }

        $transactionId = 'CF_' . $order->order_number . '_' . time();

        $payload = [
            'order_id' => $transactionId,
            'order_amount' => round($order->grand_total, 2),
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => 'CST_' . ($order->user_id ?? uniqid()),
                'customer_email' => $order->email ?? 'guest@example.com',
                'customer_phone' => $order->phone,
                'customer_name' => $order->customer_name
            ],
            'order_meta' => [
                'return_url' => route('payments.verify', ['gateway' => 'cashfree']) . '?order_id={order_id}&order_token={order_token}',
            ]
        ];

        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version' => '2022-09-01',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/orders', $payload);

            if ($response->failed()) {
                throw new Exception('Cashfree order failed: ' . $response->body());
            }

            $responseData = $response->json();

            // Save TXN ID
            $order->update(['gateway_order_id' => $transactionId]);

            return [
                'provider_order_id' => $transactionId,
                'payment_session_id' => $responseData['payment_session_id'],
                'payment_url' => $responseData['payment_link'] ?? null,
                'app_id' => $this->appId,
                'env' => $this->env
            ];
            
        } catch (Exception $e) {
            Log::error("Cashfree order creation failed", ['error' => $e->getMessage(), 'order' => $order->id]);
            throw $e;
        }
    }

    public function extractOrderId(array $requestData): ?string
    {
        return $requestData['order_id'] ?? null;
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $transactionId = $requestData['order_id'] ?? $order->gateway_order_id ?? '';
        
        if (empty($transactionId)) {
            return false;
        }

        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => '2022-09-01',
            'Accept' => 'application/json'
        ])->get($this->baseUrl . "/orders/{$transactionId}");

        if ($response->successful()) {
            $data = $response->json();
            if (($data['order_status'] ?? '') === 'PAID') {
                return true;
            }
        }

        return false;
    }

    public function refund(Order $order, float $amount): array
    {
        $originalTxnId = $order->gateway_order_id ?? null;

        if (!$originalTxnId) {
            throw new Exception("Original transaction ID missing for refund");
        }

        $refundId = 'REF_' . $order->order_number . '_' . time();

        $payload = [
            'refund_amount' => round($amount, 2),
            'refund_id' => $refundId,
            'refund_note' => 'Customer requested refund'
        ];

        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . "/orders/{$originalTxnId}/refunds", $payload);

        if ($response->failed()) {
            throw new Exception("Cashfree Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
