<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\point_purchase_requests;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\User;
use App\Notifications\point_purchase_notifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointPurchaseRequestsApiController extends Controller
{
    /**
     * Get paginated purchase requests
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $status = $request->input('status'); // all, pending, approved, cancelled
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = point_purchase_requests::with(['user' => function($query) {
                $query->select('id', 'user_name', 'email', 'is_active');
            }]);

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('points_requested', 'like', "%{$search}%")
                        ->orWhereHas('user', function($query) use ($search) {
                            $query->where('user_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            // Apply status filter
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            // Apply sorting
            $allowedSortFields = ['id', 'points_requested', 'total_price', 'status', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                if ($sortBy === 'status') {
                    // Custom sort for status to prioritize pending first
                    $query->orderByRaw("CASE
                        WHEN status = 'pending' THEN 1
                        WHEN status = 'approved' THEN 2
                        WHEN status = 'cancelled' THEN 3
                        ELSE 4
                    END");
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }
            }

            $requests = $query->paginate($perPage);

            // Transform data
            $transformedRequests = $requests->getCollection()->map(function ($purchaseRequest) {
                return [
                    'id' => $purchaseRequest->id,
                    'user' => [
                        'id' => $purchaseRequest->user?->id,
                        'name' => $purchaseRequest->user?->user_name,
                        'email' => $purchaseRequest->user?->email,
                        'is_active' => $purchaseRequest->user?->is_active,
                    ],
                    'points_requested' => $purchaseRequest->points_requested,
                    'price_per_point' => $purchaseRequest->price_per_point,
                    'total_price' => $purchaseRequest->total_price,
                    'status' => $purchaseRequest->status,
                    'created_at' => $purchaseRequest->created_at->toISOString(),
                    'updated_at' => $purchaseRequest->updated_at->toISOString(),
                ];
            });

            $requests->setCollection($transformedRequests);

            return response()->json([
                'requests' => [
                    'data' => $requests->items(),
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Point Purchase Requests API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load purchase requests',
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
            $stats = [
                [
                    'label' => 'Total Requests',
                    'value' => point_purchase_requests::count(),
                    'icon' => 'fas fa-file-invoice-dollar',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Pending',
                    'value' => point_purchase_requests::where('status', 'pending')->count(),
                    'icon' => 'fas fa-clock',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Approved',
                    'value' => point_purchase_requests::where('status', 'approved')->count(),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Cancelled',
                    'value' => point_purchase_requests::where('status', 'cancelled')->count(),
                    'icon' => 'fas fa-times-circle',
                    'color' => 'danger'
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
     * Approve a purchase request
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $purchaseRequest = point_purchase_requests::findOrFail($id);

            if ($purchaseRequest->status !== 'pending') {
                return response()->json([
                    'error' => 'Only pending requests can be approved'
                ], 422);
            }

            // Update request status
            $purchaseRequest->update(['status' => 'approved']);

            // Add points to user
            $userPoints = palservice_points::where('user_id', $purchaseRequest->user_id)->first();

            if ($userPoints) {
                $userPoints->point += $purchaseRequest->points_requested;
                $userPoints->save();
            } else {
                palservice_points::create([
                    'user_id' => $purchaseRequest->user_id,
                    'point' => $purchaseRequest->points_requested
                ]);
            }

            // Create transaction record
            point_transactions::create([
                'user_id' => $purchaseRequest->user_id,
                'point' => $purchaseRequest->points_requested,
                'type' => 'purchased',
                'description' => 'Point purchase request approved #' . $purchaseRequest->id
            ]);

            DB::commit();

            // Send push notification (after commit to ensure data is saved)
            try {
                $user = User::find($purchaseRequest->user_id);
                if ($user && !empty($user->fcm_token)) {
                    $user->notify(new point_purchase_notifications(
                        'approved',
                        $purchaseRequest->points_requested,
                        (string) $purchaseRequest->id
                    ));
                    Log::info("FCM notification sent to user {$user->id} for approved purchase request #{$purchaseRequest->id}");
                }
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the approval
                Log::warning("Failed to send FCM notification for purchase request #{$purchaseRequest->id}: " . $notificationError->getMessage());
            }

            return response()->json([
                'message' => 'Purchase request approved successfully',
                'request' => $purchaseRequest->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving purchase request: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to approve purchase request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a purchase request
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $purchaseRequest = point_purchase_requests::findOrFail($id);

            if ($purchaseRequest->status !== 'pending') {
                return response()->json([
                    'error' => 'Only pending requests can be cancelled'
                ], 422);
            }

            $purchaseRequest->update(['status' => 'cancelled']);

            // Send push notification
            try {
                $user = User::find($purchaseRequest->user_id);
                if ($user && !empty($user->fcm_token)) {
                    $user->notify(new point_purchase_notifications(
                        'cancelled',
                        $purchaseRequest->points_requested,
                        (string) $purchaseRequest->id
                    ));
                    Log::info("FCM notification sent to user {$user->id} for cancelled purchase request #{$purchaseRequest->id}");
                }
            } catch (\Exception $notificationError) {
                Log::warning("Failed to send FCM notification for cancelled purchase request #{$purchaseRequest->id}: " . $notificationError->getMessage());
            }

            return response()->json([
                'message' => 'Purchase request cancelled successfully',
                'request' => $purchaseRequest->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling purchase request: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to cancel purchase request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a purchase request
     */
    public function destroy($id): JsonResponse
    {
        try {
            $purchaseRequest = point_purchase_requests::findOrFail($id);

            // Prevent deletion of approved requests
            if ($purchaseRequest->status === 'approved') {
                return response()->json([
                    'error' => 'Cannot delete approved purchase requests'
                ], 422);
            }

            $purchaseRequest->delete();

            return response()->json([
                'message' => 'Purchase request deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting purchase request: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete purchase request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve pending requests
     */
    public function bulkApprove(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $pendingRequests = point_purchase_requests::where('status', 'pending')->get();
            $approvedCount = 0;

            foreach ($pendingRequests as $purchaseRequest) {
                // Update request status
                $purchaseRequest->update(['status' => 'approved']);

                // Add points to user
                $userPoints = palservice_points::where('user_id', $purchaseRequest->user_id)->first();

                if ($userPoints) {
                    $userPoints->point += $purchaseRequest->points_requested;
                    $userPoints->save();
                } else {
                    palservice_points::create([
                        'user_id' => $purchaseRequest->user_id,
                        'point' => $purchaseRequest->points_requested
                    ]);
                }

                // Create transaction record
                point_transactions::create([
                    'user_id' => $purchaseRequest->user_id,
                    'point' => $purchaseRequest->points_requested,
                    'type' => 'purchased',
                    'description' => 'Point purchase request approved #' . $purchaseRequest->id
                ]);

                $approvedCount++;
            }

            DB::commit();

            // Send notifications after commit (batch)
            foreach ($pendingRequests as $purchaseRequest) {
                try {
                    $user = User::find($purchaseRequest->user_id);
                    if ($user && !empty($user->fcm_token)) {
                        $user->notify(new point_purchase_notifications(
                            'approved',
                            $purchaseRequest->points_requested,
                            (string) $purchaseRequest->id
                        ));
                    }
                } catch (\Exception $notificationError) {
                    Log::warning("Failed to send FCM notification for bulk approved request #{$purchaseRequest->id}: " . $notificationError->getMessage());
                }
            }

            return response()->json([
                'message' => "{$approvedCount} purchase requests approved successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk approving requests: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to approve requests',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
