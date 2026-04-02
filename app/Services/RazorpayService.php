<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayService
{
    public function isConfigured(): bool
    {
        $k = config('commerce.razorpay.key');
        $s = config('commerce.razorpay.secret');

        return is_string($k) && $k !== '' && is_string($s) && $s !== '';
    }

    /**
     * @return array{id: string, amount: int, currency: string, receipt: string}
     */
    public function createOrder(int $amountPaise, string $receipt): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $key = config('commerce.razorpay.key');
        $secret = config('commerce.razorpay.secret');

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->asJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountPaise,
                'currency' => 'INR',
                'receipt' => substr($receipt, 0, 40),
                'payment_capture' => 1,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Razorpay order failed: '.$response->body());
        }

        /** @var array{id: string, amount: int, currency: string, receipt: string} $json */
        $json = $response->json();

        return $json;
    }

    public function verifyPaymentSignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $secret = config('commerce.razorpay.secret');
        $payload = $razorpayOrderId.'|'.$razorpayPaymentId;
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $razorpaySignature);
    }
}
