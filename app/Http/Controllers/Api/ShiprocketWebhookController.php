<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiprocketWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // For security, ideally check Shiprocket signature here.
        // Shiprocket typically sends x-api-key or JWT depending on configuration.
        // For this demo, we'll assume the payload is valid.

        Log::info('Shiprocket Webhook Received', $request->all());

        $awb = $request->input('awb');
        $status = $request->input('current_status'); // e.g. "DELIVERED", "IN TRANSIT"

        if (!$awb) {
            return response()->json(['message' => 'Missing AWB'], 422);
        }

        $shipment = Shipment::where('awb', $awb)->first();

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $normalizedStatus = strtolower(str_replace(' ', '_', $status ?? ''));

        $shipment->update([
            'status' => $normalizedStatus
        ]);

        if (strtoupper($status) === 'DELIVERED') {
            $order = $shipment->order;
            if ($order && $order->order_status !== \App\Models\Order::ORDER_STATUS_DELIVERED) {
                $oldStatus = $order->order_status;
                $order->update(['order_status' => \App\Models\Order::ORDER_STATUS_DELIVERED]);
                
                // Fire OrderStatusChanged Event without errors safely if it exists
                if (class_exists(\App\Events\OrderStatusChanged::class)) {
                    event(new \App\Events\OrderStatusChanged($order, $oldStatus, \App\Models\Order::ORDER_STATUS_DELIVERED, 'Updated via Shiprocket webhook'));
                }
            }
        }

        return response()->json(['message' => 'Webhook Processed']);
    }
}
