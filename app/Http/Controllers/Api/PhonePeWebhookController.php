<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhonePeWebhookController extends Controller
{
    public function handle(Request $request, OrderService $orders, PaymentManager $paymentManager)
    {
        Log::info("PhonePe Webhook Received", $request->all());

        // Basic verification
        // While PhonePe sends Basic Auth or JWT payload in V2, we adopt a security-first S2S Polling Strategy.
        // We use the webhook simply as a ping. The source of truth is ALWAYS fetched directly from PhonePe's server securely.
        
        $transactionId = null;

        // V2 Webhook payloads usually send a base64 encoded 'response' string or clear JSON.
        // If it's a base64 encoded response like V1:
        if ($request->has('response')) {
            $decoded = json_decode(base64_decode($request->input('response')), true);
            $transactionId = $decoded['data']['transactionId'] ?? null;
        } 
        // If it's direct JSON like V2 Native:
        else if ($request->has('data.transactionId')) {
            $transactionId = $request->input('data.transactionId');
        } 
        // If standard body
        else if ($request->has('transactionId')) {
            $transactionId = $request->input('transactionId');
        }

        if (!$transactionId) {
            Log::warning("PhonePe Webhook: Missing transactionId", ['payload' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'Missing transactionId'], 400);
        }

        $order = Order::where('gateway_order_id', $transactionId)->first();
        
        if (!$order) {
            Log::warning("PhonePe Webhook: Order not found for txn", ['txn' => $transactionId]);
            // Still returning 200 OK because we don't want PhonePe to endlessly retry this zombie webhook
            return response()->json(['success' => true, 'message' => 'Order not found, ignored']);
        }

        // Idempotency: Ignore if already paid
        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            Log::info("PhonePe Webhook: Already processed", ['txn' => $transactionId]);
            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        // Force S2S poll PhonePe to get absolute truthful status (eliminates webhook spoofing)
        Log::info("PhonePe Webhook: Triggering Server-To-Server Check", ['txn' => $transactionId]);
        $driver = $paymentManager->driver('phonepe');
        $isPaid = $driver->verifyPayment(['transactionId' => $transactionId], $order);

        if ($isPaid) {
            try {
                $orders->finalizeOnlinePayment($order, $request->all());
                Log::info("PhonePe Webhook: Order finalized safely", ['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::error("PhonePe Webhook Event: Finalization error", ['message' => $e->getMessage()]);
                // If it fails on our DB layer or mailing, return 500 so PhonePe retries
                return response()->json(['success' => false, 'message' => 'Finalization error'], 500);
            }
        } else {
             Log::info("PhonePe Webhook: Poll confirmed unpaid status", ['order_id' => $order->id]);
        }

        return response()->json(['success' => true]);
    }
}
