<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\Visitor;
use App\Models\AnalyticsSession;
use Browser; // We might not have a browser parser package, so I'll write a simple fallback

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        // Exclude admin, API, and static assets
        if ($request->is('admin*') || $request->is('api*') || $request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // Basic Bot Protection (Simple User-Agent check)
        $userAgent = strtolower($request->userAgent() ?? '');
        if (str_contains($userAgent, 'bot') || str_contains($userAgent, 'spider') || str_contains($userAgent, 'crawler')) {
            return $next($request);
        }

        // 1. Resolve Visitor ID
        $visitorUuid = $request->cookie('da_vid');
        $isNewVisitor = false;

        if (! $visitorUuid) {
            $visitorUuid = (string) Str::uuid();
            $isNewVisitor = true;
            Cookie::queue('da_vid', $visitorUuid, 60 * 24 * 365); // 1 year
        }

        // 2. Resolve Session ID
        $sessionUuid = $request->cookie('da_sid');
        $isNewSession = false;

        if (! $sessionUuid) {
            $sessionUuid = (string) Str::uuid();
            $isNewSession = true;
        }
        
        // Always bump session cookie by 30 mins on activity
        Cookie::queue('da_sid', $sessionUuid, 30); 

        // Make IDs available to request for controllers/views
        $request->attributes->set('da_vid', $visitorUuid);
        $request->attributes->set('da_sid', $sessionUuid);

        // Async dispatch or DB operations (Keep it fast, just 1-2 upserts)
        $this->trackVisit($request, $visitorUuid, $sessionUuid, $isNewVisitor, $isNewSession);

        return $next($request);
    }

    private function trackVisit(Request $request, string $visitorUuid, string $sessionUuid, bool $isNewVisitor, bool $isNewSession): void
    {
        try {
            $userId = auth()->check() ? auth()->id() : null;

            // Simple device parsing
            $userAgent = $request->userAgent() ?? '';
            $deviceType = 'desktop';
            if (preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
                $deviceType = 'mobile';
            } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
                $deviceType = 'tablet';
            }

            // Visitor Upsert
            $visitor = Visitor::firstOrCreate(
                ['visitor_uuid' => $visitorUuid],
                [
                    'first_source' => session('attr_utm_source') ?: $request->query('utm_source'),
                    'first_medium' => session('attr_utm_medium') ?: $request->query('utm_medium'),
                    'first_campaign' => session('attr_utm_campaign') ?: $request->query('utm_campaign'),
                    'device_type' => $deviceType,
                    'first_seen_at' => now(),
                ]
            );

            // Identity Stitching (if user logged in but visitor record has no user_id)
            if ($userId && ! $visitor->user_id) {
                $visitor->user_id = $userId;
                $visitor->save();
            }

            // Session Upsert
            if ($isNewSession) {
                $visitor->increment('total_sessions');
                $visitor->update(['last_seen_at' => now()]);

                AnalyticsSession::create([
                    'session_uuid' => $sessionUuid,
                    'visitor_id' => $visitor->id,
                    'source' => session('attr_utm_source') ?: $request->query('utm_source'),
                    'medium' => session('attr_utm_medium') ?: $request->query('utm_medium'),
                    'campaign' => session('attr_utm_campaign') ?: $request->query('utm_campaign'),
                    'landing_page' => mb_substr($request->fullUrl(), 0, 500),
                    'referrer' => mb_substr($request->header('referer') ?? '', 0, 500),
                    'device_type' => $deviceType,
                    'started_at' => now(),
                    'ended_at' => now(),
                ]);
            } else {
                // Just update the ended_at to extend session
                AnalyticsSession::where('session_uuid', $sessionUuid)->update(['ended_at' => now()]);
                $visitor->update(['last_seen_at' => now()]);
            }

        } catch (\Throwable $e) {
            // Fail silently so we don't break the storefront if DB connection drops briefly
            \Illuminate\Support\Facades\Log::warning('TrackVisitor middleware error: ' . $e->getMessage());
        }
    }
}
