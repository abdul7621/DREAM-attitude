<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShiprocketService
{
    private string $baseUrl = 'https://apiv2.shiprocket.in/v1/external';

    private function token(): string
    {
        return Cache::remember('shiprocket_token', now()->addHours(23), function () {
            $ss = app(SettingsService::class);
            $res = Http::post($this->baseUrl . '/auth/login', [
                'email' => $ss->get('shipping.shiprocket_email'),
                'password' => $ss->get('shipping.shiprocket_password'),
            ]);
            if (!$res->successful()) {
                throw new \Exception('Shiprocket auth failed: ' . $res->body());
            }
            return $res->json('token');
        });
    }

    public function createOrder(Order $order): array
    {
        $items = $order->orderItems->map(fn($i) => [
            'name' => $i->product_name_snapshot,
            'sku' => $i->sku_snapshot ?? 'SKU-' . $i->id,
            'units' => $i->qty,
            'selling_price' => $i->unit_price,
        ])->toArray();

        $res = Http::withToken($this->token())
            ->post($this->baseUrl . '/orders/create/adhoc', [
                'order_id' => (string) $order->order_number,
                'order_date' => $order->created_at->format('Y-m-d H:i'),
                'pickup_location' => 'Primary',
                'billing_customer_name' => $order->customer_name,
                'billing_address' => $order->address_line1,
                'billing_city' => $order->city,
                'billing_pincode' => $order->postal_code,
                'billing_state' => $order->state,
                'billing_country' => 'India',
                'billing_email' => $order->email ?? '',
                'billing_phone' => $order->phone,
                'shipping_is_billing' => true,
                'order_items' => $items,
                'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'sub_total' => $order->grand_total,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => 0.5,
            ]);

        if (!$res->successful()) {
            throw new \Exception('Shiprocket createOrder failed: ' . $res->body());
        }

        return $res->json();
    }

    public function generateAWB(int $shipmentId): array
    {
        $res = Http::withToken($this->token())
            ->post($this->baseUrl . '/courier/assign/awb', [
                'shipment_id' => $shipmentId,
            ]);

        if (!$res->successful()) {
            throw new \Exception('AWB generation failed: ' . $res->body());
        }

        return $res->json();
    }

    public function generateLabel(int $shipmentId): string
    {
        $res = Http::withToken($this->token())
            ->post($this->baseUrl . '/courier/generate/label', [
                'shipment_id' => [$shipmentId],
            ]);

        return $res->json('label_url', '');
    }

    public function trackOrder(string $awb): array
    {
        $res = Http::withToken($this->token())
            ->get($this->baseUrl . '/courier/track/awb/' . $awb);

        return $res->json() ?? [];
    }

    public function cancelShipment(int $orderId): bool
    {
        $res = Http::withToken($this->token())
            ->post($this->baseUrl . '/orders/cancel', [
                'ids' => [$orderId],
            ]);

        return $res->successful();
    }
}
