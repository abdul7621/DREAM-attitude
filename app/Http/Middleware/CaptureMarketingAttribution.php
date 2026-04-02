<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    private const KEYS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'gclid', 'fbclid',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        foreach (self::KEYS as $key) {
            if ($request->filled($key) && ! session()->has('attr_'.$key)) {
                session(['attr_'.$key => mb_substr((string) $request->input($key), 0, 255)]);
            }
        }

        return $next($request);
    }
}
