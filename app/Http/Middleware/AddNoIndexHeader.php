<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds X-Robots-Tag: noindex, nofollow to the response. Use on routes that
 * must not be indexed by Google (login, register, password reset, etc.)
 * regardless of the underlying view template.
 */
class AddNoIndexHeader
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request)->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
