<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanUserRequest;
use App\Http\Requests\UnbanUserRequest;
use App\Http\Requests\BanDeviceRequest;
use App\Models\BannedDevice;
use App\Models\User;
use App\Models\BanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    /**
     * Display a listing of banned users.
     */
    public function index(Request $request)
    {
        $query = User::where('is_active', 'banned');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phones', 'like', "%{$search}%");
            });
        }

        $bannedUsers = $query->paginate(15);

        return view('admin.users.banned', compact('bannedUsers'));
    }

    /**
     * Show form to ban a user.
     */
    public function banForm($userId)
    {
        $user = User::findOrFail($userId);
        return view('admin.users.ban_form', compact('user'));
    }

    public function toggleBan(Request $request, $id)
    {
        try {
            // Find the user
            $user = User::findOrFail($id);
            $action = $request->input('action');

            // Begin transaction
            DB::beginTransaction();

            if ($action === 'ban') {
                // Ban user immediately
                $user->is_active = 'banned';
                $user->save();

                // Create a ban record using BanHistory if available
                if (class_exists(BanHistory::class)) {
                    BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'ban',
                        'performed_by' => auth()->id(),
                        'reason' => 'Banned via admin panel',
                    ]);
                }

                $message = "User has been banned successfully";
            } else {
                // Unban user immediately
                $user->is_active = 'active';
                $user->save();

                // Create an unban record using BanHistory if available
                if (class_exists(BanHistory::class)) {
                    BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'unban',
                        'performed_by' => auth()->id(),
                        'reason' => 'Unbanned via admin panel',
                    ]);
                }

                $message = "User has been unbanned successfully";
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $user->is_active
            ]);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Ban a user and their device.
     */
    public function banUser(BanUserRequest $request, $userId)
    {
        $user = User::findOrFail($userId);

        DB::beginTransaction();
        try {
            $user->ban(
                $request->reason,
                $request->device_id,
                [
                    'device_name' => $request->device_name,
                    'device_brand' => $request->device_brand,
                    'device_model' => $request->device_model,
                    'os_version' => $request->os_version,
                    'ip_address' => $request->ip(),
                ],
                auth()->id()
            );

            DB::commit();

            return redirect()->route('admin.users.banned')
                ->with('success', 'User has been banned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user.
     */
    public function unbanUser(UnbanUserRequest $request, $userId)
    {
        $user = User::findOrFail($userId);

        DB::beginTransaction();
        try {
            $user->unban($request->reason, auth()->id());
            DB::commit();

            return redirect()->route('admin.users.banned')
                ->with('success', 'User has been unbanned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of banned devices.
     */
    /**
     * Display a listing of banned devices.
     */
    public function devices(Request $request)
    {
        $query = BannedDevice::with('user');

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Count active devices (currently banned)
        $activeDevicesCount = BannedDevice::whereNull('unban_at')
            ->orWhere('unban_at', '>', now())
            ->count();

        // Count unbanned devices
        $unbannedDevicesCount = BannedDevice::whereNotNull('unban_at')
            ->where('unban_at', '<=', now())
            ->count();

        // Filter by active bans only
        if ($request->has('active_only') && $request->active_only) {
            $query->where(function($q) {
                $q->whereNull('unban_at')
                    ->orWhere('unban_at', '>', now());
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $bannedDevices = $query->paginate(15);

        return view('admin.devices.banned', compact('bannedDevices', 'activeDevicesCount', 'unbannedDevicesCount'));
    }
    /**
     * Show form to ban a device.
     */
    public function banDeviceForm()
    {
        return view('admin.devices.ban_form');
    }

    /**
     * Ban a specific device.
     */
    public function banDevice(BanDeviceRequest $request)
    {
        DB::beginTransaction();
        try {
            // Check if device is already banned
            $existingBan = BannedDevice::where('device_id', $request->device_id)
                ->whereNull('unban_at')
                ->first();

            if ($existingBan) {
                return back()->with('warning', 'This device is already banned');
            }

            // Ban user if user_id is provided
            if ($request->user_id) {
                $user = User::find($request->user_id);
                if ($user) {
                    $user->is_active = 'banned';
                    $user->save();

                    // Record in history
                    if (class_exists(BanHistory::class)) {
                        BanHistory::create([
                            'user_id' => $user->id,
                            'banned_device_id' => null,
                            'action' => 'ban',
                            'performed_by' => auth()->id(),
                            'reason' => $request->reason,
                        ]);
                    }
                }
            }

            // Create banned device record
            $bannedDevice = BannedDevice::create([
                'user_id' => $request->user_id,
                'device_id' => $request->device_id,
                'device_name' => $request->device_name,
                'device_brand' => $request->device_brand,
                'device_model' => $request->device_model,
                'os_version' => $request->os_version,
                'ip_address' => $request->ip(),
                'email' => $request->email,
                'phone' => $request->phone,
                'ban_reason' => $request->reason,
                'banned_at' => now(),
            ]);

            // Record in history
            if (class_exists(BanHistory::class)) {
                BanHistory::create([
                    'user_id' => $request->user_id,
                    'banned_device_id' => $bannedDevice->id,
                    'action' => 'ban',
                    'performed_by' => auth()->id(),
                    'reason' => $request->reason,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.devices.banned')
                ->with('success', 'Device has been banned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to ban device: ' . $e->getMessage());
        }
    }



    /**
     * Unban a specific device.
     */
    public function unbanDevice(Request $request, $deviceId)
    {
        $bannedDevice = BannedDevice::findOrFail($deviceId);

        DB::beginTransaction();
        try {
            $bannedDevice->unban($request->reason, auth()->id());

            DB::commit();

            return redirect()->route('admin.devices.banned')
                ->with('success', 'Device has been unbanned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to unban device: ' . $e->getMessage());
        }
    }

    /**
     * View ban history.
     */
    public function history(Request $request)
    {
        $query = BanHistory::with(['user', 'bannedDevice', 'performer']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.bans.history', compact('history'));
    }
}
