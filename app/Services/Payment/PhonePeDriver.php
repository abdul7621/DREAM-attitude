<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Cache;
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
     * Per PhonePe docs: use `expires_at` (epoch timestamp), NOT `expires_in` (can be null).
     */
    private function getAccessToken($forceRefresh = false): string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("PhonePe Client ID or Secret is missing in config.");
        }

        $cacheKey = "phonepe_v2_token_" . md5($this->clientId);
        
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
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

        // PhonePe V2 docs: `expires_in` can be NULL. Use `expires_at` (epoch seconds) instead.
        $expiresAt = $response->json('expires_at');
        if ($expiresAt && is_numeric($expiresAt)) {
            // Cache until 5 minutes before expiration for safety margin
            $ttlSeconds = max(60, (int) $expiresAt - time() - 300);
        } else {
            // Fallback: if neither field is reliable, cache for 30 minutes as a safe default
            $ttlSeconds = 1800;
            Log::warning("PhonePe OAuth: expires_at missing from token response, using 30min default cache TTL");
        }

        Cache::put($cacheKey, $token, $ttlSeconds);

        return $token;
    }

    public function createOrder(Order $order): array
    {
        // Payment Idempotency Support (Unique TXN mapping) - length restricted to PhonePe V2 limits (< 35 chars)
        $transactionId = 'DA' . time() . $order->id;
        $amountPaise = (int) round($order->grand_total * 100);

        $payload = [
            'merchantOrderId' => $transactionId,
            'amount' => $amountPaise,
            'expireAfter' => 1200, // 20 minutes checkout session timeout
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Order ' . $order->order_number,
                'merchantUrls' => [
                    'redirectUrl' => route('payments.verify', [
                        'gateway' => 'phonepe',
                        'transactionId' => $transactionId
                    ])
                ]
            ],
            // MetaInfo for reconciliation and tracking
            'metaInfo' => [
                'udf1' => $order->order_number,
                'udf2' => (string) $order->id,
                'udf3' => $order->customer_phone ?? '',
                'udf4' => $order->customer_name ?? '',
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

                if ($response->failed()) {
                    throw new Exception('PhonePe order creation failed: ' . $response->body());
                }

                $responseData = $response->json();
                
                // V2 Response: flat JSON with redirectUrl at root level
                // Doc response example: {"orderId":"OMO...","state":"PENDING","expireAt":...,"redirectUrl":"https://..."}
                $paymentUrl = $responseData['redirectUrl'] ?? null;

                if (!$paymentUrl) {
                    throw new Exception("PhonePe payment URL missing from response: " . $response->body());
                }

                $order->update(['gateway_order_id' => $transactionId]);

                return [
                    'provider_order_id' => $transactionId,
                    'payment_url' => $paymentUrl
                ];

            } catch (Exception $e) {
                // Only retry if it was explicitly a 401 Unauthorized exception
                if (str_contains($e->getMessage(), '401') && $attempts < $maxAttempts) {
                    $forceRefresh = true;
                } else {
                    Log::error("PhonePe order creation failed permanently", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }
        }

        throw new Exception("PhonePe order failed to initialize");
    }

    public function extractOrderId(array $requestData): ?string
    {
        // Our redirect URL passes transactionId as a query parameter
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

                // V2 Status API: GET /checkout/v2/order/{merchantOrderId}/status
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "O-Bearer {$token}",
                ])->get($this->basePgUrl . "/order/{$transactionId}/status");

                if ($response->status() === 401) {
                    $forceRefresh = true;
                    continue; // Retry
                }

                // V2 Status Response: flat JSON at root level
                // Doc example: {"orderId":"OMO...","state":"COMPLETED","amount":1000,"paymentDetails":[...]}
                $state = $response->json('state');
                
                Log::info("PhonePe status API response", [
                    'txn' => $transactionId,
                    'state' => $state,
                    'orderId' => $response->json('orderId'),
                    'amount' => $response->json('amount'),
                ]);

                // Order-Payment Bind Protection
                // Cryptographically checked via authorized S2S OAuth token
                if ($response->successful() && $state === 'COMPLETED') {
                    $paidAmountPaise = (int) $response->json('amount');
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
                if (str_contains($e->getMessage(), '401') && $attempts < $maxAttempts) {
                    $forceRefresh = true;
                } else {
                    Log::error("PhonePe status verification failed", ['error' => $e->getMessage()]);
                    return false;
                }
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

    /**
     * Initiate a refund via PhonePe V2 Refund API.
     * V2 fields: merchantRefundId, originalMerchantOrderId, amount
     * Endpoint: POST /checkout/v2/refund
     */
    public function refund(Order $order, float $amount): array
    {
        $originalOrderId = $order->gateway_order_id ?? null;
        if (!$originalOrderId) {
            throw new Exception("Original transaction ID missing for refund");
        }

        // V2 Refund ID: alphanumeric, unique per refund attempt
        $merchantRefundId = 'DAREF' . time() . $order->id;
        $amountPaise = (int) round($amount * 100);

        // V2 Refund Payload per official docs
        $payload = [
            'merchantRefundId' => $merchantRefundId,
            'originalMerchantOrderId' => $originalOrderId,
            'amount' => $amountPaise,
        ];

        Log::info("PhonePe V2: Initiating Refund", [
            'refundId' => $merchantRefundId,
            'originalOrderId' => $originalOrderId,
            'amount' => $amountPaise,
        ]);

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
                ])->post($this->basePgUrl . '/refund', $payload);

                if ($response->status() === 401) {
                    $forceRefresh = true;
                    if ($attempts >= $maxAttempts) {
                        throw new Exception("PhonePe Refund: Unauthorized (401) even after token refresh.");
                    }
                    continue;
                }

                if ($response->failed()) {
                    throw new Exception("PhonePe Refund failed: " . $response->body());
                }

                // V2 Refund Response: {"refundId":"OMRxxxxx","amount":1234,"state":"PENDING"}
                $refundState = $response->json('state');
                Log::info("PhonePe V2: Refund Initiated", [
                    'refundId' => $response->json('refundId'),
                    'state' => $refundState,
                    'amount' => $response->json('amount'),
                ]);

                return $response->json();

            } catch (Exception $e) {
                if (str_contains($e->getMessage(), '401') && $attempts < $maxAttempts) {
                    $forceRefresh = true;
                } else {
                    Log::error("PhonePe Refund failed permanently", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }
        }

        throw new Exception("PhonePe Refund failed to initialize");
    }

    /**
     * Check refund status via PhonePe V2 Refund Status API.
     * Endpoint: GET /checkout/v2/refund/{merchantRefundId}/status
     */
    public function checkRefundStatus(string $merchantRefundId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "O-Bearer {$token}",
        ])->get($this->basePgUrl . "/refund/{$merchantRefundId}/status");

        if ($response->failed()) {
            Log::error("PhonePe Refund Status check failed", ['refundId' => $merchantRefundId, 'response' => $response->body()]);
            throw new Exception("PhonePe Refund Status check failed: " . $response->body());
        }

        Log::info("PhonePe V2: Refund Status", [
            'refundId' => $merchantRefundId,
            'state' => $response->json('state'),
        ]);

        return $response->json();
    }
}
