<?php

namespace App\Http\Controllers;

use App\Models\point_transactions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class PointTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasPermission('point_transactions.index')) {
            return view('errors.403');
        }

        $pointTransactions = point_transactions::with('user')->latest()->paginate(7);
        return view('point_transactions.index', compact('pointTransactions'));
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param point_transactions $point_transactions
     * @return Application|Factory|View
     */
    public function show(point_transactions $point_transactions)
    {
        $user = Auth::user();
        if (!$user->hasPermission('point_transactions.show')) {
            return view('errors.403');
        }

        return view('point_transactions.show', compact('point_transactions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param point_transactions $point_transactions
     * @return \Illuminate\Http\Response
     */
    public function edit(point_transactions $point_transactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param point_transactions $point_transactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, point_transactions $point_transactions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param point_transactions $point_transactions
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(point_transactions $point_transactions)
    {
        try {
            $purchaseRequest = point_transactions::findOrFail($point_transactions);
            // Delete the point transaction
            $purchaseRequest->delete();
            // Log the success message
            Log::info('Point transaction deleted successfully.');
            // Redirect back with success message
            return redirect()->back()->with('success', 'Point transaction deleted successfully.');

        } catch (Throwable $th) {
            // Log the error message
            Log::error('Error deleting Point transaction: ' . $th->getMessage());
            // Redirect back with error message
            return redirect()->back()->with('error', 'Error deleting Point transaction.');
        }
    }
}
