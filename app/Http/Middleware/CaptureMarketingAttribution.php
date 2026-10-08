<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    private const KEYS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'gclid', 'fbclid', 'ref', 'coupon',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::KEYS as $key) {
            if ($request->filled($key) && ! session()->has('attr_'.$key)) {
                session(['attr_'.$key => mb_substr((string) $request->input($key), 0, 255)]);
            }
        }

        // Auto-capture referral coupon code from ?ref= or ?coupon=
        $refCode = $request->input('ref') ?: $request->input('coupon');
        if (!empty($refCode)) {
            $code = strtoupper(trim((string) $refCode));
            session(['referral_coupon_code' => $code]);

            try {
                app(\App\Services\CartService::class)->applyCouponCode($code);
            } catch (\Throwable $e) {
                // Silently skip if cart is empty or conditions not yet met
            }
        }

        return $next($request);
    }
}
