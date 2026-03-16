<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\point_transactions;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointTransactionsApiController extends Controller
{
    /**
     * Get paginated transactions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $type = $request->input('type'); // all, purchased, used, transferred, bonus, refund
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = point_transactions::with(['fromUser:id,user_name,email', 'toUser:id,user_name,email']);

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('point', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('fromUser', function($query) use ($search) {
                            $query->where('user_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('toUser', function($query) use ($search) {
                            $query->where('user_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            // Apply type filter
            if ($type && $type !== 'all') {
                $query->where('type', $type);
            }

            // Apply sorting
            $allowedSortFields = ['id', 'point', 'type', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $transactions = $query->paginate($perPage);

            // Transform data
            $transformedTransactions = $transactions->getCollection()->map(function ($transaction) {
                // Detect Google Play purchase
                $isGooglePlay = false;
                if ($transaction->metadata) {
                    $meta = is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : $transaction->metadata;
                    $isGooglePlay = isset($meta['payment_method']) && $meta['payment_method'] === 'google_play';
                }
                if (!$isGooglePlay && $transaction->type === 'purchase' && $transaction->from_user_id === null) {
                    $isGooglePlay = true;
                }

                $fromUser = null;
                if ($isGooglePlay) {
                    $fromUser = [
                        'id' => 0,
                        'name' => 'Google Play',
                        'email' => 'google-play@system',
                    ];
                } elseif ($transaction->fromUser) {
                    $fromUser = [
                        'id' => $transaction->fromUser->id,
                        'name' => $transaction->fromUser->user_name,
                        'email' => $transaction->fromUser->email,
                    ];
                }

                return [
                    'id' => $transaction->id,
                    'from_user' => $fromUser,
                    'to_user' => $transaction->toUser ? [
                        'id' => $transaction->toUser->id,
                        'name' => $transaction->toUser->user_name,
                        'email' => $transaction->toUser->email,
                    ] : null,
                    'user_id' => $transaction->user_id, // Legacy field
                    'point' => $transaction->point,
                    'type' => $transaction->type,
                    'description' => $transaction->description ?? '',
                    'created_at' => $transaction->created_at->toISOString(),
                ];
            });

            $transactions->setCollection($transformedTransactions);

            return response()->json([
                'transactions' => [
                    'data' => $transactions->items(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Point Transactions API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load transactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            // Calculate total points used from badge purchases
            $badgePointsUsed = ServicePost::where('have_badge', '!=', 'عادي')
                ->where('badge_duration', '>', 0)
                ->sum(DB::raw('CASE
                    WHEN have_badge = "ذهبي" THEN badge_duration * 2
                    WHEN have_badge = "ماسي" THEN badge_duration * 10
                    ELSE 0
                  END'));

            // Get transaction points by type
            $transactionPointsUsed = point_transactions::where('type', 'used')
                ->where('point', '>', 0)
                ->sum('point');

            $totalPointsUsed = $badgePointsUsed + $transactionPointsUsed;

            $stats = [
                [
                    'label' => 'Total Transactions',
                    'value' => point_transactions::count(),
                    'icon' => 'fas fa-exchange-alt',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Points Purchased',
                    'value' => point_transactions::where('type', 'purchased')->sum('point'),
                    'icon' => 'fas fa-shopping-cart',
                    'color' => 'success'
                ],
                [
                    'label' => 'Points Used',
                    'value' => $totalPointsUsed,
                    'icon' => 'fas fa-coins',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Points Transferred',
                    'value' => point_transactions::where('type', 'transferred')->sum('point'),
                    'icon' => 'fas fa-arrow-right',
                    'color' => 'info'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction types
     */
    public function getTypes(): JsonResponse
    {
        try {
            $types = [
                ['value' => 'purchased', 'label' => 'Purchased'],
                ['value' => 'used', 'label' => 'Used'],
                ['value' => 'transferred', 'label' => 'Transferred'],
                ['value' => 'bonus', 'label' => 'Bonus'],
                ['value' => 'refund', 'label' => 'Refund'],
            ];

            return response()->json(['types' => $types]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load types',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show transaction details
     */
    public function show($id): JsonResponse
    {
        try {
            $transaction = point_transactions::with(['fromUser', 'toUser'])
                ->findOrFail($id);

            return response()->json([
                'transaction' => [
                    'id' => $transaction->id,
                    'from_user' => $transaction->fromUser ? [
                        'id' => $transaction->fromUser->id,
                        'name' => $transaction->fromUser->user_name,
                        'email' => $transaction->fromUser->email,
                    ] : null,
                    'to_user' => $transaction->toUser ? [
                        'id' => $transaction->toUser->id,
                        'name' => $transaction->toUser->user_name,
                        'email' => $transaction->toUser->email,
                    ] : null,
                    'point' => $transaction->point,
                    'type' => $transaction->type,
                    'description' => $transaction->description ?? '',
                    'created_at' => $transaction->created_at->toISOString(),
                    'updated_at' => $transaction->updated_at->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch transaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a transaction
     */
    public function destroy($id): JsonResponse
    {
        try {
            $transaction = point_transactions::findOrFail($id);
            $transaction->delete();

            return response()->json([
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting transaction: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete transaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
