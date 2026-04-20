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

    public function createOrder(Order $order): array
    {
        $creds = $this->getCredentials();

        $products = $order->orderItems->map(fn($item) => [
            'product_name' => $item->product_name_snapshot ?? 'Product',
            'product_sku' => $item->sku_snapshot ?? 'SKU-'.$item->id,
            'product_quantity' => (string) $item->qty,
            'product_price' => (string) $item->unit_price,
            'product_tax_rate' => (string) ($order->gst_rate ?? 0),
            'product_hsn_code' => '',
            'product_discount' => '0'
        ])->toArray();

        $payload = [
            'data' => [
                'access_token' => $creds['access_token'],
                'secret_key'   => $creds['secret_key'],
                'shipments'    => [
                    [
                        'order'                 => (string) $order->order_number,
                        'sub_order'             => '',
                        'order_date'            => $order->created_at ? $order->created_at->format('d-m-Y H:i:s') : now()->format('d-m-Y H:i:s'),
                        'total_amount'          => (string) $order->grand_total,
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
                        'shipment_length'       => '10',
                        'shipment_width'        => '10',
                        'shipment_height'       => '10',
                        'weight'                => '0.5',
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
                    ]
                ]
            ]
        ];

        Log::info('iThink Push Payload', $payload);

        $res = Http::post($this->baseUrl . '/order/sync.json', $payload);

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
                'awb_numbers'  => (string) $orderId // Here orderId usually refers to AWB in iThink cancellation
            ]
        ];

        $res = Http::post($this->baseUrl . '/order/cancel.json', $payload);
        return $res->successful() && $res->json('status_code') == 1;
    }
}
