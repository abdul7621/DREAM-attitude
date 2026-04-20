<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Aapka account login hai par yeh Admin account nahi hai. Please Admin id se login karein.');
        }

        return $next($request);
    }
}
