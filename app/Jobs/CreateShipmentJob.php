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

        // ── Smart Courier Selection (COD + iThink only) ──────────
        $smartCourierData = null;
        $forceLogisticName = null;

        $smartEnabled = $settings->get('shipping.smart_courier_enabled', '0') === '1';

        if ($smartEnabled && $primaryName === 'ithink' && $this->order->payment_method === 'cod') {
            try {
                Log::info("[SMART_COURIER] Running smart selection for order {$this->order->order_number}");

                $rateResult = $ithink->getRates(
                    $this->order->postal_code,
                    'cod',
                    (float) $this->order->grand_total
                );

                $bestCourier = $ithink->selectBestCourier($rateResult, 'cod');

                if ($bestCourier) {
                    $forceLogisticName = $bestCourier['name'];
                    $smartCourierData = [
                        'smart_courier_used' => true,
                        'selected_courier'   => $bestCourier['name'],
                        'carrier_cost'       => $bestCourier['rate'],
                        'delivery_tat'       => $bestCourier['delivery_tat'],
                        'zone'               => $rateResult['zone'] ?? '',
                        'all_rates'          => array_map(fn($c) => [
                            'name' => $c['name'],
                            'rate' => $c['rate'],
                            'tat'  => $c['delivery_tat'],
                        ], $rateResult['couriers']),
                        'shipping_charged'   => (float) $this->order->shipping_total,
                        'shipping_margin'    => (float) $this->order->shipping_total - $bestCourier['rate'],
                    ];

                    Log::info("[SMART_COURIER] Order {$this->order->order_number}: {$bestCourier['name']} ₹{$bestCourier['rate']} (charged ₹{$this->order->shipping_total}, margin ₹" . round($smartCourierData['shipping_margin'], 2) . ")");
                }
            } catch (\Exception $e) {
                Log::warning("[SMART_COURIER] Rate fetch failed, proceeding without smart selection: " . $e->getMessage());
                // Continue without smart selection — not a blocker
            }
        }

        try {
            Log::info("Attempting to push order {$this->order->order_number} to Primary: {$primaryName}");
            $this->pushToProvider($primaryApi, $primaryName, $smartCourierData, $forceLogisticName);
        } catch (\Exception $e) {
            Log::critical("Primary Shipping Provider [{$primaryName}] FAILED for order {$this->order->order_number}: " . $e->getMessage());

            // FAILOVER SYSTEM
            try {
                Log::info("Attempting to push order {$this->order->order_number} to Fallback: {$fallbackName}");
                $this->pushToProvider($fallbackApi, $fallbackName, $smartCourierData);
            } catch (\Exception $e2) {
                Log::critical("Fallback Shipping Provider [{$fallbackName}] ALSO FAILED for order {$this->order->order_number}: " . $e2->getMessage());
                // Both failed, throw exception to trigger job retry
                throw new \Exception("Both Primary and Fallback shipment creation failed.");
            }
        }
    }

    private function pushToProvider($providerApi, string $providerName, ?array $smartCourierData = null, ?string $forceLogisticName = null): void
    {
        // Pass forced courier name only to iThink
        if ($providerName === 'ithink' && $forceLogisticName) {
            $result = $providerApi->createOrder($this->order, $forceLogisticName);
        } else {
            $result = $providerApi->createOrder($this->order);
        }
        
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

        // Build meta with smart courier intelligence data
        $meta = $smartCourierData ?? [];

        Shipment::create([
            'order_id' => $this->order->id,
            'carrier'  => $providerName,
            'awb'      => $awb,
            'tracking_url' => $trackingUrl,
            'status'   => 'processing',
            'meta'     => !empty($meta) ? $meta : null,
        ]);

        Log::info("Successfully pushed order {$this->order->order_number} to {$providerName}. AWB: {$awb}");
    }
}

