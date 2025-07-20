<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\User;
use App\Notifications\point_purchase_notifications;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PalservicePointsController extends Controller
{
    /**
     * Display a listisssng of the resource.
     *
     * @return Application|Factory|View
     */
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('purchase_index')) {
            return view('errors.403');
        }

        $query = palservice_points::query()->with(['user', 'user.roles', 'user.photos']);

        // Search by name, username, or ID
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('user_name', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Filter by minimum points
        if ($request->filled('min_points')) {
            $minPoints = $request->input('min_points');
            $query->where('point', '>=', $minPoints);
        }

        // Filter by maximum points
        if ($request->filled('max_points')) {
            $maxPoints = $request->input('max_points');
            $query->where('point', '<=', $maxPoints);
        }

        $palservicePoints = $query->paginate(7);

        return view('palservice_points.index', compact('palservicePoints'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create($user_id)
    {
        $user = User::findOrFail($user_id);
        return view('palservice_points.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            $user = Auth::user();
            if ($request->type === 'purchase' && !$user->hasPermission('purchase_index')) {
                return view('errors.403');
            } elseif ($request->type === 'transfer' && !$user->hasPermission('point_transfer')) {
                return view('errors.403');
            } elseif ($request->type === 'admin_grant' && !$user->hasPermission('grant_points')) {
                return view('errors.403');
            } elseif ($request->type === 'admin_deduct' && !$user->hasPermission('grant_points')) {
                return view('errors.403');
            }

            $this->validate($request, [
                'type' => 'required|in:purchase,transfer,admin_grant,admin_deduct,used',
                'points' => 'required|integer|min:1' // Changed from min:0 to min:1 - can't transfer 0 points
            ]);

            $from_user_id = null;
            if ($request->type === 'purchase' || $request->type === 'transfer' || $request->type === 'admin_grant' || $request->type === 'admin_deduct') {
                $this->validate($request, [
                    'to_user_id' => 'required|exists:users,id',
                    'from_user_id' => 'required|exists:users,id',
                ]);
                $from_user_id = $request->from_user_id;
            }

            // If admin is deducting points from a user
            if ($request->type === 'admin_deduct' && $user->hasPermission('grant_points')) {
                $userPoints = palservice_points::where('user_id', $request->to_user_id)->first();

                // Special case: If user doesn't have a points record at all
                if (!$userPoints) {
                    return redirect()->back()->with('error', 'User has no points to deduct.');
                }

                // Check if user has enough points to deduct
                if ($userPoints->point < $request->points) {
                    return redirect()->back()->with('error', 'User has insufficient points to deduct! Current balance: ' . $userPoints->point);
                }

                // Deduct points from the user
                $userPoints->point -= $request->points;
                $userPoints->save();

                // Create a transaction record for the deduction
                $transaction = new point_transactions();
                $transaction->to_user_id = $from_user_id; // Points go to admin (or system)
                $transaction->from_user_id = $request->to_user_id; // Points come from user
                $transaction->type = 'used';
                $transaction->point = $request->points;
                $transaction->save();

                // Notification for user about deduction
                $message = json_encode([
                    'en' => "Admin has deducted $request->points points from your account.",
                    'ar' => "قام المسؤول بخصم {$request->points} نقطة من حسابك."
                ]);

                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $request->to_user_id,
                    'type'    => 'pointOut'
                ]);
                $notification->save();

                DB::commit();
                return redirect()->back()->with('success', 'Points deducted successfully! New balance: ' . $userPoints->point);
            }

            // Regular transfer - check balance for non-admins
            if ($from_user_id && $user->id !== $from_user_id &&
                $request->type === 'transfer' && !$user->hasPermission('grant_points')) {
                $currentUserPoints = palservice_points::where('user_id', $user->id)->value('point');

                // Check if user has any points record
                if (!$currentUserPoints) {
                    return redirect()->back()->with('error', 'You have no points to transfer.');
                }

                if ($currentUserPoints < $request->points) {
                    return redirect()->back()->with('error', 'Insufficient points to transfer! Your balance: ' . $currentUserPoints);
                }

                palservice_points::where('user_id', $user->id)->decrement('point', $request->points);
            }

            // For admin_grant and transfers, add points to recipient
            if ($request->type === 'admin_grant' || $request->type === 'transfer' || $request->type === 'purchase') {
                $points = palservice_points::where('user_id', $request->to_user_id)->first();

                if ($points) {
                    $points->point += $request->points;
                    $points->save();
                } else {
                    // User does not have a record, create a new one
                    $points = new palservice_points();
                    $points->user_id = $request->to_user_id;
                    $points->point = $request->points;
                    $points->save();
                }

                // Create a transaction record
                $transaction = new point_transactions();
                $transaction->to_user_id = $request->to_user_id;
                $transaction->from_user_id = $from_user_id;
                $transaction->type = $request->type;
                $transaction->point = $request->points;
                $transaction->save();

                // Prepare notification message for point addition
                $message = json_encode([
                    'en' => "You have received $request->points points " .
                        ($from_user_id ? "from user ID $from_user_id" : "via $request->type") . ".",
                    'ar' => "لقد استلمت {$request->points} نقطة " .
                        ($from_user_id ? "من المستخدم رقم {$from_user_id}" : "عبر {$request->type}")
                ]);

                // Create and save the notification
                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $request->to_user_id,
                    'type'    => 'pointIn'
                ]);
                $notification->save();

                // Fetch the recipient user to send push notification
                $recipientUser = User::findOrFail($request->to_user_id);

                // Check if device token is registered
                if (!empty($recipientUser->fcm_token)) {
                    $device_token = $recipientUser->fcm_token;
                    $subject = 'Points Received';

                    // Create and send point purchase notification
                    $pointNotification = new point_purchase_notifications(
                        $request->points,
                        $subject,
                        $device_token
                    );
                    $recipientUser->notify($pointNotification);
                }
            }

            DB::commit(); // Commit the transaction

            // Show different success messages based on operation type
            if ($request->type === 'admin_grant') {
                return redirect()->back()->with('success', 'Points granted successfully!');
            } elseif ($request->type === 'transfer') {
                return redirect()->back()->with('success', 'Points transferred successfully!');
            } else {
                return redirect()->back()->with('success', 'Transaction created successfully!');
            }

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction
            Log::error('Error processing point transaction: ' . $e->getMessage());
            Log::error('Error Stack Trace: ' . $e->getTraceAsString());

            // Check for specific error conditions
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return redirect()->back()->with('error', 'User not found.');
            }

            return redirect()->back()->with('error', 'An error occurred while processing the transaction: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     *
     * @param palservice_points $palservice_points
     * @return Application|Factory|View
     */
    public function show(palservice_points $palservice_points)
    {
        $user = Auth::user();
        if (!$user->hasPermission('purchase_index')) {
            return view('errors.403');
        }

        return view('palservice_points.edit', compact('palservice_points'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param palservice_points $palservice_points
     * @return Application|Factory|View
     */
    public function edit(palservice_points $palservice_points)
    {
        $user = Auth::user();
        if (!$user->hasPermission('purchase_index')) {
            return view('errors.403');
        }

        return view('palservice_points.edit', compact('palservice_points'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param palservice_points $palservice_points
     * @return Application|Factory|View
     */
    public function update(Request $request, palservice_points $palservice_points)
    {
        $user = Auth::user();
        if (!$user->hasPermission('purchase_index')) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        try {
            $this->validate($request, [
                'name' => 'required|unique:palservice_points,name,' . $palservice_points->id,
                'description' => 'required',
                'point_count' => 'required|integer|min:0'
            ]);

            $palservice_points->name = $request->name;
            $palservice_points->description = $request->description;
            $palservice_points->point_count = $request->point_count;
            $palservice_points->save();

            return response()->json(['success' => true, 'message' => 'Palservice point updated successfully!']);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error updating palservice point: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the palservice point.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param palservice_points $palservice_points
     * @return RedirectResponse
     */
    public function destroy(palservice_points $palservice_points)
    {
        try {
            // Delete the pal service point
            $palservice_points->delete();

            // Log the success message
            Log::info('Pal Service Point deleted successfully.');

            return response()->json(['success' => true, 'message' => 'Pal Service Point deleted successfully.']);

        } catch (Throwable $th) {
            // Log the error message
            Log::error('Error deleting Pal Service Point: ' . $th->getMessage());

            return response()->json(['success' => false, 'message' => 'Error deleting Pal Service Point.'], 500);
        }
    }
}
