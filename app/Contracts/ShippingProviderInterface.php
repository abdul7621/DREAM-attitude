<?php

namespace App\Contracts;

use App\Models\Order;

interface ShippingProviderInterface
{
    /**
     * Pushes the order to the shipping aggregator.
     * @param Order $order
     * @return array Response data containing shipment/order IDs provided by the aggregator.
     * @throws \Exception
     */
    public function createOrder(Order $order, ?string $forceLogisticName = null): array;

    /**
     * Requests the generation of the AWB for a specific internal or aggregator shipment ID.
     * @param string|int $shipmentId
     * @return array
     * @throws \Exception
     */
    public function generateAWB($shipmentId): array;

    /**
     * Generates a link or bytes for the shipping label.
     * @param string|int $shipmentId
     * @return string URL or Base64 of the label.
     * @throws \Exception
     */
    public function generateLabel($shipmentId): string;

    /**
     * Tracks the current status of an AWB.
     * @param string $awb
     * @return array
     */
    public function trackOrder(string $awb): array;

    /**
     * Cancels the shipment before it is manifested.
     * @param string|int $orderId
     * @return bool
     */
    public function cancelShipment($orderId): bool;
}
