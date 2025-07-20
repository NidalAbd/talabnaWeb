<?php

namespace App\Http\Controllers;

use App\Models\point_transactions;
use App\Models\ServicePost;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PointTransactionsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('point_transactions.index')) {
            return view('errors.403');
        }

        $query = point_transactions::with(['fromUser', 'toUser']);

        // Apply filters
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('user') && $request->user) {
            $query->where(function($q) use ($request) {
                $q->where('from_user_id', $request->user)
                  ->orWhere('to_user_id', $request->user);
            });
        }

        if ($request->has('date_range') && $request->date_range) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate total points used
        $totalPointsUsed = $this->calculateTotalPointsUsed();

        return view('point_transactions.index', compact('transactions', 'totalPointsUsed'));
    }

    /**
     * Calculate the total points used from badge purchases and other "used" transactions
     * This provides an accurate count even if individual transactions show 0
     */
    private function calculateTotalPointsUsed()
    {
        // Get level IDs
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        // Get points used from service posts with badges
        $badgePointsUsed = 0;
        
        if ($regularLevel) {
            $query = ServicePost::where('level_id', '!=', $regularLevel->id)
                ->where('badge_duration', '>', 0);
                
            if ($goldLevel) {
                $goldPoints = clone $query;
                $badgePointsUsed += $goldPoints->where('level_id', $goldLevel->id)->sum(DB::raw('badge_duration * 2'));
            }
            
            if ($diamondLevel) {
                $diamondPoints = clone $query;
                $badgePointsUsed += $diamondPoints->where('level_id', $diamondLevel->id)->sum(DB::raw('badge_duration * 10'));
            }
        }

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
                    ->where('level_id', '!=', null)
                    ->where('created_at', '>=', $transaction->created_at->subMinutes(5))
                    ->where('created_at', '<=', $transaction->created_at->addMinutes(5))
                    ->first();

                if($relatedPost) {
                    // Get level IDs for point calculation
                    $goldLevel = Level::where('name->ar', 'ذهبي')->first();
                    $diamondLevel = Level::where('name->ar', 'ماسي')->first();
                    
                    // Calculate the correct points based on level type and duration
                    $badgePointCost = 0;

                    if($goldLevel && $relatedPost->level_id === $goldLevel->id) {
                        $badgePointCost = $relatedPost->badge_duration * 2;
                    } elseif($diamondLevel && $relatedPost->level_id === $diamondLevel->id) {
                        $badgePointCost = $relatedPost->badge_duration * 10;
                    }

                    if($badgePointCost > 0) {
                        // Update the transaction with the correct point value
                        $transaction->update(['point' => $badgePointCost]);
                        $fixedCount++;
                        
                        Log::info('Fixed transaction record', [
                            'transaction_id' => $transaction->id,
                            'post_id' => $relatedPost->id,
                            'corrected_points' => $badgePointCost
                        ]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', "Fixed {$fixedCount} transaction records.");
    }
}
