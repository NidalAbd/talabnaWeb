<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BanController extends Controller
{
    /**
     * Toggle user ban status (ban or unban)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleBan(Request $request, $id)
    {
        try {
            // Log the incoming request
            Log::info('Mobile API - Toggle Ban Request', [
                'user_id' => $id,
                'action' => $request->input('action'),
                'requester_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            // Find the user
            $user = User::findOrFail($id);
            $action = $request->input('action', 'ban'); // Default to ban if not specified

            // Begin transaction
            DB::beginTransaction();

            if ($action === 'ban') {
                // Ban user immediately
                $user->is_active = 'banned';
                $user->save();

                // Create a ban record using BanHistory
                if (class_exists(\App\Models\BanHistory::class)) {
                    \App\Models\BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'ban',
                        'performed_by' => auth()->id(),
                        'reason' => 'Banned via mobile app',
                    ]);
                }

                $message = "User has been banned successfully";
            } else {
                // Unban user immediately
                $user->is_active = 'active';
                $user->save();

                // Create an unban record using BanHistory
                if (class_exists(\App\Models\BanHistory::class)) {
                    \App\Models\BanHistory::create([
                        'user_id' => $user->id,
                        'banned_device_id' => null,
                        'action' => 'unban',
                        'performed_by' => auth()->id(),
                        'reason' => 'Unbanned via mobile app',
                    ]);
                }

                $message = "User has been unbanned successfully";
            }

            // Commit transaction
            DB::commit();

            // Log success
            Log::info('Mobile API - User ban status changed', [
                'user_id' => $id,
                'action' => $action,
                'new_status' => $user->is_active,
                'requester_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $user->is_active
            ]);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log error
            Log::error('Mobile API - Ban toggle failed', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user ban status
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBanStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'success' => true,
                'is_banned' => $user->is_active === 'banned',
                'status' => $user->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Mobile API - Error getting ban status', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
