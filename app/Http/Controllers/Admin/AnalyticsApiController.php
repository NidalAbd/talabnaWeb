<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicePost;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\point_purchase_requests;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnalyticsApiController extends Controller
{
    /**
     * Get user analytics data
     */
    public function getUserAnalytics(): JsonResponse
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            // User statistics
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', 'active')->count();
            $bannedUsers = User::where('is_active', 'banned')->count();

            // Monthly user growth
            $newUsersThisMonth = User::whereMonth('created_at', $currentMonth->month)->count();
            $newUsersLastMonth = User::whereMonth('created_at', $previousMonth->month)->count();

            // Growth calculations
            $userGrowthRate = $newUsersLastMonth > 0 ?
                (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 : 0;

            // Get level IDs for premium user counting
            $regularLevel = Level::where('name->ar', 'عادي')->first();

            // User engagement metrics
            $usersWithPosts = User::whereHas('servicePosts')->count();
            $usersWithPoints = User::whereHas('palservicePoints')->count();
            $premiumUsers = User::whereHas('servicePosts', function($query) use ($regularLevel) {
                if ($regularLevel) {
                    $query->where('level_id', '!=', $regularLevel->id);
                }
            })->count();

            // Monthly user activity
            $monthlyActiveUsers = User::whereHas('servicePosts', function($query) use ($currentMonth) {
                $query->whereMonth('created_at', $currentMonth->month);
            })->count();

            // Top users by posts
            $topUsersByPosts = User::withCount('servicePosts')
                ->orderBy('service_posts_count', 'desc')
                ->take(10)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->user_name,
                        'email' => $user->email,
                        'posts_count' => $user->service_posts_count
                    ];
                });

            // Recent user registrations
            $recentUsers = User::latest()
                ->take(10)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->user_name,
                        'email' => $user->email,
                        'created_at' => $user->created_at->toISOString()
                    ];
                });

            return response()->json([
                'stats' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'banned_users' => $bannedUsers,
                    'new_this_month' => $newUsersThisMonth,
                    'growth_rate' => round($userGrowthRate, 2),
                    'users_with_posts' => $usersWithPosts,
                    'users_with_points' => $usersWithPoints,
                    'premium_users' => $premiumUsers,
                    'monthly_active' => $monthlyActiveUsers
                ],
                'top_users' => $topUsersByPosts,
                'recent_users' => $recentUsers
            ]);
        } catch (\Exception $e) {
            Log::error('User Analytics Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load user analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get point analytics data
     */
    public function getPointAnalytics(): JsonResponse
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            // Point system statistics
            $totalPointsInSystem = palservice_points::sum('point');
            $totalTransactions = point_transactions::count();
            $totalPurchaseRequests = point_purchase_requests::count();

            // Monthly point statistics
            $monthlyPointsSold = point_purchase_requests::where('status', 'approved')
                ->whereMonth('created_at', $currentMonth->month)
                ->sum('points_requested');
            $monthlyPointsUsed = point_transactions::where('type', 'used')
                ->whereMonth('created_at', $currentMonth->month)
                ->sum('point');

            // Previous month for comparison
            $previousMonthPointsSold = point_purchase_requests::where('status', 'approved')
                ->whereMonth('created_at', $previousMonth->month)
                ->sum('points_requested');
            $pointsGrowthRate = $previousMonthPointsSold > 0 ?
                (($monthlyPointsSold - $previousMonthPointsSold) / $previousMonthPointsSold) * 100 : 0;

            // Get level IDs for badge counting
            $regularLevel = Level::where('name->ar', 'عادي')->first();

            // Point usage by type
            $pointsUsedForBadges = $regularLevel ? ServicePost::where('level_id', '!=', $regularLevel->id)->count() * 10 : 0;
            $pointsUsedForFeatures = point_transactions::where('type', 'used')->sum('point');
            $totalPointsUsed = $pointsUsedForBadges + $pointsUsedForFeatures;

            // Purchase request statistics
            $pendingRequests = point_purchase_requests::where('status', 'pending')->count();
            $approvedRequests = point_purchase_requests::where('status', 'approved')->count();
            $cancelledRequests = point_purchase_requests::where('status', 'cancelled')->count();

            // Top point users
            $topPointUsers = User::withSum('palservicePoints', 'point')
                ->orderBy('palservice_points_sum_point', 'desc')
                ->take(10)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->user_name,
                        'email' => $user->email,
                        'total_points' => $user->palservice_points_sum_point ?? 0
                    ];
                });

            // Recent point transactions
            $recentTransactions = point_transactions::with(['fromUser:id,user_name', 'toUser:id,user_name'])
                ->latest()
                ->take(15)
                ->get()
                ->map(function($transaction) {
                    return [
                        'id' => $transaction->id,
                        'from_user' => $transaction->fromUser ? $transaction->fromUser->user_name : null,
                        'to_user' => $transaction->toUser ? $transaction->toUser->user_name : null,
                        'point' => $transaction->point,
                        'type' => $transaction->type,
                        'created_at' => $transaction->created_at->toISOString()
                    ];
                });

            return response()->json([
                'stats' => [
                    'total_points' => $totalPointsInSystem,
                    'total_transactions' => $totalTransactions,
                    'total_requests' => $totalPurchaseRequests,
                    'monthly_sold' => $monthlyPointsSold,
                    'monthly_used' => $monthlyPointsUsed,
                    'growth_rate' => round($pointsGrowthRate, 2),
                    'points_used_badges' => $pointsUsedForBadges,
                    'points_used_features' => $pointsUsedForFeatures,
                    'total_used' => $totalPointsUsed,
                    'pending_requests' => $pendingRequests,
                    'approved_requests' => $approvedRequests,
                    'cancelled_requests' => $cancelledRequests
                ],
                'top_users' => $topPointUsers,
                'recent_transactions' => $recentTransactions
            ]);
        } catch (\Exception $e) {
            Log::error('Point Analytics Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load point analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get post analytics data
     */
    public function getPostAnalytics(): JsonResponse
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();

            // Post statistics
            $totalPosts = ServicePost::count();
            $publishedPosts = ServicePost::where('state', 'published')->count();
            $pendingPosts = ServicePost::where('state', 'not published')->count();
            $rejectedPosts = ServicePost::where('state', 'rejected')->count();

            // Monthly post growth
            $newPostsThisMonth = ServicePost::whereMonth('created_at', $currentMonth->month)->count();
            $newPostsLastMonth = ServicePost::whereMonth('created_at', $previousMonth->month)->count();

            // Growth calculations
            $postGrowthRate = $newPostsLastMonth > 0 ?
                (($newPostsThisMonth - $newPostsLastMonth) / $newPostsLastMonth) * 100 : 0;

            // Get level IDs for badge counting
            $goldLevel = Level::where('name->ar', 'ذهبي')->first();
            $diamondLevel = Level::where('name->ar', 'ماسي')->first();
            $regularLevel = Level::where('name->ar', 'عادي')->first();

            // Badge statistics
            $goldenPosts = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)->count() : 0;
            $diamondPosts = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)->count() : 0;
            $normalPosts = $regularLevel ? ServicePost::where('level_id', $regularLevel->id)->count() : 0;

            // Top posts by views
            $topPostsByViews = ServicePost::with(['user:id,user_name', 'category:id,name'])
                ->orderBy('view_count', 'desc')
                ->take(10)
                ->get()
                ->map(function($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'user' => $post->user ? $post->user->user_name : 'Unknown',
                        'category' => $post->category ? $post->category->name : 'Unknown',
                        'view_count' => $post->view_count,
                        'created_at' => $post->created_at->toISOString()
                    ];
                });

            // Recent posts
            $recentPosts = ServicePost::with(['user:id,user_name', 'category:id,name'])
                ->latest()
                ->take(15)
                ->get()
                ->map(function($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'user' => $post->user ? $post->user->user_name : 'Unknown',
                        'category' => $post->category ? $post->category->name : 'Unknown',
                        'state' => $post->state,
                        'created_at' => $post->created_at->toISOString()
                    ];
                });

            return response()->json([
                'stats' => [
                    'total' => $totalPosts,
                    'published' => $publishedPosts,
                    'pending' => $pendingPosts,
                    'rejected' => $rejectedPosts,
                    'new_this_month' => $newPostsThisMonth,
                    'growth_rate' => round($postGrowthRate, 2),
                    'golden' => $goldenPosts,
                    'diamond' => $diamondPosts,
                    'normal' => $normalPosts
                ],
                'top_posts' => $topPostsByViews,
                'recent_posts' => $recentPosts
            ]);
        } catch (\Exception $e) {
            Log::error('Post Analytics Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load post analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overview analytics (summary of all)
     */
    public function getOverview(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Users',
                    'value' => User::count(),
                    'icon' => 'fas fa-users',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Active Posts',
                    'value' => ServicePost::where('state', 'published')->count(),
                    'icon' => 'fas fa-file-alt',
                    'color' => 'success'
                ],
                [
                    'label' => 'Total Points',
                    'value' => palservice_points::sum('point'),
                    'icon' => 'fas fa-coins',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Point Transactions',
                    'value' => point_transactions::count(),
                    'icon' => 'fas fa-exchange-alt',
                    'color' => 'info'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load overview',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
