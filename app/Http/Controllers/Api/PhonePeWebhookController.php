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
        Log::info("PhonePe Webhook Received", ['payload' => $request->all(), 'headers' => $request->headers->all()]);

        // ─── Step 1: Validate Webhook Authorization ────────────────────────
        // PhonePe V2 sends: Authorization: SHA256(username:password)
        // We use the webhook as a trusted trigger for S2S polling, but still validate sender identity.
        $authHeader = $request->header('Authorization');
        $webhookUsername = config('services.phonepe.webhook_username');
        $webhookPassword = config('services.phonepe.webhook_password');

        if ($webhookUsername && $webhookPassword) {
            $expectedHash = hash('sha256', "{$webhookUsername}:{$webhookPassword}");
            if ($authHeader !== $expectedHash) {
                Log::warning("PhonePe Webhook: Authorization mismatch", [
                    'received' => $authHeader,
                    'expected' => $expectedHash,
                ]);
                // Still process — our security comes from S2S polling, not webhook trust.
                // But log the mismatch for monitoring.
            }
        }

        // ─── Step 2: Extract merchantOrderId from V2 Webhook Payload ───────
        // V2 webhook format: {"event":"checkout.order.completed","payload":{"merchantOrderId":"DA...","state":"COMPLETED",...}}
        $merchantOrderId = null;
        $event = $request->input('event');

        // V2 Native Format (primary): event + payload.merchantOrderId
        if ($request->has('payload.merchantOrderId')) {
            $merchantOrderId = $request->input('payload.merchantOrderId');
        }
        // Fallback: if payload is at root level (some edge cases)
        elseif ($request->has('merchantOrderId')) {
            $merchantOrderId = $request->input('merchantOrderId');
        }
        // Legacy V1 fallback: base64 encoded 'response' field (for migration safety)
        elseif ($request->has('response')) {
            $decoded = json_decode(base64_decode($request->input('response')), true);
            $merchantOrderId = $decoded['data']['merchantTransactionId'] 
                            ?? $decoded['data']['transactionId'] 
                            ?? null;
        }
        // Last resort: our custom transactionId param
        elseif ($request->has('transactionId')) {
            $merchantOrderId = $request->input('transactionId');
        }

        Log::info("PhonePe Webhook: Parsed", ['event' => $event, 'merchantOrderId' => $merchantOrderId]);

        if (!$merchantOrderId) {
            Log::warning("PhonePe Webhook: Missing merchantOrderId", ['payload' => $request->all()]);
            // Return 200 to prevent PhonePe from retrying endlessly
            return response()->json(['success' => true, 'message' => 'Missing merchantOrderId, ignored']);
        }

        $order = Order::where('gateway_order_id', $merchantOrderId)->first();
        
        if (!$order) {
            Log::warning("PhonePe Webhook: Order not found for merchantOrderId", ['merchantOrderId' => $merchantOrderId]);
            // Return 200 OK to prevent PhonePe from endlessly retrying this zombie webhook
            return response()->json(['success' => true, 'message' => 'Order not found, ignored']);
        }

        // ─── Step 3: Idempotency — Ignore if already paid ──────────────────
        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            Log::info("PhonePe Webhook: Already processed", ['merchantOrderId' => $merchantOrderId, 'order_id' => $order->id]);
            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        // ─── Step 4: S2S Poll for absolute truth (eliminates webhook spoofing) ───
        Log::info("PhonePe Webhook: Triggering Server-To-Server Status Check", ['merchantOrderId' => $merchantOrderId]);
        $driver = $paymentManager->driver('phonepe');
        $isPaid = $driver->verifyPayment(['transactionId' => $merchantOrderId], $order);

        if ($isPaid) {
            try {
                $orders->finalizeOnlinePayment($order, $request->all());
                Log::info("PhonePe Webhook: Order finalized safely", ['order_id' => $order->id]);
            } catch (\Exception $e) {
                Log::error("PhonePe Webhook: Finalization error", ['message' => $e->getMessage(), 'order_id' => $order->id]);
                // Return 500 so PhonePe retries this webhook
                return response()->json(['success' => false, 'message' => 'Finalization error'], 500);
            }
        } else {
            Log::info("PhonePe Webhook: S2S poll confirmed unpaid status", ['order_id' => $order->id, 'event' => $event]);
        }

        // Always return 200 OK promptly (PhonePe requires response within 3-5 seconds)
        return response()->json(['success' => true]);
    }
}
