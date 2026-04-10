<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Get the unique string identifier for this gateway (e.g., 'razorpay', 'phonepe')
     */
    public function getDriverName(): string;

    /**
     * Create a pending payment order with the gateway
     * Returns an array with details needed for checkout (e.g., order_id, token)
     */
    public function createOrder(Order $order): array;

    /**
     * Verify a payment after the user returns from gateway
     * Throws an exception or returns false if invalid
     */
    public function verifyPayment(array $requestData, Order $order): bool;

    /**
     * Refund a payment for an order
     */
    public function refund(Order $order, float $amount): array;
}
