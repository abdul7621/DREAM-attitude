<?php

namespace App\Services;

use App\Models\Order;
use App\Contracts\ShippingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IthinkLogisticsService implements ShippingProviderInterface
{
    private string $baseUrl = 'https://my.ithinklogistics.com/api_v3';

    private function getCredentials(): array
    {
        $ss = app(SettingsService::class);
        $accessToken = $ss->get('shipping.ithink_access_token');
        $secretKey = $ss->get('shipping.ithink_secret_key');

        if (!$accessToken || !$secretKey) {
            throw new \Exception('iThink Logistics credentials are not configured in Admin Settings.');
        }

        return [
            'access_token' => $accessToken,
            'secret_key' => $secretKey,
        ];
    }

    // ── Smart Courier Selection Engine ─────────────────────────

    /**
     * Fetch live courier rates from iThink Rate API.
     *
     * @return array{couriers: array, zone: string, expected_delivery: string}
     */
    public function getRates(string $toPincode, string $paymentMethod, float $productMrp): array
    {
        $creds = $this->getCredentials();
        $ss = app(SettingsService::class);

        $payload = [
            'data' => [
                'from_pincode'        => $ss->get('shipping.origin_pincode', '395003'),
                'to_pincode'          => $toPincode,
                'shipping_length_cms' => $ss->get('shipping.default_length_cm', '10'),
                'shipping_width_cms'  => $ss->get('shipping.default_width_cm', '10'),
                'shipping_height_cms' => $ss->get('shipping.default_height_cm', '10'),
                'shipping_weight_kg'  => $ss->get('shipping.default_weight_kg', '0.5'),
                'order_type'          => 'forward',
                'payment_method'      => strtolower($paymentMethod) === 'cod' ? 'cod' : 'prepaid',
                'product_mrp'         => number_format($productMrp, 2, '.', ''),
                'access_token'        => $creds['access_token'],
                'secret_key'          => $creds['secret_key'],
            ]
        ];

        Log::info('[SMART_COURIER] Fetching rates', ['to' => $toPincode, 'method' => $paymentMethod]);

        $res = Http::timeout(8)->post($this->baseUrl . '/rate/check.json', $payload);

        if (!$res->successful()) {
            Log::warning('[SMART_COURIER] Rate API HTTP error', ['status' => $res->status()]);
            return ['couriers' => [], 'zone' => '', 'expected_delivery' => ''];
        }

        $json = $res->json();

        if (($json['status'] ?? '') !== 'success') {
            Log::warning('[SMART_COURIER] Rate API returned non-success', ['response' => $json]);
            return ['couriers' => [], 'zone' => '', 'expected_delivery' => ''];
        }

        // Parse courier data from response
        $couriers = [];
        foreach (($json['data'] ?? []) as $courier) {
            if (!is_array($courier)) continue;
            $couriers[] = [
                'name'         => $courier['logistic_name'] ?? 'Unknown',
                'rate'         => (float) ($courier['rate'] ?? 0),
                'delivery_tat' => $courier['delivery_tat'] ?? '—',
                'cod'          => ($courier['cod'] ?? 'N') === 'Y',
                'prepaid'      => ($courier['prepaid'] ?? 'N') === 'Y',
                'pickup'       => ($courier['pickup'] ?? 'N') === 'Y',
                'zone'         => $courier['logistics_zone'] ?? '',
            ];
        }

        // Sort by rate ascending (cheapest first)
        usort($couriers, fn($a, $b) => $a['rate'] <=> $b['rate']);

        Log::info('[SMART_COURIER] Rates fetched', [
            'count' => count($couriers),
            'couriers' => array_map(fn($c) => $c['name'] . ':₹' . $c['rate'], $couriers),
        ]);

        return [
            'couriers'          => $couriers,
            'zone'              => $json['zone'] ?? '',
            'expected_delivery' => $json['expected_delivery_date'] ?? '',
        ];
    }

    /**
     * Select the best courier from rate results.
     * Logic: cheapest courier that supports the payment method and has pickup available.
     */
    public function selectBestCourier(array $rateResult, string $paymentMethod): ?array
    {
        $couriers = $rateResult['couriers'] ?? [];
        if (empty($couriers)) return null;

        $isCod = strtolower($paymentMethod) === 'cod';

        // Filter: must support payment method + pickup
        $eligible = array_filter($couriers, function ($c) use ($isCod) {
            if ($isCod && !$c['cod']) return false;
            if (!$isCod && !$c['prepaid']) return false;
            if (!$c['pickup']) return false;
            return true;
        });

        if (empty($eligible)) {
            Log::warning('[SMART_COURIER] No eligible courier found, returning cheapest overall');
            return $couriers[0] ?? null; // Fallback to cheapest even if filter fails
        }

        // Already sorted by rate, so first eligible = best
        $best = reset($eligible);

        Log::info('[SMART_COURIER] Best courier selected', [
            'courier' => $best['name'],
            'rate'    => $best['rate'],
            'tat'     => $best['delivery_tat'],
        ]);

        return $best;
    }

    // ── Order Creation ────────────────────────────────────────

    public function createOrder(Order $order, ?string $forceLogisticName = null): array
    {
        $creds = $this->getCredentials();
        $ss = app(SettingsService::class);

        $products = $order->orderItems->map(fn($item) => [
            'product_name' => $item->product_name_snapshot ?? 'Product',
            'product_sku' => $item->sku_snapshot ?? 'SKU-'.$item->id,
            'product_quantity' => (string) $item->qty,
            'product_price' => (string) $item->unit_price,
            'product_tax_rate' => (string) ($order->gst_rate ?? 0),
            'product_hsn_code' => '',
            'product_discount' => '0'
        ])->toArray();

        $shipmentData = [
            'order'                 => (string) $order->order_number,
            'sub_order'             => '',
            'order_date'            => $order->created_at ? $order->created_at->format('d-m-Y H:i:s') : now()->format('d-m-Y H:i:s'),
            'total_amount'          => (string) $order->subtotal,
            'name'                  => $order->customer_name,
            'company_name'          => '',
            'add'                   => $order->address_line1 . ($order->address_line2 ? ', ' . $order->address_line2 : ''),
            'add2'                  => '',
            'add3'                  => '',
            'pin'                   => (string) $order->postal_code,
            'city'                  => $order->city,
            'state'                 => $order->state,
            'country'               => 'India',
            'phone'                 => (string) $order->phone,
            'alt_phone'             => '',
            'email'                 => $order->email ?? '',
            'is_billing_same_as_shipping' => 'yes',
            'billing_name'          => $order->customer_name,
            'billing_company_name'  => '',
            'billing_add'           => $order->address_line1 . ($order->address_line2 ? ', ' . $order->address_line2 : ''),
            'billing_add2'          => '',
            'billing_add3'          => '',
            'billing_pin'           => (string) $order->postal_code,
            'billing_city'          => $order->city,
            'billing_state'         => $order->state,
            'billing_country'       => 'India',
            'billing_phone'         => (string) $order->phone,
            'billing_alt_phone'     => '',
            'billing_email'         => $order->email ?? '',
            'products'              => $products,
            'shipment_length'       => $ss->get('shipping.default_length_cm', '10'),
            'shipment_width'        => $ss->get('shipping.default_width_cm', '10'),
            'shipment_height'       => $ss->get('shipping.default_height_cm', '10'),
            'weight'                => $ss->get('shipping.default_weight_kg', '0.5'),
            'shipping_charges'      => '0',
            'giftwrap_charges'      => '0',
            'transaction_charges'   => '0',
            'total_discount'        => '0',
            'first_attemp_discount' => '0',
            'cod_amount'            => $order->payment_method === 'cod' ? (string) $order->grand_total : '0',
            'cod_charges'           => '0',
            'advance_amount'        => '0',
            'payment_mode'          => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'reseller_name'         => '',
            'eway_bill_number'      => '',
            'gst_number'            => ''
        ];

        // Smart Courier: if a specific logistics partner was selected, force it
        if ($forceLogisticName) {
            $shipmentData['logistic_name'] = $forceLogisticName;
            Log::info('[SMART_COURIER] Forcing courier in order creation', ['courier' => $forceLogisticName]);
        }

        $payload = [
            'data' => [
                'access_token' => $creds['access_token'],
                'secret_key'   => $creds['secret_key'],
                'shipments'    => [$shipmentData]
            ]
        ];

        Log::info('iThink Push Payload', $payload);

        $res = Http::post($this->baseUrl . '/order/sync.json', $payload);

        Log::info('iThink API Response', ['status' => $res->status(), 'body' => $res->json()]);

        if (!$res->successful()) {
            throw new \Exception('iThink createOrder failed HTTP Error: ' . $res->body());
        }

        $json = $res->json();
        if (!in_array($json['status_code'] ?? 0, [1, 200, 201, '200', '201', '1'])) { 
            throw new \Exception('iThink createOrder API Error: ' . json_encode($json));
        }

        return $json;
    }

    public function generateAWB($shipmentId): array
    {
        // iThink automatically assigns AWB on order creation.
        // We will just return it from the order check or it might already be in createOrder response.
        return [];
    }

    public function generateLabel($shipmentId): string
    {
        // iThink manifest/label API can be integrated here later if needed.
        return '';
    }

    public function trackOrder(string $awb): array
    {
        // For polling. We rely on Webhooks primarily.
        return [];
    }

    public function cancelShipment($orderId): bool
    {
        $creds = $this->getCredentials();
        
        $payload = [
            'data' => [
                'access_token' => $creds['access_token'],
                'secret_key'   => $creds['secret_key'],
                'awb_numbers'  => (string) $orderId
            ]
        ];

        $res = Http::post($this->baseUrl . '/order/cancel.json', $payload);
        return $res->successful() && $res->json('status_code') == 1;
    }
}
