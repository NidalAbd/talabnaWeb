<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\MarketingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationMarketingController extends Controller
{
    /**
     * Show the notification form
     */
    public function index()
    {
        // Get the total count of users with FCM tokens
        $fcmUsersCount = User::whereNotNull('fcm_token')->count();

        // Check if there's a notification result in the session
        $notificationResult = session('notification_result') ? session('notification_result') : null;

        return view('admin.notifications.marketing.send', compact('fcmUsersCount', 'notificationResult'));
    }

    /**
     * Send notification to all users with FCM tokens
     */
    public function sendToAll(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'image_url' => 'nullable|url',
            'deep_link' => 'nullable|string|max:255',
        ]);

        // Get all users with FCM tokens
        $users = User::whereNotNull('fcm_token')
            ->where('is_active', 'active')
            ->get();

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // Start database transaction
        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                try {
                    $user->notify(new MarketingNotification(
                        $validated['title'],
                        $validated['body'],
                        $validated['image_url'] ?? null,
                        $validated['deep_link'] ?? null,
                        $user->fcm_token
                    ));

                    // Log successful notification
                    $successCount++;
                } catch (\Exception $e) {
                    // Log error
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

            // Prepare result data
            $resultData = [
                'success' => true,
                'title' => $validated['title'],
                'total' => count($users),
                'successful' => $successCount,
                'failed' => $failedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            // Prepare failure result
            $resultData = [
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
                'total' => count($users),
                'successful' => $successCount,
                'failed' => $failedCount,
                'errors' => $errors
            ];

            Log::error('Notification Campaign Error: ' . $e->getMessage());
        }

        // Store result in session and redirect back
        return redirect()->route('admin.notifications.marketing.index')
            ->with('notification_result', $resultData);
    }

    /**
     * Send notification to specific users
     */
    public function sendToSpecific(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'image_url' => 'nullable|url',
            'deep_link' => 'nullable|string|max:255',
        ]);

        // Get specific users with FCM tokens
        $users = User::whereIn('id', $validated['user_ids'])
            ->whereNotNull('fcm_token')
            ->where('is_active', 'active')
            ->get();

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        // Start database transaction
        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                try {
                    $user->notify(new MarketingNotification(
                        $validated['title'],
                        $validated['body'],
                        $validated['image_url'] ?? null,
                        $validated['deep_link'] ?? null,
                        $user->fcm_token
                    ));

                    // Log successful notification
                    $successCount++;
                } catch (\Exception $e) {
                    // Log error
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

            // Prepare result data
            $resultData = [
                'success' => true,
                'title' => $validated['title'],
                'total' => count($users),
                'successful' => $successCount,
                'failed' => $failedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            // Prepare failure result
            $resultData = [
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
                'total' => count($users),
                'successful' => $successCount,
                'failed' => $failedCount,
                'errors' => $errors
            ];

            Log::error('Notification Campaign Error: ' . $e->getMessage());
        }

        // Store result in session and redirect back
        return redirect()->route('admin.notifications.marketing.index')
            ->with('notification_result', $resultData);
    }

    /**
     * Show notification history
     */
    public function history()
    {
        $notificationLogs = DB::table('notification_logs')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.notifications.marketing.history', compact('notificationLogs'));
    }
}
