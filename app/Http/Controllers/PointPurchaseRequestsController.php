<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\point_purchase_requests;
use App\Models\point_transactions;
use App\Models\User;
use App\Notifications\point_purchase_notifications;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PointPurchaseRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $purchaseRequests = point_purchase_requests::with('user')->paginate(7);
        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $purchaseRequests = point_purchase_requests::with('user')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('user_name', 'like', "%{$search}%");
            })
            ->paginate(10);

        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    public function create()
    {
        $userId = auth()->id();
        return view('purchase_requests.create', compact('userId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'points_requested' => 'required|integer',
        ]);

        $price_per_point = 7.5; // Set the price per point
        $total_price = $price_per_point * $request->points_requested;

        point_purchase_requests::create([
            'user_id' => auth()->id(),
            'points_requested' => $request->points_requested,
            'price_per_point' => $price_per_point,
            'total_price' => $total_price,
        ]);

        return redirect()->back()->with('success', 'Your point purchase request has been submitted.');
    }

    /**
     * Display the specified resource.
     *
     * @param point_purchase_requests $point_purchase_requests
     * @return void
     */
    public function show(point_purchase_requests $point_purchase_requests)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param point_purchase_requests $point_purchase_requests
     * @return Application|Factory|View
     */
    public function edit(point_purchase_requests $point_purchase_requests): View|Factory|Application
    {
        $purchaseRequest = point_purchase_requests::findOrFail($point_purchase_requests);

        return view('purchase_requests.edit', compact('purchaseRequest'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param point_purchase_requests $point_purchase_requests
     * @return RedirectResponse
     */
    public function update(Request $request, point_purchase_requests $point_purchase_requests)
    {
        $purchaseRequest = point_purchase_requests::findOrFail($point_purchase_requests);

        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:0',
            'status' => 'required|in:pending,approved,cancelled'
        ]);

        $purchaseRequest->user_id = $request->user_id;
        $purchaseRequest->points = $request->points;
        $purchaseRequest->status = $request->status;
        $purchaseRequest->save();

        return redirect()->route('purchase_requests.index')->with('success', 'Purchase request updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param point_purchase_requests $point_purchase_requests
     * @return RedirectResponse
     */
    public function destroy(point_purchase_requests $point_purchase_requests)
    {
        $purchaseRequest = point_purchase_requests::findOrFail($point_purchase_requests);
        $purchaseRequest->delete();

        return redirect()->route('purchase_requests.index')->with('success', 'Purchase request deleted successfully!');

    }


    public function approved(Request $request, point_purchase_requests $purchaseRequest)
    {
        DB::beginTransaction(); // Start the transaction
    
        try {
            $userRequest = User::findOrFail($request->user_id);
    
            // Check if device token is registered
            if (empty($userRequest->fcm_token)) {
                return redirect()->back()->with('error', 'User device token is not registered.');
            }
    
            $device_token = $userRequest->fcm_token;
            $purchaseRequest = point_purchase_requests::findOrFail($purchaseRequest->id);
            $purchaseRequest->status = 'approved';
            $purchaseRequest->user_id = $request->user_id;
    
            // Ensure points_requested is within an acceptable range
            if ($purchaseRequest->points_requested > PHP_INT_MAX || $purchaseRequest->points_requested < -PHP_INT_MAX) {
                throw new \Exception('Invalid points requested value.');
            }
    
            $purchaseRequest->save();
    
            $adminUser = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->firstOrFail();
    
            // Add or deduct points from user's balance
            $user = $purchaseRequest->user;
    
            // Check if the user already has a record in the palservice_points table
            $points = palservice_points::where('user_id', $user->id)->first();
    
            if ($points) {
                // Check if points_requested is negative and if user has enough balance to deduct points
                if ($purchaseRequest->points_requested < 0 && $points->point < abs($purchaseRequest->points_requested)) {
                    throw new \Exception('Insufficient balance to approve the purchase request.');
                }
    
                // Update points balance
                $points->point += $purchaseRequest->points_requested;
                $points->save();
            } else {
                // User does not have a record, create a new one
                if ($purchaseRequest->points_requested < 0) {
                    throw new \Exception('Insufficient balance to approve the purchase request.');
                }
    
                $points = new palservice_points();
                $points->user_id = $user->id;
                $points->point = $purchaseRequest->points_requested;
                $points->save();
            }
    
            // Create a transaction record
            $transaction = new point_transactions();
            $transaction->from_user_id = $adminUser->id;
            $transaction->to_user_id = $user->id;
            $transaction->point = abs($purchaseRequest->points_requested); // Ensure positive value for unsigned column
            $transaction->type = $purchaseRequest->points_requested > 0 ? 'Purchase' : 'Deduction';
            $transaction->save();
    
            // Create and save the notification
            $message = "You have successfully " . ($purchaseRequest->points_requested > 0 ? 'purchased' : 'deducted') . " $purchaseRequest->points_requested points.";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
            ]);
            $user->notifications()->save($notification);
    
            // Send point purchase notification
            $subject = 'Your point purchase confirmation';
            $notification = new point_purchase_notifications($purchaseRequest->points_requested, $subject, $device_token);
            $user->notify($notification);
    
            DB::commit(); // Commit the transaction
    
            return redirect()->back()->with('success', ($purchaseRequest->points_requested > 0 ? 'Purchased' : 'Deducted') . ' request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction
            Log::error('Error approving purchase request: ' . $e->getMessage());
    
            // Check for specific error conditions
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return redirect()->back()->with('error', 'User or purchase request not found.');
            } elseif ($e->getMessage() == 'User device token is not registered.') {
                return redirect()->back()->with('error', 'User device token is not registered.');
            } elseif ($e->getMessage() == 'Insufficient balance to approve the purchase request.') {
                return redirect()->back()->with('error', 'Insufficient balance to approve the purchase request.');
            } elseif ($e->getMessage() == 'Invalid points requested value.') {
                return redirect()->back()->with('error', 'Invalid points requested value.');
            }
    
            return redirect()->back()->with('error', 'An error occurred while approving the purchase request.');
        }
    }







    public function cancel(Request $request, point_purchase_requests $purchaseRequest)
    {
        $purchaseRequest->status = 'cancelled';
        $purchaseRequest->save();

        return redirect()->back()->with('success', 'Purchase request cancelled successfully.');
    }
}
