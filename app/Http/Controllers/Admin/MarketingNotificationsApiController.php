<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MarketingNotification;
use Carbon\Carbon;
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

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('notification_logs.title', 'like', "%{$search}%")
                        ->orWhere('notification_logs.body', 'like', "%{$search}%")
                        ->orWhere('users.user_name', 'like', "%{$search}%");
                });
            }

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
            Log::error('Marketing Notifications index() error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load notification logs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics with chart data
     */
    public function getStats(): JsonResponse
    {
        try {
            $now = Carbon::now();

            $totalCampaigns = DB::table('notification_logs')->count();
            $activeWithFcm  = User::whereNotNull('fcm_token')->where('is_active', 'active')->count();
            $totalSent      = (int) DB::table('notification_logs')->sum('successful_count');
            $totalFailed    = (int) DB::table('notification_logs')->sum('failed_count');
            $totalAttempted = $totalSent + $totalFailed;
            $deliveryRate   = $totalAttempted > 0 ? round(($totalSent / $totalAttempted) * 100, 1) : 0;

            // Week-over-week campaign comparison
            $thisWeekCampaigns = DB::table('notification_logs')
                ->whereBetween('created_at', [$now->copy()->subDays(7), $now])
                ->count();
            $lastWeekCampaigns = DB::table('notification_logs')
                ->whereBetween('created_at', [$now->copy()->subDays(14), $now->copy()->subDays(7)])
                ->count();

            $stats = [
                [
                    'label' => 'Total Campaigns',
                    'value' => $totalCampaigns,
                    'icon'  => 'fas fa-bullhorn',
                    'color' => 'primary',
                ],
                [
                    'label' => 'Active Users with FCM',
                    'value' => $activeWithFcm,
                    'icon'  => 'fas fa-users',
                    'color' => 'success',
                ],
                [
                    'label' => 'Total Delivered',
                    'value' => $totalSent,
                    'icon'  => 'fas fa-check-circle',
                    'color' => 'info',
                ],
                [
                    'label' => 'Failed Deliveries',
                    'value' => $totalFailed,
                    'icon'  => 'fas fa-exclamation-triangle',
                    'color' => 'danger',
                ],
            ];

            // Campaign volume chart (last 30 days)
            $dailyCampaigns = DB::table('notification_logs')
                ->whereBetween('created_at', [$now->copy()->subDays(30), $now])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(successful_count) as delivered'),
                    DB::raw('SUM(failed_count) as failed')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $campaignChart = [
                'labels'   => $dailyCampaigns->pluck('date')->toArray(),
                'datasets' => [
                    [
                        'label'           => 'Delivered',
                        'data'            => $dailyCampaigns->pluck('delivered')->toArray(),
                        'borderColor'     => '#22c55e',
                        'backgroundColor' => 'rgba(34,197,94,0.1)',
                        'fill'            => true,
                        'tension'         => 0.3,
                    ],
                    [
                        'label'           => 'Failed',
                        'data'            => $dailyCampaigns->pluck('failed')->toArray(),
                        'borderColor'     => '#ef4444',
                        'backgroundColor' => 'rgba(239,68,68,0.1)',
                        'fill'            => true,
                        'tension'         => 0.3,
                    ],
                ],
            ];

            return response()->json([
                'stats'          => $stats,
                'delivery_rate'  => $deliveryRate,
                'campaign_chart' => $campaignChart,
                'week_trend'     => [
                    'this_week' => $thisWeekCampaigns,
                    'last_week' => $lastWeekCampaigns,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Marketing Notifications getStats() error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

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
            // Get ALL active users for in-app notifications
            $allActiveUsers = User::where('is_active', 'active')->get();

            // Get users with FCM tokens for push notifications
            $usersWithFcm = $allActiveUsers->filter(fn($u) => !empty($u->fcm_token));

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            DB::beginTransaction();

            // 1. Create in-app database notifications for ALL active users
            $notificationMessage = json_encode([
                'en' => $validated['title'] . ': ' . $validated['body'],
                'ar' => $validated['title'] . ': ' . $validated['body'],
            ]);

            $now = now();
            $notificationRecords = $allActiveUsers->map(fn($user) => [
                'user_id' => $user->id,
                'message' => $notificationMessage,
                'type' => 'marketing',
                'read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray();

            // Batch insert in-app notifications (chunks of 500)
            foreach (array_chunk($notificationRecords, 500) as $chunk) {
                DB::table('notifications')->insert($chunk);
            }

            // 2. Send FCM push notifications to users with tokens
            foreach ($usersWithFcm as $user) {
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
                        'user_name' => $user->user_name,
                        'error' => $e->getMessage()
                    ];

                    Log::error('FCM send failed for user', [
                        'user_id'   => $user->id,
                        'user_name' => $user->user_name,
                        'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
                        'error'     => $e->getMessage(),
                        'campaign_title' => $validated['title'],
                    ]);
                }
            }

            // Log the campaign
            DB::table('notification_logs')->insert([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'image_url' => $validated['image_url'] ?? null,
                'deep_link' => $validated['deep_link'] ?? null,
                'total_recipients' => count($allActiveUsers),
                'successful_count' => $successCount,
                'failed_count' => $failedCount,
                'admin_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Log summary
            Log::info('Marketing campaign sent', [
                'title'      => $validated['title'],
                'admin_id'   => auth()->id(),
                'total_users' => count($allActiveUsers),
                'in_app_notifications' => count($allActiveUsers),
                'fcm_sent' => $successCount,
                'fcm_failed' => $failedCount,
            ]);

            if ($failedCount > 0) {
                Log::warning('Marketing campaign had FCM failures', [
                    'title'       => $validated['title'],
                    'failed_count' => $failedCount,
                    'errors'      => array_slice($errors, 0, 20),
                ]);
            }

            return response()->json([
                'message' => 'Notification sent successfully',
                'result' => [
                    'total' => count($allActiveUsers),
                    'in_app' => count($allActiveUsers),
                    'push_successful' => $successCount,
                    'push_failed' => $failedCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Marketing campaign sendToAll() critical error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'title'   => $validated['title'] ?? 'unknown',
            ]);

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
                Log::warning('Test notification failed: no FCM token', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                ]);

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

            Log::info('Test notification sent', [
                'user_id'   => $user->id,
                'user_name' => $user->user_name,
                'title'     => $validated['title'],
                'admin_id'  => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Test notification sent successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->user_name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Test notification sendTest() error', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'user_id'   => $validated['user_id'],
                'title'     => $validated['title'],
            ]);

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
            $log = DB::table('notification_logs')->where('id', $id)->first();

            if (!$log) {
                return response()->json([
                    'error' => 'Notification log not found'
                ], 404);
            }

            DB::table('notification_logs')->where('id', $id)->delete();

            Log::info('Notification log deleted', [
                'log_id'   => $id,
                'title'    => $log->title,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Notification log deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Notification log destroy() error', [
                'message' => $e->getMessage(),
                'log_id'  => $id,
            ]);

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
            Log::error('Marketing Notifications getActiveUsers() error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load users',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
