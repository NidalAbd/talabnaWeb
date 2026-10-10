<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class NotificationController extends Controller
{
    /**
     * The app's tabs (2026-10-08). The app filtered only the pages it had loaded, so a tab with few matches (an account
     * with 518 notifications and a handful of comments) paged through everything and looked stuck. The server filters now.
     */
    public const CATEGORIES = [
        'messages' => ['comment', 'comment_reply', 'mention', 'like'],
        'activity' => ['post', 'badge', 'badge_applied', 'badge_upgraded', 'badge_switched', 'badge_expired', 'pointIn',
            'points_approved', 'pointOut', 'sub_category', 'user', 'follower'],
    ];

    /** GET users/{user}/notifications?page=&category=messages|activity|system&read=0|1 */
    public function index($user): \Illuminate\Http\JsonResponse
    {
        // Only your own notifications, whatever id is in the URL.
        $category = request()->query('category');
        $read = request()->query('read');
        $notifications = Notification::where('user_id', Auth::id())
            ->when(isset(self::CATEGORIES[$category]), fn ($q) => $q->whereIn('type', self::CATEGORIES[$category]))
            ->when($category === 'system', fn ($q) => $q->whereNotIn('type', array_merge(...array_values(self::CATEGORIES))))
            ->when($read === '0' || $read === '1', fn ($q) => $q->where('read', (int) $read))
            ->orderBy('created_at', 'desc')->orderByDesc('id')
            ->paginate(20);

        // Whether what each notification points at still exists, so the app can show
        // "No longer available" instead of opening a broken screen (2026-10-05).
        $targets = $notifications->getCollection()->map->target;
        $postIds = $targets->where('type', 'post')->pluck('id')->filter()->unique()->values();
        $userIds = $targets->where('type', 'user')->pluck('id')->filter()->unique()->values();
        $livePosts = $postIds->isEmpty() ? collect() : \App\Models\ServicePost::whereIn('id', $postIds)
            ->where('state', 'published')->pluck('id')->flip();
        $liveUsers = $userIds->isEmpty() ? collect() : \App\Models\User::whereIn('id', $userIds)
            ->where('is_active', 'active')->pluck('id')->flip();
        $notifications->getCollection()->each(function ($n) use ($livePosts, $liveUsers) {
            $t = $n->target;
            $n->setAttribute('target_available', match ($t['type'] ?? null) {
                'post' => $t['id'] ? $livePosts->has($t['id']) : false,
                'user' => $t['id'] ? $liveUsers->has($t['id']) : false,
                default => true,
            });
        });

        return response()->json($notifications);
    }

    public function countNotification($user): \Illuminate\Http\JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id())->where('read', 0)->orderBy('created_at', 'desc')->count();
        return response()->json($notifications);
    }

    public function markAsRead($user , $notification): \Illuminate\Http\JsonResponse
    {
        $notification = Notification::findOrFail($notification);
        if ((int) $notification->user_id !== (int) Auth::id()) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $notification->read = 1;
        $notification->save();
        return response()->json(['message' => 'Notification marked as read.' .$notification->read], 200);
    }
    public function markAllAsRead()
    {
        // One query (it saved them one by one: hundreds of queries for a busy account).
        Notification::where('user_id', Auth::id())->where('read', 0)->update(['read' => 1]);

        return response()->json(['message' => 'All notifications marked as read.'], 200);
    }

}
