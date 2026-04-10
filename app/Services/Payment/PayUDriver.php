<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayUDriver implements PaymentGatewayInterface
{
    private string $merchantKey;
    private string $merchantSalt;
    private string $env;
    private string $baseUrl;

    public function __construct(protected PaymentMethod $method)
    {
        $this->merchantKey = (string) ($this->method->getConfigValue('merchant_key') ?? '');
        $this->merchantSalt = (string) ($this->method->getConfigValue('merchant_salt') ?? '');
        $this->env = (string) ($this->method->getConfigValue('env') ?? 'TEST');
        
        $this->baseUrl = $this->env === 'PROD' 
            ? 'https://secure.payu.in/_payment' 
            : 'https://test.payu.in/_payment';
    }

    public function getDriverName(): string
    {
        return $this->method->name;
    }

    public function createOrder(Order $order): array
    {
        if (empty($this->merchantKey) || empty($this->merchantSalt)) {
            throw new Exception("PayU is not configured");
        }

        $transactionId = 'PU_' . $order->order_number . '_' . time();
        $amount = round($order->grand_total, 2);
        $productInfo = 'Order #' . $order->order_number;
        $firstName = $order->customer_name;
        $email = $order->email ?? 'guest@example.com';

        // Hash sequence: key|txnid|amount|productinfo|firstname|email|udf1|...|udf10|salt
        $hashSequence = "{$this->merchantKey}|{$transactionId}|{$amount}|{$productInfo}|{$firstName}|{$email}|||||||||||{$this->merchantSalt}";
        $hash = hash("sha512", $hashSequence);

        $order->update(['gateway_order_id' => $transactionId]);

        return [
            'provider_order_id' => $transactionId,
            'payment_url' => $this->baseUrl,
            'key' => $this->merchantKey,
            'amount' => $amount,
            'productinfo' => $productInfo,
            'firstname' => $firstName,
            'email' => $email,
            'phone' => $order->phone,
            'surl' => route('payments.verify', ['gateway' => 'payu']),
            'furl' => route('payments.verify', ['gateway' => 'payu']),
            'hash' => $hash,
        ];
    }

    public function extractOrderId(array $requestData): ?string
    {
        return $requestData['txnid'] ?? null;
    }

    public function verifyPayment(array $requestData, Order $order): bool
    {
        $status = $requestData['status'] ?? '';
        $txnid = $requestData['txnid'] ?? '';
        $amount = $requestData['amount'] ?? '';
        $productinfo = $requestData['productinfo'] ?? '';
        $firstname = $requestData['firstname'] ?? '';
        $email = $requestData['email'] ?? '';
        $hashStr = $requestData['hash'] ?? '';

        if ($status !== 'success') {
            return false;
        }

        // Reverse hash sequence for verification
        // salt|status|||||||||||email|firstname|productinfo|amount|txnid|key
        $hashSequence = "{$this->merchantSalt}|{$status}|||||||||||{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$this->merchantKey}";
        $expectedHash = hash("sha512", $hashSequence);

        return $hashStr === $expectedHash;
    }

    public function refund(Order $order, float $amount): array
    {
        $payuTxnId = $order->gateway_payment_id ?? null; // usually mihpayid

        if (!$payuTxnId) {
            throw new Exception("Original payment ID missing for refund");
        }

        $refundId = 'REF_' . $order->order_number . '_' . time();

        $hashSequence = "{$this->merchantKey}|cancel_refund_transaction|{$payuTxnId}|{$this->merchantSalt}";
        $hash = hash("sha512", $hashSequence);

        $apiUrl = $this->env === 'PROD' ? 'https://info.payu.in/merchant/postservice.php?form=2' : 'https://test.payu.in/merchant/postservice.php?form=2';

        $response = Http::asForm()->post($apiUrl, [
            'key' => $this->merchantKey,
            'command' => 'cancel_refund_transaction',
            'hash' => $hash,
            'var1' => $payuTxnId,
            'var2' => $refundId,
            'var3' => round($amount, 2),
        ]);

        if ($response->failed()) {
            throw new Exception("PayU Refund failed: " . $response->body());
        }

        return $response->json();
    }
}
