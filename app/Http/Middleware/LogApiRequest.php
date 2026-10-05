<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        // "Last seen" for chat, written at most once a minute per user.
        try {
            $uid = Auth::id();
            if ($uid && \Illuminate\Support\Facades\Cache::add("seen:{$uid}", 1, 60)) {
                DB::table('users')->where('id', $uid)->update(['last_seen_at' => now()]);
            }
        } catch (\Throwable $e) {
            // never block a request over presence
        }

        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        try {
            DB::table('api_request_logs')->insert([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'method' => $request->method(),
                'endpoint' => substr($request->path(), 0, 500),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => $responseTimeMs,
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'device_id' => $request->header('X-Device-Id'),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Don't break the request if logging fails
            \Log::error('API request logging failed: ' . $e->getMessage());
        }

        return $response;
    }
}
