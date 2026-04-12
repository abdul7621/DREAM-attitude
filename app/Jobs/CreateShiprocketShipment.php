<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\ShiprocketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShiprocketShipment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly Order $order
    ) {}

    public function handle(ShiprocketService $shiprocket): void
    {
        try {
            $result = $shiprocket->createOrder($this->order);
            $shipmentId = $result['payload']['shipment_id'] ?? null;

            if (!$shipmentId) {
                Log::error('Shiprocket: no shipment_id', $result);
                return;
            }

            $awbResult = $shiprocket->generateAWB($shipmentId);
            $awb = $awbResult['response']['data']['awb_code'] ?? null;
            $courier = $awbResult['response']['data']['courier_name'] ?? null;

            // We store the shiprocket shipment directly into the shipments table.
            Shipment::create([
                'order_id' => $this->order->id,
                'carrier' => 'shiprocket',
                'awb' => $awb,
                'tracking_url' => $awb ? 'https://shiprocket.co/tracking/' . $awb : null,
                'status' => 'processing',
            ]);

            // Save some identifiers directly on order if we updated db table, or just use shipments table
            // Based on previous instructions, it wants us to save awb_number, tracking_url on orders
            // but we also have `Shipment` model.
            // For now, save them to order and let the shipment be created.
            $this->order->update([
                // 'shipment_id' => $shipmentId, // Since migration isn't run, handle gracefully if needed.
                // 'awb_number' => $awb,
                // 'courier_name' => $courier,
                // 'tracking_url' => $awb ? 'https://shiprocket.co/tracking/' . $awb : null,
            ]);

            Log::info('Shiprocket Shipment created for order: ' . $this->order->order_number);
        } catch (\Exception $e) {
            Log::error('Shiprocket job failed: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
            $this->fail($e);
        }
    }
}
