<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\SettingsService;
use App\Services\ShiprocketService;
use App\Services\IthinkLogisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly Order $order) {}

    public function handle(SettingsService $settings, ShiprocketService $shiprocket, IthinkLogisticsService $ithink): void
    {
        $activeProvider = $settings->get('shipping.active_provider', 'shiprocket');

        // Determine Primary and Fallback provider instances based on the database setting
        if ($activeProvider === 'ithink') {
            $primaryName = 'ithink';
            $primaryApi = $ithink;
            $fallbackName = 'shiprocket';
            $fallbackApi = $shiprocket;
        } else {
            $primaryName = 'shiprocket';
            $primaryApi = $shiprocket;
            $fallbackName = 'ithink';
            $fallbackApi = $ithink;
        }

        try {
            Log::info("Attempting to push order {$this->order->order_number} to Primary: {$primaryName}");
            $this->pushToProvider($primaryApi, $primaryName);
        } catch (\Exception $e) {
            Log::critical("Primary Shipping Provider [{$primaryName}] FAILED for order {$this->order->order_number}: " . $e->getMessage());

            // FAILOVER SYSTEM
            try {
                Log::info("Attempting to push order {$this->order->order_number} to Fallback: {$fallbackName}");
                $this->pushToProvider($fallbackApi, $fallbackName);
            } catch (\Exception $e2) {
                Log::critical("Fallback Shipping Provider [{$fallbackName}] ALSO FAILED for order {$this->order->order_number}: " . $e2->getMessage());
                // Both failed, throw exception to trigger job retry
                throw new \Exception("Both Primary and Fallback shipment creation failed.");
            }
        }
    }

    private function pushToProvider($providerApi, string $providerName): void
    {
        $result = $providerApi->createOrder($this->order);
        
        $awb = null;
        $trackingUrl = null;

        if ($providerName === 'ithink') {
            // iThink Logistics v3 API Response Format
            // If status_code == 1, there is a 'data' array containing exactly the waybill info
            $awb = $result['data'][1]['waybill'] ?? $result['data'][0]['waybill'] ?? null;
            // Sometimes it arrays by index
            if (!$awb) {
                // Dig into the structure
                foreach (($result['data'] ?? []) as $key => $shipData) {
                    if (is_array($shipData) && isset($shipData['waybill'])) {
                        $awb = $shipData['waybill'];
                        break;
                    }
                }
            }
            $trackingUrl = $awb ? 'https://ithinklogistics.com/track/' . $awb : null;
            
        } elseif ($providerName === 'shiprocket') {
            $shipmentId = $result['payload']['shipment_id'] ?? null;
            if (!$shipmentId) {
                throw new \Exception("No shipment_id found in Shiprocket response");
            }
            $awbResult = $providerApi->generateAWB($shipmentId);
            $awb = $awbResult['response']['data']['awb_code'] ?? null;
            $trackingUrl = $awb ? 'https://shiprocket.co/tracking/' . $awb : null;
        }

        Shipment::create([
            'order_id' => $this->order->id,
            'carrier'  => $providerName,
            'awb'      => $awb,
            'tracking_url' => $trackingUrl,
            'status'   => 'processing',
        ]);

        Log::info("Successfully pushed order {$this->order->order_number} to {$providerName}. AWB: {$awb}");
    }
}
