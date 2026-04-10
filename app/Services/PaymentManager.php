<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentMethod;
use App\Services\Payment\RazorpayDriver;
use App\Services\Payment\PhonePeDriver;
use App\Services\Payment\CashfreeDriver;
use App\Services\Payment\InstamojoDriver;
use App\Services\Payment\PayUDriver;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentManager
{
    public function driver(string $name): PaymentGatewayInterface
    {
        $method = PaymentMethod::where('name', $name)
            ->where('is_active', true)
            ->firstOrFail();

        $driverName = $method->driver ?? $method->name;

        return match ($driverName) {
            'razorpay'  => new RazorpayDriver($method),
            'phonepe'   => new PhonePeDriver($method),
            'cashfree'  => new CashfreeDriver($method),
            'instamojo' => new InstamojoDriver($method),
            'payu'      => new PayUDriver($method),
            default     => throw new InvalidArgumentException("Unsupported gateway driver: {$driverName}"),
        };
    }

    public function defaultDriver(): ?PaymentGatewayInterface
    {
        $method = PaymentMethod::where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (!$method) {
            return null;
        }

        return $this->driver($method->name);
    }

    public function activeGateways(): Collection
    {
        return PaymentMethod::active()->orderBy('sort_order')->get();
    }

    public function activeOnlineGateways(): Collection
    {
        return PaymentMethod::online()->orderBy('sort_order')->get();
    }
}
