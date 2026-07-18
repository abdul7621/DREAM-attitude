<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NimbusWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = config('services.nimbus.webhook_token');
        if ($token && $request->header('x-api-key') !== $token && $request->input('token') !== $token) {
            Log::warning('NimbusPost Webhook Unauthorized', ['ip' => $request->ip()]);
            abort(401);
        }

        Log::info('NimbusPost Webhook Received', $request->all());

        // Parse fields from payload
        // NimbusPost payload can have attributes at top-level or inside a nested 'payload' key
        $data = $request->input('payload') ?? $request->all();

        $awb = $data['awb'] ?? $data['awb_number'] ?? $data['waybill'] ?? $data['awb_no'] ?? null;
        $status = $data['status'] ?? $data['current_status'] ?? $data['tracking_status'] ?? null;
        $orderNo = $data['order_number'] ?? $data['order_id'] ?? $data['ref_id'] ?? null;

        $shipment = null;
        if ($awb) {
            $shipment = Shipment::where('awb', $awb)->first();
        }

        // Fallback to searching by order number if AWB is missing or not matched
        if (!$shipment && $orderNo) {
            $orderModel = \App\Models\Order::where('order_number', $orderNo)->first();
            if ($orderModel) {
                // Find shipment linked to this order
                $shipment = $orderModel->shipments()->where('carrier', 'nimbus')->first();
                if (!$shipment) {
                    // Fallback to first shipment of this order
                    $shipment = $orderModel->shipments()->first();
                }
                
                if ($shipment && $awb && !$shipment->awb) {
                    $shipment->update(['awb' => $awb]);
                }
            }
        }

        if (!$shipment) {
            Log::warning('NimbusPost Webhook: Shipment not found', ['awb' => $awb, 'order_number' => $orderNo]);
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $internalStatus = $this->mapNimbusStatusToInternal($status);

        $shipment->update([
            'status' => $internalStatus
        ]);

        if ($internalStatus === 'delivered') {
            $order = $shipment->order;
            if ($order && $order->order_status !== \App\Models\Order::ORDER_STATUS_DELIVERED) {
                $oldStatus = $order->order_status;
                $order->update(['order_status' => \App\Models\Order::ORDER_STATUS_DELIVERED]);
                
                if (class_exists(\App\Events\OrderStatusChanged::class)) {
                    event(new \App\Events\OrderStatusChanged($order, $oldStatus, \App\Models\Order::ORDER_STATUS_DELIVERED, 'Updated via NimbusPost webhook'));
                }
            }
        }

        return response()->json(['message' => 'Webhook Processed']);
    }

    private function mapNimbusStatusToInternal(?string $nimbusStatus): string
    {
        if (!$nimbusStatus) return 'processing';

        $lower = strtolower(trim($nimbusStatus));

        switch ($lower) {
            case 'delivered':
                return 'delivered';
            case 'rto':
            case 'returned':
            case 'rto delivered':
            case 'rto-del':
                return 'rto';
            case 'cancelled':
            case 'cancel':
                return 'cancelled';
            case 'in transit':
            case 'intransit':
            case 'shipped':
            case 'dispatched':
            case 'transit':
                return 'shipped';
            case 'out for delivery':
            case 'out-for-delivery':
                return 'out_for_delivery';
            case 'pickup scheduled':
            case 'manifested':
            case 'ready to ship':
                return 'processing';
            default:
                return str_replace(' ', '_', $lower);
        }
    }
}
