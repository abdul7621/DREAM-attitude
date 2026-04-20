<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IthinkWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('iThink Webhook Received', $request->all());

        // iThink structure usually sends array of updates or single object
        // Example: ['awb_no' => '...', 'status' => '...', 'order_no' => '...']
        $awb = $request->input('awb_no') ?? $request->input('awb_number') ?? $request->input('waybill');
        $status = $request->input('status') ?? $request->input('current_status'); // e.g. "Delivered", "In Transit"

        if (!$awb) {
            // Dig into nested structures if it's nested
            $data = $request->input('data', []);
            if (!empty($data) && is_array($data)) {
                $item = isset($data[0]) ? $data[0] : $data;
                $awb = $item['awb_no'] ?? $item['awb_number'] ?? $item['waybill'] ?? null;
                $status = $item['status'] ?? $item['current_status'] ?? null;
            }
        }

        $shipment = null;
        if ($awb) {
            $shipment = Shipment::where('awb', $awb)->first();
        }

        // Fallback to searching by order number if AWB is missing from our DB
        if (!$shipment) {
            $orderNo = $request->input('order_no') ?? $request->input('order_number') ?? $request->input('refnum');
            if (!$orderNo && isset($data) && is_array($data)) {
                $item = isset($data[0]) ? $data[0] : $data;
                $orderNo = $item['order_no'] ?? $item['order_number'] ?? $item['refnum'] ?? null;
            }

            if ($orderNo) {
                $orderModel = \App\Models\Order::where('order_number', $orderNo)->first();
                if ($orderModel) {
                    $shipment = $orderModel->shipments()->where('carrier', 'ithink')->first();
                    if ($shipment && $awb && !$shipment->awb) {
                        $shipment->update(['awb' => $awb]);
                    }
                }
            }
        }

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $internalStatus = $this->mapIthinkStatusToInternal($status);

        $shipment->update([
            'status' => $internalStatus
        ]);

        if ($internalStatus === 'delivered') {
            $order = $shipment->order;
            if ($order && $order->order_status !== \App\Models\Order::ORDER_STATUS_DELIVERED) {
                $oldStatus = $order->order_status;
                $order->update(['order_status' => \App\Models\Order::ORDER_STATUS_DELIVERED]);
                
                if (class_exists(\App\Events\OrderStatusChanged::class)) {
                    event(new \App\Events\OrderStatusChanged($order, $oldStatus, \App\Models\Order::ORDER_STATUS_DELIVERED, 'Updated via iThink webhook'));
                }
            }
        } elseif ($internalStatus === 'cancelled' || $internalStatus === 'rto') {
            $order = $shipment->order;
            // Depending on logic, you might want to mark order as Cancelled or Returned
            // if ($order && !in_array($order->order_status, [\App\Models\Order::ORDER_STATUS_CANCELLED, \App\Models\Order::ORDER_STATUS_RETURNED])) {
            //     $order->update(['order_status' => \App\Models\Order::ORDER_STATUS_RETURNED]);
            // }
        }

        return response()->json(['message' => 'Webhook Processed']);
    }

    private function mapIthinkStatusToInternal(?string $iThinkStatus): string
    {
        if (!$iThinkStatus) return 'processing';

        $lower = strtolower(trim($iThinkStatus));

        switch ($lower) {
            case 'delivered':
                return 'delivered';
            case 'rto':
            case 'returned':
            case 'rto delivered':
                return 'rto';
            case 'cancelled':
            case 'cancel':
                return 'cancelled';
            case 'in transit':
            case 'intransit':
            case 'shipped':
            case 'dispatched':
                return 'shipped';
            case 'out for delivery':
                return 'out_for_delivery';
            case 'pickup scheduled':
            case 'ready to ship':
                return 'processing';
            default:
                // Replace spaces with underscores
                return str_replace(' ', '_', $lower);
        }
    }
}
