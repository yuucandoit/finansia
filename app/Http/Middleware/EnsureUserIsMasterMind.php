<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMasterMind
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isMasterMind()) {
            abort(403, 'Akses ditolak. Halaman ini hanya dapat diakses oleh Master Mind (wahyusnjy).');
        }

        return $next($request);
    }
}
