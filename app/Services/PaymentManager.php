<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $drivers = [];

    public function extend(string $driverName, PaymentGatewayInterface $driver): self
    {
        $this->drivers[$driverName] = $driver;
        return $this;
    }

    public function driver(string $driverName): PaymentGatewayInterface
    {
        if (!isset($this->drivers[$driverName])) {
            throw new InvalidArgumentException("Payment driver [{$driverName}] not supported.");
        }

        return $this->drivers[$driverName];
    }
}
