<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MarketingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingNotificationsApiController extends Controller
{
    /**
     * Get notification logs with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = DB::table('notification_logs')
                ->leftJoin('users', 'notification_logs.admin_id', '=', 'users.id')
                ->select(
                    'notification_logs.*',
                    'users.user_name as admin_name'
                );

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('notification_logs.title', 'like', "%{$search}%")
                        ->orWhere('notification_logs.body', 'like', "%{$search}%")
                        ->orWhere('users.user_name', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $allowedSortFields = ['id', 'created_at', 'total_recipients', 'successful_count', 'failed_count'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy('notification_logs.' . $sortBy, $sortDirection);
            }

            $logs = $query->paginate($perPage);

            return response()->json([
                'logs' => [
                    'data' => $logs->items(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Marketing Notifications API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load notification logs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Campaigns',
                    'value' => DB::table('notification_logs')->count(),
                    'icon' => 'fas fa-bullhorn',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Active Users with FCM',
                    'value' => User::whereNotNull('fcm_token')
                        ->where('is_active', 'active')
                        ->count(),
                    'icon' => 'fas fa-users',
                    'color' => 'success'
                ],
                [
                    'label' => 'Total Sent',
                    'value' => DB::table('notification_logs')->sum('successful_count'),
                    'icon' => 'fas fa-paper-plane',
                    'color' => 'info'
                ],
                [
                    'label' => 'Failed Deliveries',
                    'value' => DB::table('notification_logs')->sum('failed_count'),
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'danger'
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

    /**
     * Send notification to all users
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'image_url' => 'nullable|url',
            'deep_link' => 'nullable|string|max:255',
        ]);

        try {
            // Get all users with FCM tokens
            $users = User::whereNotNull('fcm_token')
                ->where('is_active', 'active')
                ->get();

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($users as $user) {
                try {
                    $user->notify(new MarketingNotification(
                        $validated['title'],
                        $validated['body'],
                        $validated['image_url'] ?? null,
                        $validated['deep_link'] ?? null,
                        $user->fcm_token
                    ));

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ];

                    Log::error('FCM Notification Error: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'fcm_token' => $user->fcm_token
                    ]);
                }
            }

            // Log notification campaign
            DB::table('notification_logs')->insert([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'image_url' => $validated['image_url'] ?? null,
                'deep_link' => $validated['deep_link'] ?? null,
                'total_recipients' => count($users),
                'successful_count' => $successCount,
                'failed_count' => $failedCount,
                'admin_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Notification sent successfully',
                'result' => [
                    'total' => count($users),
                    'successful' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending marketing notification: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to send notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test notification to a specific user
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'image_url' => 'nullable|url',
            'deep_link' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::find($validated['user_id']);

            if (!$user->fcm_token) {
                return response()->json([
                    'error' => 'User does not have a valid FCM token'
                ], 422);
            }

            $user->notify(new MarketingNotification(
                $validated['title'],
                $validated['body'],
                $validated['image_url'] ?? null,
                $validated['deep_link'] ?? null,
                $user->fcm_token
            ));

            return response()->json([
                'message' => 'Test notification sent successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->user_name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending test notification: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to send test notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification log
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::table('notification_logs')->where('id', $id)->delete();

            return response()->json([
                'message' => 'Notification log deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting notification log: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete notification log',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active users for testing
     */
    public function getActiveUsers(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search', '');

            $users = User::whereNotNull('fcm_token')
                ->where('is_active', 'active')
                ->where(function($q) use ($search) {
                    if ($search) {
                        $q->where('user_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }
                })
                ->select('id', 'user_name', 'email')
                ->limit(20)
                ->get();

            return response()->json(['users' => $users]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load users',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
