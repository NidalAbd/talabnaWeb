<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\point_transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointTransactionController extends Controller
{
    /**
     * Get transactions for a specific user
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTransactions($userId)
    {
        try {
            // Get transactions where user is either sender or receiver
            $transactions = point_transactions::where('from_user_id', $userId)
                ->orWhere('to_user_id', $userId)
                ->with(['fromUser', 'toUser'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error fetching user transactions: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to fetch transactions'], 500);
        }
    }

    /**
     * Get transactions between two specific users
     *
     * @param int $fromUserId
     * @param int $toUserId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransactionsBetweenUsers($fromUserId, $toUserId)
    {
        try {
            // Get transactions between these two users
            $transactions = point_transactions::where(function($query) use ($fromUserId, $toUserId) {
                $query->where('from_user_id', $fromUserId)
                    ->where('to_user_id', $toUserId);
            })
                ->orWhere(function($query) use ($fromUserId, $toUserId) {
                    $query->where('from_user_id', $toUserId)
                        ->where('to_user_id', $fromUserId);
                })
                ->with(['fromUser', 'toUser'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions between users: ' . $e->getMessage(), [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to fetch transactions'], 500);
        }
    }

    /**
     * Get the last N transactions for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLastTransactions($userId, $limit = 5)
    {
        try {
            // Get the last N transactions for this user
            $transactions = point_transactions::where('from_user_id', $userId)
                ->orWhere('to_user_id', $userId)
                ->with(['fromUser', 'toUser'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error fetching last transactions: ' . $e->getMessage(), [
                'user_id' => $userId,
                'limit' => $limit,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to fetch transactions'], 500);
        }
    }
}
