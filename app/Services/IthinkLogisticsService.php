<?php

namespace App\Services;

use App\Models\Order;
use App\Contracts\ShippingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IthinkLogisticsService implements ShippingProviderInterface
{
    private string $baseUrl = 'https://api.ithinklogistics.com/api_v3';

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

        // Prepare items block if iThink requests detailed items
        // Wait, standard Add Order requires "product_name" as string or concatenated, let's keep it simple or join.
        $productNames = $order->orderItems->pluck('product_name_snapshot')->join(', ');

        $payload = [
            'data' => [
                'access_token' => $creds['access_token'],
                'secret_key'   => $creds['secret_key'],
                'shipments'    => [
                    [
                        'waybill'               => '', // Let iThink generate it
                        'order'                 => (string) $order->order_number,
                        'sub_order'             => '',
                        'order_date'            => $order->created_at->format('d-m-Y'),
                        'total_amount'          => $order->grand_total,
                        'name'                  => $order->customer_name,
                        'company_name'          => '',
                        'add'                   => $order->address_line1 . ($order->address_line2 ? ', ' . $order->address_line2 : ''),
                        'pin'                   => $order->postal_code,
                        'city'                  => $order->city,
                        'state'                 => $order->state,
                        'country'               => 'India',
                        'phone'                 => $order->phone,
                        'alt_phone'             => '',
                        'email'                 => $order->email ?? '',
                        'is_billing_same_as_shipping' => 'yes',
                        'billing_name'          => $order->customer_name,
                        'billing_company_name'  => '',
                        'billing_add'           => $order->address_line1 . ($order->address_line2 ? ', ' . $order->address_line2 : ''),
                        'billing_pin'           => $order->postal_code,
                        'billing_city'          => $order->city,
                        'billing_state'         => $order->state,
                        'billing_country'       => 'India',
                        'billing_phone'         => $order->phone,
                        'billing_alt_phone'     => '',
                        'billing_email'         => $order->email ?? '',
                        'products'              => $productNames,
                        'products_sku'          => '',
                        'quantity'              => $order->orderItems->sum('qty'),
                        'payment_mode'          => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                        'return_address_id'     => '', 
                        'length'                => '10',
                        'width'                 => '10',
                        'height'                => '10',
                        'weight'                => '0.5' // Ensure dimension matches package
                    ]
                ]
            ]
        ];

        Log::info('iThink Push Payload', $payload);

        $res = Http::post($this->baseUrl . '/order/add.json', $payload);

        if (!$res->successful()) {
            throw new \Exception('iThink createOrder failed HTTP Error: ' . $res->body());
        }

        $json = $res->json();
        if ($json['status_code'] != 1) { // iThink returns status_code == 1 for success
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
