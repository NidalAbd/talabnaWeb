<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\User;
use App\Notifications\point_recived_notifications;
use App\Notifications\point_send_notifications;
use App\Notifications\point_transactions_notifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PalservicePointsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store($pointsRequested ,$fromUser, $toUser)
    {

        $FromUser = Auth::user();
        $ToUser = User::findOrFail($toUser);
        Log::info('FromUser' , [$FromUser->id]);
        Log::info('fromUser' , [$fromUser]);
        Log::info('ToUser' , [$ToUser->id]);
        Log::info('ToUser' , [$toUser]);
        Log::info('pointsRequested' , [$pointsRequested]);
        // Deduct points from the current user if it's a transfer
        if ($fromUser && $FromUser->id !== $fromUser) {
            $currentUserPoints = palservice_points::where('user_id', $FromUser->id)->value('point');
            Log::info('currentUserPoints' , [$currentUserPoints]);
            if ($currentUserPoints < $pointsRequested) {
                return response()->json(['error' => 'Insufficient points to transfer!'], 422);
            }
            palservice_points::where('user_id', $FromUser->id)->decrement('point', $pointsRequested);
            $message = "You have successfully transferred $pointsRequested points to $toUser.";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'pointOut'
            ]);
            $FromUser->notifications()->save($notification);
//            $EmailNotifications = new point_send_notifications($FromUser, $ToUser, $pointsRequested, 'Points Send');
////            $FromUser->notify($EmailNotifications);
        }else{
            return response()->json(['error' => 'you can\'t transfer  points to yourself!'], 425);
        }

        // Check if the user already has a record in the palservice_points table
        $points = palservice_points::where('user_id', $toUser)->first();

        if ($points) {
            // User already has a record, add purchased points to existing balance
            $points->point += $pointsRequested;
            $points->save();
        } else {
            // User does not have a record, create a new one
            $points = new palservice_points();
            $points->user_id = $toUser;
            $points->point = $pointsRequested;
            $points->save();
        }
        // Create a transaction record
        $transaction = new point_transactions();
        $transaction->to_user_id = $toUser;
        $transaction->from_user_id = $fromUser;
        $transaction->type = 'transfer';
        $transaction->point = $pointsRequested;
        $transaction->save();
        $message = "You have received $pointsRequested points from $fromUser.";
        $notification = new Notification([
            'message' => $message,
            'user_id' => $toUser,
            'type'    => 'pointIn'
        ]);

        $notification->save();
//        $EmailNotifications = new point_recived_notifications($FromUser, $ToUser, $pointsRequested, 'Points Received');
//        $ToUser->notify($EmailNotifications);
        return response()->json(['success' => 'Transaction created successfully!'], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
