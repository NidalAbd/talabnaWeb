<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannedDeviceResource;
use App\Models\BannedDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanCheckController extends Controller
{
    /**
     * Check if a device or user is banned.
     */
    public function checkBanStatus(Request $request)

    {

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

                    'banned' => true,

                    'reason' => $bannedDevice->ban_reason

                ], 403);

            }

        }



        // Check if authenticated user is banned

        if (Auth::check()) {

            $user = Auth::user();

            if ($user->is_active === 'banned') {

                // If this is a new device, also ban it automatically

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

                    'banned' => true,

                    'reason' => 'Your account has been suspended'

                ], 403);

            }

        }



        // User and device are not banned

        return response()->json([

            'success' => true,

            'banned' => false

        ], 200);

    }
    /**
     * Register device information
     */
    public function registerDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
            'device_brand' => 'nullable|string',
            'device_model' => 'nullable|string',
            'os_version' => 'nullable|string',
        ]);

        // Check if device is banned
        $bannedDevice = BannedDevice::where('device_id', $request->device_id)
            ->where(function($query) {
                $query->whereNull('unban_at')
                    ->orWhere('unban_at', '>', now());
            })
            ->first();

        if ($bannedDevice) {
            // Device is banned
            return response()->json([
                'banned' => true,
                'reason' => $bannedDevice->ban_reason
            ]);
        }

        // If user is authenticated, check if they're banned
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->is_active === 'banned') {
                // User is banned, so ban this device too
                BannedDevice::create([
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                    'device_name' => $request->device_name,
                    'device_brand' => $request->device_brand,
                    'device_model' => $request->device_model,
                    'os_version' => $request->os_version,
                    'ip_address' => $request->ip(),
                    'email' => $user->email,
                    'phone' => $user->phones,
                    'fcm_token' => $user->fcm_token,
                    'ban_reason' => "Associated with banned user account",
                    'banned_at' => now(),
                ]);

                return response()->json([
                    'banned' => true,
                    'reason' => "Your account has been suspended."
                ]);
            }

            // Not banned, update the user's FCM token if provided
            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }
        }

        return response()->json([
            'banned' => false,
            'device_registered' => true
        ]);
    }
}
