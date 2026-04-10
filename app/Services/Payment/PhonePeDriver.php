<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhonePeDriver implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $saltKey;
    private string $saltIndex;
    private string $env;
    private string $baseUrl;

    public function __construct(protected PaymentMethod $method)
    {
        $this->merchantId = $this->method->getConfigValue('merchant_id', '');
        $this->saltKey = $this->method->getConfigValue('salt_key', '');
        $this->saltIndex = (string) $this->method->getConfigValue('salt_index', '1');
        $this->env = $this->method->getConfigValue('env', 'UAT');
        
        $this->baseUrl = $this->env === 'PROD' 
            ? 'https://api.phonepe.com/apis/hermes' 
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    public function createOrder(Order $order): array
    {
        if (empty($this->merchantId) || empty($this->saltKey)) {
            throw new Exception("PhonePe is not configured");
        }

        $amountPaise = (int) round($order->grand_total * 100);
        $transactionId = 'TXN_' . $order->order_number . '_' . time();

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $transactionId,
            'merchantUserId' => 'MUID_' . ($order->user_id ?? $order->email ?? uniqid()),
            'amount' => $amountPaise,
            'redirectUrl' => route('payments.verify', ['gateway' => 'phonepe']),
            'redirectMode' => 'POST',
            'callbackUrl' => route('payments.verify', ['gateway' => 'phonepe']),
            'mobileNumber' => $order->phone,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE'
            ]
        ];

        $encode = base64_encode(json_encode($payload));
        $saltKey = $this->saltKey;
        $saltIndex = $this->saltIndex;
        $string = $encode . '/pg/v1/pay' . $saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $saltIndex;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'X-VERIFY' => $finalXHeader,
            ])->post($this->baseUrl . '/pg/v1/pay', [
                'request' => $encode
            ]);

            if ($response->failed() || !$response->json('success')) {
                throw new Exception('PhonePe order failed: ' . $response->body());
            }

            $responseData = $response->json();
            $paymentUrl = $responseData['data']['instrumentResponse']['redirectInfo']['url'];

            // Save TXN ID
            $order->update(['gateway_order_id' => $transactionId]);

            return [
                'provider_order_id' => $transactionId,
                'payment_url' => $paymentUrl
            ];
            
        } catch (Exception $e) {
            Log::error("PhonePe order creation failed", ['error' => $e->getMessage(), 'order' => $order->id]);
            throw $e;
        }
    }

    public function extractOrderId(array $requestData): ?string
    {
        return $requestData['transactionId'] ?? null;
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $transactionId = $requestData['transactionId'] ?? $order->gateway_order_id ?? '';
        
        if (empty($transactionId)) {
            return false;
        }

        $saltKey = $this->saltKey;
        $saltIndex = $this->saltIndex;
        
        $finalXHeader = hash('sha256', "/pg/v1/status/{$this->merchantId}/{$transactionId}" . $saltKey) . "###" . $saltIndex;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
            'X-VERIFY' => $finalXHeader,
            'X-MERCHANT-ID' => $this->merchantId
        ])->get($this->baseUrl . "/pg/v1/status/{$this->merchantId}/{$transactionId}");

        if ($response->json('success') === true && $response->json('data.state') === 'COMPLETED') {
            return true;
        }

        return false;
    }

    public function refund(Order $order, float $amount): array
    {
        $amountPaise = (int) round($amount * 100);
        $originalTxnId = $order->gateway_order_id ?? null;

        if (!$originalTxnId) {
            throw new Exception("Original transaction ID missing for refund");
        }

        $refundTxnId = 'REF_' . $order->order_number . '_' . time();

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantUserId' => 'MUID_' . ($order->user_id ?? 'guest'),
            'originalTransactionId' => $originalTxnId,
            'merchantTransactionId' => $refundTxnId,
            'amount' => $amountPaise,
            'callbackUrl' => route('payments.verify'), // or refund callback
        ];

        $encode = base64_encode(json_encode($payload));
        $string = $encode . '/pg/v1/refund' . $this->saltKey;
        $sha256 = hash('sha256', $string);
        $finalXHeader = $sha256 . '###' . $this->saltIndex;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
            'X-VERIFY' => $finalXHeader,
        ])->post($this->baseUrl . '/pg/v1/refund', [
            'request' => $encode
        ]);

        if ($response->failed() || !$response->json('success')) {
            throw new Exception("PhonePe Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
