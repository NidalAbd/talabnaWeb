<?php

namespace App\Http\Controllers;

use App\Models\point_transactions;
use App\Models\ServicePost;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PointTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('point_transactions.index')) {
            return view('errors.403');
        }

        $query = point_transactions::with(['fromUser', 'toUser']);

        // Filter by transaction type if specified
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        $pointTransactions = $query->latest()->paginate(7);

        // Calculate totals for the dashboard and transaction metrics
        $totalPointsUsed = $this->calculateTotalPointsUsed();

        return view('point_transactions.index', compact('pointTransactions', 'totalPointsUsed'));
    }

    /**
     * Calculate the total points used from badge purchases and other "used" transactions
     * This provides an accurate count even if individual transactions show 0
     */
    private function calculateTotalPointsUsed()
    {
        // Get points used from service posts with badges
        $badgePointsUsed = ServicePost::where('have_badge', '!=', 'عادي')
            ->where('badge_duration', '>', 0)
            ->sum(DB::raw('CASE
                WHEN have_badge = "ذهبي" THEN badge_duration * 2
                WHEN have_badge = "ماسي" THEN badge_duration * 10
                ELSE 0
              END'));

        // Get any additional "used" points from transactions that have proper values
        $transactionPointsUsed = point_transactions::where('type', 'used')
            ->where('point', '>', 0)
            ->sum('point');

        return $badgePointsUsed + $transactionPointsUsed;
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
    public function destroy($id)
    {
        try {
            // Find the transaction by ID
            $transaction = point_transactions::findOrFail($id);

            // Log the transaction details before deletion
            Log::info('Deleting point transaction', [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'point' => $transaction->point,
                'from_user_id' => $transaction->from_user_id,
                'to_user_id' => $transaction->to_user_id
            ]);

            // Delete the transaction
            $transaction->delete();

            // Log success
            Log::info('Point transaction deleted successfully.');

            // Redirect back with success message
            return redirect()->back()->with('success', 'Point transaction deleted successfully.');

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error deleting point transaction: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            // Redirect back with error message
            return redirect()->back()->with('error', 'Error deleting point transaction: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate and fix any transaction records that have incorrect values
     */
    public function fixTransactionRecords()
    {
        $fixedCount = 0;

        // Find all "used" type transactions with 0 points
        $invalidTransactions = point_transactions::where('type', 'used')
            ->where('point', 0)
            ->get();

        foreach($invalidTransactions as $transaction) {
            // If both from_user_id and to_user_id are the same user, this is likely a badge purchase
            if($transaction->from_user_id === $transaction->to_user_id && $transaction->from_user_id !== null) {
                // Try to find a related service post from around the same time
                $relatedPost = ServicePost::where('user_id', $transaction->from_user_id)
                    ->where('have_badge', '!=', 'عادي')
                    ->where('created_at', '>=', $transaction->created_at->subMinutes(5))
                    ->where('created_at', '<=', $transaction->created_at->addMinutes(5))
                    ->first();

                if($relatedPost) {
                    // Calculate the correct points based on badge type and duration
                    $badgePointCost = 0;

                    if($relatedPost->have_badge === 'ذهبي') {
                        $badgePointCost = $relatedPost->badge_duration * 2;
                    } elseif($relatedPost->have_badge === 'ماسي') {
                        $badgePointCost = $relatedPost->badge_duration * 10;
                    }

                    if($badgePointCost > 0) {
                        $transaction->point = $badgePointCost;
                        $transaction->save();
                        $fixedCount++;
                    }
                }
            }
        }

        return redirect()->route('point_transactions.index')
            ->with('success', "Fixed $fixedCount transaction records with incorrect point values.");
    }



}
