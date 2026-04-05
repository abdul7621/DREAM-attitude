<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isAdmin() || in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'You do not have the required permissions.');
    }
}
