<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            \App\Http\Middleware\ResolveSiteRedirect::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CaptureMarketingAttribution::class,
            \App\Http\Middleware\CheckStoreMode::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/shiprocket/webhook',
            'api/webhooks/ithink',
            'payments/verify/*',
            'payments/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch. Please refresh the page.'], 419);
            }
            return redirect()->back()->withErrors(['error' => 'Your session has expired. Please try again.'])->withInput($request->except('_token'));
        });
    })->create();
