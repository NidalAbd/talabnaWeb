<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\palservice_points;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PalServicePointsApiController extends Controller
{
    /**
     * Get list of users with their point balances
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $minPoints = $request->input('min_points');
            $maxPoints = $request->input('max_points');
            $sortField = $request->input('sort_field', 'point');
            $sortDirection = $request->input('sort_direction', 'desc');

            $query = palservice_points::with(['user:id,user_name,email']);

            // Search by user
            if ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('user_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('id', 'LIKE', "%{$search}%");
                });
            }

            // Filter by minimum points
            if ($minPoints !== null) {
                $query->where('point', '>=', $minPoints);
            }

            // Filter by maximum points
            if ($maxPoints !== null) {
                $query->where('point', '<=', $maxPoints);
            }

            // Apply sorting
            if (in_array($sortField, ['point', 'created_at', 'updated_at'])) {
                $query->orderBy($sortField, $sortDirection);
            }

            $points = $query->paginate($perPage);

            return response()->json([
                'data' => $points->items(),
                'current_page' => $points->currentPage(),
                'last_page' => $points->lastPage(),
                'per_page' => $points->perPage(),
                'total' => $points->total()
            ]);
        } catch (\Exception $e) {
            Log::error('Pal Service Points Index Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load points',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for point system
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalPoints = palservice_points::sum('point');
            $totalUsers = palservice_points::count();
            $usersWithPoints = palservice_points::where('point', '>', 0)->count();
            $usersWithoutPoints = palservice_points::where('point', '=', 0)->count();
            $averagePoints = $totalUsers > 0 ? round($totalPoints / $totalUsers, 2) : 0;
            $maxPoints = palservice_points::max('point') ?? 0;

            return response()->json([
                'total_points' => $totalPoints,
                'total_users' => $totalUsers,
                'users_with_points' => $usersWithPoints,
                'users_without_points' => $usersWithoutPoints,
                'average_points' => $averagePoints,
                'max_points' => $maxPoints
            ]);
        } catch (\Exception $e) {
            Log::error('Pal Service Points Stats Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top users by points
     */
    public function getTopUsers(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            $topUsers = palservice_points::with(['user:id,user_name,email'])
                ->orderBy('point', 'desc')
                ->take($limit)
                ->get();

            return response()->json($topUsers);
        } catch (\Exception $e) {
            Log::error('Top Users Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load top users',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
