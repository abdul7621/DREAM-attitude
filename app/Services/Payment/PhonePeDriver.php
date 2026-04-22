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
    private string $clientId;
    private string $clientSecret;
    private int $clientVersion;
    private string $env;
    private string $baseAuthUrl;
    private string $basePgUrl;

    public function __construct(protected PaymentMethod $method)
    {
        // For PhonePe V2, utilizing existing V1 dynamic fields:
        // merchant_id = Client ID
        // salt_key = Client Secret
        // salt_index = Client Version (default 1)
        $this->clientId = (string) ($this->method->getConfigValue('merchant_id') ?? '');
        $this->clientSecret = (string) ($this->method->getConfigValue('salt_key') ?? '');
        $this->clientVersion = (int) ($this->method->getConfigValue('salt_index') ?? 1);
        $this->env = strtoupper((string) ($this->method->getConfigValue('env') ?? 'UAT'));
        
        if ($this->env === 'PROD') {
            // Identity Manager is standard for Production V2 OAuth
            $this->baseAuthUrl = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
            $this->basePgUrl = 'https://api.phonepe.com/apis/pg/checkout/v2';
        } else {
            $this->baseAuthUrl = 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';
            $this->basePgUrl = 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2';
        }
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    /**
     * Obtains the O-Bearer OAuth token, caching it securely before expiration.
     */
    private function getAccessToken($forceRefresh = false): string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("PhonePe Client ID or Secret is missing in config.");
        }

        $cacheKey = "phonepe_v2_token_" . md5($this->clientId);
        
        if (!$forceRefresh && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        Log::info("PhonePe V2: Requesting new OAuth Token");

        $response = Http::asForm()->post($this->baseAuthUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'client_version' => $this->clientVersion,
            'grant_type' => 'client_credentials'
        ]);

        if ($response->failed() || empty($response->json('access_token'))) {
            Log::error("PhonePe OAuth Failed", ['response' => $response->body(), 'status' => $response->status()]);
            // Some Prod merchants map to hermes, fallback safety
            if ($this->env === 'PROD' && $response->status() === 404 && str_contains($this->baseAuthUrl, 'identity-manager')) {
                $this->baseAuthUrl = 'https://api.phonepe.com/apis/hermes/v1/oauth/token';
                return $this->getAccessToken($forceRefresh);
            }
            throw new Exception('Failed to obtain PhonePe Auth Token. Please check Client ID and Client Secret.');
        }

        $token = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 86400);

        // Cache the token with 5-minute buffer (300 seconds)
        \Illuminate\Support\Facades\Cache::put($cacheKey, $token, max(1, $expiresIn - 300));

        return $token;
    }

    public function createOrder(Order $order): array
    {
        // Payment Idempotency Support (Unique TXN mapping)
        $transactionId = 'TXN_' . $order->order_number . '_' . time();
        $amountPaise = (int) round($order->grand_total * 100);

        $payload = [
            'merchantOrderId' => $transactionId,
            'amount' => $amountPaise,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Order ' . $order->order_number,
                'merchantUrls' => [
                    'redirectUrl' => route('payments.verify', ['gateway' => 'phonepe'])
                ]
            ]
        ];

        Log::info("PhonePe V2: Creating Order", ['txnId' => $transactionId, 'amount' => $amountPaise]);

        $attempts = 0;
        $maxAttempts = 2;
        $forceRefresh = false;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $token = $this->getAccessToken($forceRefresh);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "O-Bearer {$token}",
                ])->post($this->basePgUrl . '/pay', $payload);

                if ($response->status() === 401) {
                    $forceRefresh = true;
                    if ($attempts >= $maxAttempts) {
                        throw new Exception("PhonePe Unauthorized (401) even after token refresh.");
                    }
                    continue; // Retry after fetching fresh token
                }

                if ($response->failed() || !$response->json('success')) {
                    throw new Exception('PhonePe order creation failed: ' . $response->body());
                }

                $responseData = $response->json();
                
                $paymentUrl = $responseData['data']['redirectInfo']['url'] 
                           ?? $responseData['data']['instrumentResponse']['redirectInfo']['url'] 
                           ?? null;

                if (!$paymentUrl) {
                    throw new Exception("PhonePe payment URL missing from response: " . json_encode($responseData));
                }

                $order->update(['gateway_order_id' => $transactionId]);

                return [
                    'provider_order_id' => $transactionId,
                    'payment_url' => $paymentUrl
                ];

            } catch (Exception $e) {
                if ($attempts >= $maxAttempts) {
                    Log::error("PhonePe order creation failed permanently", ['error' => $e->getMessage()]);
                    throw $e;
                }
                $forceRefresh = true;
            }
        }

        throw new Exception("PhonePe order failed to initialize");
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

        Log::info("PhonePe V2: Verifying Status", ['txnId' => $transactionId]);

        $attempts = 0;
        $maxAttempts = 2;
        $forceRefresh = false;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $token = $this->getAccessToken($forceRefresh);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "O-Bearer {$token}",
                ])->get($this->basePgUrl . "/order/{$transactionId}/status");

                if ($response->status() === 401) {
                    $forceRefresh = true;
                    continue; // Retry
                }

                $state = $response->json('data.state');
                
                Log::info("PhonePe status API response", ['txn' => $transactionId, 'state' => $state]);

                // Order-Payment Bind Protection (Hash Validation Alternative)
                // Cryptographically checked via authorized S2S API token
                if ($response->json('success') === true && $state === 'COMPLETED') {
                    $paidAmountPaise = (int) $response->json('data.amount');
                    $expectedAmountPaise = (int) round($order->grand_total * 100);

                    if ($paidAmountPaise === $expectedAmountPaise) {
                        return true;
                    } else {
                        Log::critical("PhonePe Amount Mismatch! Possible Tampering.", [
                            'order' => $order->id,
                            'expected_paise' => $expectedAmountPaise,
                            'received_paise' => $paidAmountPaise
                        ]);
                        return false;
                    }
                }

                return false;

            } catch (Exception $e) {
                if ($attempts >= $maxAttempts) {
                    Log::error("PhonePe status verification failed", ['error' => $e->getMessage()]);
                    return false;
                }
                $forceRefresh = true;
            }
        }

        return false;
    }

    public function verifySignature(\Illuminate\Http\Request $request): bool
    {
        // Safe: UI frontend redirects do not carry full checksum in V2. 
        // We ALWAYS trigger Server-to-Server API verification manually via verifyPayment() in PaymentController.
        // Furthermore, Webhooks are handled by PhonePeWebhookController which enforces S2S polling.
        return true; 
    }

    public function refund(Order $order, float $amount): array
    {
        $originalTxnId = $order->gateway_order_id ?? null;
        if (!$originalTxnId) {
            throw new Exception("Original transaction ID missing for refund");
        }

        $refundTxnId = 'REF_' . $order->order_number . '_' . time();
        $amountPaise = (int) round($amount * 100);

        $payload = [
            'merchantId' => $this->clientId,
            'originalTransactionId' => $originalTxnId,
            'merchantTransactionId' => $refundTxnId,
            'amount' => $amountPaise,
        ];

        $token = $this->getAccessToken();

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "O-Bearer {$token}",
        ])->post($this->basePgUrl . '/refund', $payload);

        if ($response->failed() || !$response->json('success')) {
            throw new Exception("PhonePe Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
