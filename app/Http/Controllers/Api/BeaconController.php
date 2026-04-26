<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsEvent;
use App\Models\Visitor;
use Illuminate\Support\Facades\Log;

class BeaconController extends Controller
{
    public function track(Request $request)
    {
        try {
            $visitorUuid = $request->cookie('da_vid');
            $sessionUuid = $request->cookie('da_sid');

            if (! $visitorUuid || ! $sessionUuid) {
                return response()->json(['status' => 'ignored', 'reason' => 'missing_cookies']);
            }

            // Quick lookup
            $session = AnalyticsSession::where('session_uuid', $sessionUuid)->first();
            if (! $session) {
                return response()->json(['status' => 'ignored', 'reason' => 'invalid_session']);
            }

            $visitorId = $session->visitor_id;

            // Rate limit: max 100 events per session
            if ($session->event_count >= 100) {
                return response()->json(['status' => 'ignored', 'reason' => 'rate_limit']);
            }

            $payload = json_decode($request->getContent(), true);
            if (! $payload || ! isset($payload['event_name'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            $eventName = $payload['event_name'];
            $meta = $payload['meta'] ?? [];
            $pageUrl = $payload['page_url'] ?? null;
            $pageType = $payload['page_type'] ?? null;
            
            // Extract product/variant id from meta if present
            $productId = $meta['product_id'] ?? null;
            $variantId = $meta['variant_id'] ?? null;

            // Identity Stitching & Revenue Tracking
            if ($eventName === 'purchase' && isset($meta['revenue'])) {
                $session->reached_purchase = true;
                $session->revenue = (float) $meta['revenue'];
                
                // Update visitor LTV
                Visitor::where('id', $visitorId)->increment('total_orders');
                Visitor::where('id', $visitorId)->increment('total_revenue', (float) $meta['revenue']);
            }

            // Update Funnel Flags
            $updates = [
                'event_count' => $session->event_count + 1,
                'exit_page' => mb_substr($pageUrl, 0, 500),
                'ended_at' => now(),
                'duration_seconds' => now()->diffInSeconds($session->started_at),
            ];

            // If it's a page_view, update page count and remove bounce flag
            if ($eventName === 'page_view') {
                $updates['page_count'] = $session->page_count + 1;
            }
            
            // Un-bounce if the user is interacting (scroll, cart, checkout, page_view > 1)
            if ($session->page_count > 1 || in_array($eventName, ['scroll_25', 'scroll_50', 'scroll_75', 'add_to_cart', 'checkout_start', 'purchase', 'search'])) {
                $updates['is_bounce'] = false;
            }

            if ($eventName === 'product_view') $updates['reached_product'] = true;
            if ($eventName === 'cart_view' || $eventName === 'add_to_cart') $updates['reached_cart'] = true;
            if ($eventName === 'checkout_start') $updates['reached_checkout'] = true;

            $session->update($updates);

            // Record Event
            AnalyticsEvent::create([
                'session_id' => $session->id,
                'visitor_id' => $visitorId,
                'event_name' => mb_substr($eventName, 0, 50),
                'page_url' => mb_substr($pageUrl, 0, 500),
                'page_type' => mb_substr($pageType, 0, 30),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'meta' => $meta,
            ]);

            return response()->json(['status' => 'success']);

        } catch (\Throwable $e) {
            Log::warning('Beacon API error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
