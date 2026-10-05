<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class NotificationController extends Controller
{
    public function index($user): \Illuminate\Http\JsonResponse
    {
        // Only your own notifications, whatever id is in the URL.
        $notifications = Notification::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);

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
        $user = Auth::user()->id; // Assuming you are using Laravel's built-in authentication
        $notifications = Notification::where('user_id', $user)->where('read', 0)->get();

       foreach ($notifications as  $notification){
           $notification->read = 1;
           $notification->save();
       }
        return response()->json(['message' => 'All notifications marked as read.'], 200);
    }

}
