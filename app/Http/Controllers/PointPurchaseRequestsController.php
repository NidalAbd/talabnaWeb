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
        $purchaseRequests = point_purchase_requests::with(['user' => function($query) {
            // Only load users that exist and are active
            $query->where('is_active', 'active');
        }])
            ->orderByRaw("CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'approved' THEN 2
                WHEN status = 'cancelled' THEN 3
                ELSE 4
            END")
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        return view('purchase_requests.index', compact('purchaseRequests'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $purchaseRequests = point_purchase_requests::with(['user' => function($query) {
            $query->where('is_active', 'active');
        }])
            ->whereHas('user', function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->where('is_active', 'active');
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
        $purchaseRequest = point_purchase_requests::with('user')->findOrFail($point_purchase_requests);

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
        try {
            $purchaseRequest = point_purchase_requests::findOrFail($point_purchase_requests);
            $purchaseRequest->delete();

            return response()->json(['success' => true, 'message' => 'Purchase request deleted successfully!']);
        } catch (\Exception $e) {
            Log::error("Error deleting purchase request: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete purchase request.'], 500);
        }
    }

    public function approved(Request $request, point_purchase_requests $purchaseRequest)
    {
        DB::beginTransaction();

        try {
            // First, check if the purchase request has a valid user
            if (!$purchaseRequest->user) {
                throw new \Exception('Purchase request has no associated user.');
            }

            $userRequest = User::findOrFail($request->user_id);

            // Check if device token is registered
            if (empty($userRequest->fcm_token)) {
                return response()->json(['success' => false, 'message' => 'User device token is not registered.'], 400);
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
            $message = json_encode([
                'en' => "You have successfully " . ($purchaseRequest->points_requested > 0 ? 'purchased' : 'deducted') . " $purchaseRequest->points_requested points.",
                'ar' => "لقد " . ($purchaseRequest->points_requested > 0 ? 'اشتريت' : 'خصمت') . " $purchaseRequest->points_requested نقطة بنجاح."
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'pointIn'
            ]);
            $user->notifications()->save($notification);

            // Send point purchase notification
            $subject = 'Your point purchase confirmation';
            $notification = new point_purchase_notifications($purchaseRequest->points_requested, $subject, $device_token);
            $user->notify($notification);

            DB::commit();

            return response()->json(['success' => true, 'message' => ($purchaseRequest->points_requested > 0 ? 'Purchased' : 'Deducted') . ' request approved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving purchase request: ' . $e->getMessage());

            // Check for specific error conditions
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['success' => false, 'message' => 'User or purchase request not found.'], 404);
            } elseif ($e->getMessage() == 'User device token is not registered.') {
                return response()->json(['success' => false, 'message' => 'User device token is not registered.'], 400);
            } elseif ($e->getMessage() == 'Insufficient balance to approve the purchase request.') {
                return response()->json(['success' => false, 'message' => 'Insufficient balance to approve the purchase request.'], 400);
            } elseif ($e->getMessage() == 'Invalid points requested value.') {
                return response()->json(['success' => false, 'message' => 'Invalid points requested value.'], 400);
            } elseif ($e->getMessage() == 'Purchase request has no associated user.') {
                return response()->json(['success' => false, 'message' => 'Purchase request has no associated user.'], 400);
            }

            return response()->json(['success' => false, 'message' => 'An error occurred while approving the purchase request.'], 500);
        }
    }

    public function cancel(Request $request, point_purchase_requests $purchaseRequest)
    {
        try {
            $purchaseRequest->status = 'cancelled';
            $purchaseRequest->save();

            return response()->json(['success' => true, 'message' => 'Purchase request cancelled successfully.']);
        } catch (\Exception $e) {
            Log::error("Error cancelling purchase request: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to cancel purchase request.'], 500);
        }
    }

    /**
     * Clean up orphaned purchase requests (requests with non-existent users)
     */
    public function cleanupOrphanedRequests()
    {
        $orphanedCount = point_purchase_requests::whereDoesntHave('user')->count();

        if ($orphanedCount > 0) {
            point_purchase_requests::whereDoesntHave('user')->delete();
            return redirect()->back()->with('success', "Cleaned up $orphanedCount orphaned purchase requests.");
        }

        return redirect()->back()->with('info', 'No orphaned purchase requests found.');
    }
}
