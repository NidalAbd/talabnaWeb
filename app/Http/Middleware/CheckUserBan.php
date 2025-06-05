<?php

namespace App\Http\Middleware;

use App\Models\BannedDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
// In your CheckUserBan middleware

    public function handle(Request $request, Closure $next): Response
    {
        // Skip for specific routes
        if ($request->routeIs('api.check-ban-status') || $request->routeIs('api.register-device')) {
            return $next($request);
        }

        // Get device ID from header
        $deviceId = $request->header('X-Device-ID');

        // Check if device is banned
        if ($deviceId) {
            $bannedDevice = BannedDevice::where('device_id', $deviceId)
                ->where(function($query) {
                    $query->whereNull('unban_at')
                        ->orWhere('unban_at', '>', now());
                })
                ->first();

            if ($bannedDevice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'banned' => true,
                    'reason' => $bannedDevice->ban_reason
                ], 403);
            }
        }

        // If user is authenticated, check if they're banned
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_active === 'banned') {
                // If this is a new device, also ban it
                if ($deviceId && !BannedDevice::where('device_id', $deviceId)->exists()) {
                    // Create new banned device record for this banned user
                    BannedDevice::create([
                        'user_id' => $user->id,
                        'device_id' => $deviceId,
                        'device_name' => $request->header('X-Device-Name'),
                        'device_brand' => $request->header('X-Device-Brand'),
                        'device_model' => $request->header('X-Device-Model'),
                        'os_version' => $request->header('X-OS-Version'),
                        'ip_address' => $request->ip(),
                        'ban_reason' => 'Device used by banned user',
                        'banned_at' => now(),
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                    'banned' => true,
                    'reason' => 'Your account has been suspended'
                ], 403);
            }
        }

        return $next($request);
    }
}
