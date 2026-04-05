<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $mode = $settingsService->get('store.mode', 'live');

        // Admin, API, and webhook routes should always bypass store mode
        if ($request->is('admin*') || $request->is('api*') || $request->is('webhook*')) {
            return $next($request);
        }

        // If store is live, just proceed
        if ($mode === 'live') {
            return $next($request);
        }

        // If user is authenticated and is an admin, they can preview the store
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        // Handle Coming Soon Mode
        if ($mode === 'coming_soon' && !$request->routeIs('coming_soon')) {
            return redirect()->route('coming_soon');
        }

        // Handle Maintenance Mode
        if ($mode === 'maintenance' && !$request->routeIs('maintenance')) {
            return redirect()->route('maintenance');
        }

        return $next($request);
    }
}
