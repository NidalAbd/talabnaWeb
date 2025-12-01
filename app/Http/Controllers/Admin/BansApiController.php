<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedDevice;
use App\Models\User;
use App\Models\BanHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BansApiController extends Controller
{
    /**
     * Get paginated banned users
     */
    public function getBannedUsers(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = User::where('is_active', 'banned');

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('user_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phones', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $allowedSortFields = ['id', 'user_name', 'email', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $bannedUsers = $query->paginate($perPage);

            // Transform data
            $transformedUsers = $bannedUsers->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'phones' => $user->phones,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->toISOString(),
                ];
            });

            $bannedUsers->setCollection($transformedUsers);

            return response()->json([
                'banned_users' => [
                    'data' => $bannedUsers->items(),
                    'current_page' => $bannedUsers->currentPage(),
                    'last_page' => $bannedUsers->lastPage(),
                    'per_page' => $bannedUsers->perPage(),
                    'total' => $bannedUsers->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Banned Users API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load banned users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated banned devices
     */
    public function getBannedDevices(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $activeOnly = $request->input('active_only', false);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = BannedDevice::with('user');

            // Filter by active bans only
            if ($activeOnly) {
                $query->where(function($q) {
                    $q->whereNull('unban_at')
                        ->orWhere('unban_at', '>', now());
                });
            }

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('device_id', 'like', "%{$search}%")
                        ->orWhere('device_name', 'like', "%{$search}%")
                        ->orWhere('device_brand', 'like', "%{$search}%")
                        ->orWhere('device_model', 'like', "%{$search}%")
                        ->orWhereHas('user', function($userQuery) use ($search) {
                            $userQuery->where('user_name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply sorting
            $allowedSortFields = ['id', 'device_id', 'created_at', 'unban_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $bannedDevices = $query->paginate($perPage);

            // Transform data
            $transformedDevices = $bannedDevices->getCollection()->map(function ($device) {
                return [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'device_brand' => $device->device_brand,
                    'device_model' => $device->device_model,
                    'os_version' => $device->os_version,
                    'ip_address' => $device->ip_address,
                    'reason' => $device->reason,
                    'user' => $device->user ? [
                        'id' => $device->user->id,
                        'user_name' => $device->user->user_name,
                    ] : null,
                    'is_active' => !$device->unban_at || $device->unban_at > now(),
                    'created_at' => $device->created_at->toISOString(),
                    'unban_at' => $device->unban_at ? $device->unban_at->toISOString() : null,
                ];
            });

            $bannedDevices->setCollection($transformedDevices);

            return response()->json([
                'banned_devices' => [
                    'data' => $bannedDevices->items(),
                    'current_page' => $bannedDevices->currentPage(),
                    'last_page' => $bannedDevices->lastPage(),
                    'per_page' => $bannedDevices->perPage(),
                    'total' => $bannedDevices->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Banned Devices API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load banned devices',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user ban status
     */
    public function toggleUserBan(Request $request, $userId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:ban,unban',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $action = $validated['action'];

            if ($action === 'ban') {
                $user->is_active = 'banned';
                $user->save();

                // Create ban history
                if (class_exists(BanHistory::class)) {
                    BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'ban',
                        'performed_by' => auth()->id(),
                        'reason' => $validated['reason'] ?? 'Banned via admin panel',
                    ]);
                }

                $message = "User has been banned successfully";
            } else {
                $user->is_active = 'active';
                $user->save();

                // Create unban history
                if (class_exists(BanHistory::class)) {
                    BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'unban',
                        'performed_by' => auth()->id(),
                        'reason' => $validated['reason'] ?? 'Unbanned via admin panel',
                    ]);
                }

                $message = "User has been unbanned successfully";
            }

            DB::commit();

            return response()->json([
                'message' => $message,
                'status' => $user->is_active
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to toggle ban status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unban a device
     */
    public function unbanDevice(Request $request, $deviceId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $device = BannedDevice::findOrFail($deviceId);
            $device->unban_at = now();
            $device->save();

            // Create unban history
            if (class_exists(BanHistory::class)) {
                BanHistory::create([
                    'user_id' => $device->user_id,
                    'banned_device_id' => $device->id,
                    'action' => 'unban_device',
                    'performed_by' => auth()->id(),
                    'reason' => $validated['reason'] ?? 'Device unbanned via admin panel',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Device has been unbanned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to unban device',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ban statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Banned Users',
                    'value' => User::where('is_active', 'banned')->count(),
                    'icon' => 'fas fa-user-slash',
                    'color' => 'danger'
                ],
                [
                    'label' => 'Active Device Bans',
                    'value' => BannedDevice::where(function($q) {
                        $q->whereNull('unban_at')
                            ->orWhere('unban_at', '>', now());
                    })->count(),
                    'icon' => 'fas fa-mobile-alt',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Total Devices Banned',
                    'value' => BannedDevice::count(),
                    'icon' => 'fas fa-ban',
                    'color' => 'info'
                ],
                [
                    'label' => 'Bans Today',
                    'value' => User::where('is_active', 'banned')
                        ->whereDate('updated_at', today())
                        ->count(),
                    'icon' => 'fas fa-calendar-day',
                    'color' => 'primary'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
