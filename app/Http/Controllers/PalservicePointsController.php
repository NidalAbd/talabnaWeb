<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PalservicePointsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasPermission('purchase_index')) {
            return view('errors.403');
        }

        $palservicePoints = palservice_points::paginate(7);
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
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        $user = Auth::user();
        if ($request->type === 'purchase' && !$user->hasPermission('purchase_index')) {
            return view('errors.403');
        } elseif ($request->type === 'transfer' && !$user->hasPermission('point_transfer')) {
            return view('errors.403');
        } elseif ($request->type === 'admin_grant' && !$user->hasPermission('grant_points')) {
            return view('errors.403');
        }

        $this->validate($request, [
            'type' => 'required|in:purchase,transfer,admin_grant,used',
            'points' => 'required|integer|min:0'
        ]);

        if ($request->type === 'purchase' || $request->type === 'transfer' || $request->type === 'admin_grant' ) {
            $this->validate($request, [
                'to_user_id' => 'required|exists:users,id',
                'from_user_id' => 'required|exists:users,id',
            ]);
            $from_user_id = $request->from_user_id;
        }

        // Deduct points from the current user if it's a transfer
        if ($from_user_id && $user->id !== $from_user_id) {
            $currentUserPoints = palservice_points::where('user_id', $user->id)->value('point');
            if ($currentUserPoints < $request->points) {
                return redirect()->back()->with('error', 'Insufficient points to transfer!');
            }
            palservice_points::where('user_id', $user->id)->decrement('point', $request->points);

        }

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
        // Add points to the recipient user


        // Create a transaction record
        $transaction = new point_transactions();
        $transaction->to_user_id = $request->to_user_id;
        $transaction->from_user_id = $from_user_id;
        $transaction->type = $request->type;
        $transaction->point = $request->points;
        $transaction->save();
        $message = "You have received $request->points points from $request->to_user_id.";
        $notification = new Notification([
            'message' => $message,
            'user_id' => $request->to_user_id,
            'type'    => 'pointIn'
        ]);

        $notification->save();
        return redirect()->back()->with('success', 'Transaction created successfully!');
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
            return view('errors.403');
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

            return redirect()->route('palservice_points.index')->with('success', 'Palservice point updated successfully!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the palservice point.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param palservice_points $palservice_points
     * @return RedirectResponse
     */
    public function destroy(palservice_points $palservice_points): RedirectResponse
    {
        try {
            // Delete the pal service point
            $palservice_points->delete();

            // Log the success message
            Log::info('Pal Service Point deleted successfully.');

            // Redirect back with success message
            return redirect()->back()->with('success', 'Pal Service Point deleted successfully.');

        } catch (Throwable $th) {
            // Log the error message
            Log::error('Error deleting Pal Service Point: ' . $th->getMessage());

            // Redirect back with error message
            return redirect()->back()->with('error', 'Error deleting Pal Service Point.');
        }
    }
}
