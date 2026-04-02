<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveSiteRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('redirects') || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->where(function ($q) use ($path): void {
                $q->where('from_path', $path)->orWhere('from_path', ltrim($path, '/'));
            })
            ->orderByDesc('id')
            ->first();

        if (! $redirect) {
            return $next($request);
        }

        $to = $redirect->to_path;
        $code = (int) $redirect->http_code ?: 302;
        if (preg_match('#^https?://#i', $to)) {
            return redirect()->away($to, $code);
        }

        $to = str_starts_with($to, '/') ? $to : '/'.$to;

        return redirect()->to($to, $code);
    }
}
