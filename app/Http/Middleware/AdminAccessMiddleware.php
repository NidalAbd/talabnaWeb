<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasRole(['admin'])) {
            return $next($request);
        }

        // For API/JSON requests, return JSON response instead of redirect
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Admin access required'
            ], 403);
        }

        // Redirect non-admin users back to welcome page
        return redirect('/');
    }
}
