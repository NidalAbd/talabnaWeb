<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Check if user has investor role or investor permissions
        if (!$user->hasRole('investor') && !$user->hasPermission('investor_view')) {
            // If user is admin, allow access
            if ($user->hasRole('admin') || $user->is_admin) {
                return $next($request);
            }

            // Redirect to dashboard with error message
            return redirect()->route('dashboard')->with('error', 'Access denied. Investor privileges required.');
        }

        return $next($request);
    }
} 