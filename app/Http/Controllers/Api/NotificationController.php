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
        $notifications = Notification::where('user_id', $user)->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($notifications);
    }

    public function countNotification($user): \Illuminate\Http\JsonResponse
    {
        $notifications = Notification::where('user_id', $user)->where('read', 0)->orderBy('created_at', 'desc')->count();
        return response()->json($notifications);
    }

    public function markAsRead($user , $notification): \Illuminate\Http\JsonResponse
    {
        $notification = Notification::findOrFail($notification);
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
