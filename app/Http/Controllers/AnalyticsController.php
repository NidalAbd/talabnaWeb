<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\point_purchase_requests;
use App\Models\Level;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Show the user analytics page with real data.
     */
    public function userAnalytics()
    {
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
            ->get();
            
        // Recent user registrations
        $recentUsers = User::latest()->take(10)->get();
        
        // User demographics (simplified)
        $userDemographics = [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'banned' => $bannedUsers,
            'with_posts' => $usersWithPosts,
            'with_points' => $usersWithPoints,
            'premium' => $premiumUsers,
            'monthly_active' => $monthlyActiveUsers
        ];

        return view('admin.analytics.user_analytics', compact(
            'totalUsers', 'activeUsers', 'bannedUsers', 'newUsersThisMonth',
            'userGrowthRate', 'userDemographics', 'topUsersByPosts', 'recentUsers'
        ));
    }

    /**
     * Show the point analytics page with real data.
     */
    public function pointAnalytics()
    {
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
        $pointsUsedForBadges = $regularLevel ? ServicePost::where('level_id', '!=', $regularLevel->id)->count() * 10 : 0; // Assuming 10 points per badge
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
            ->get();
            
        // Recent point transactions
        $recentTransactions = point_transactions::with(['fromUser', 'toUser'])
            ->latest()
            ->take(15)
            ->get();
            
        // Point system metrics
        $pointMetrics = [
            'total_points' => $totalPointsInSystem,
            'total_transactions' => $totalTransactions,
            'total_requests' => $totalPurchaseRequests,
            'monthly_sold' => $monthlyPointsSold,
            'monthly_used' => $monthlyPointsUsed,
            'growth_rate' => $pointsGrowthRate,
            'points_used_badges' => $pointsUsedForBadges,
            'points_used_features' => $pointsUsedForFeatures,
            'total_used' => $totalPointsUsed,
            'pending_requests' => $pendingRequests,
            'approved_requests' => $approvedRequests,
            'cancelled_requests' => $cancelledRequests
        ];

        return view('admin.analytics.point_analytics', compact(
            'pointMetrics', 'topPointUsers', 'recentTransactions'
        ));
    }

    /**
     * Show the post analytics page with real data.
     */
    public function postAnalytics()
    {
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
        $topPostsByViews = ServicePost::with(['user', 'category'])
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get();
            
        // Recent posts
        $recentPosts = ServicePost::with(['user', 'category'])
            ->latest()
            ->take(15)
            ->get();
            
        // Post metrics
        $postMetrics = [
            'total' => $totalPosts,
            'published' => $publishedPosts,
            'pending' => $pendingPosts,
            'rejected' => $rejectedPosts,
            'new_this_month' => $newPostsThisMonth,
            'growth_rate' => $postGrowthRate,
            'golden' => $goldenPosts,
            'diamond' => $diamondPosts,
            'normal' => $normalPosts
        ];

        return view('admin.analytics.post_analytics', compact(
            'postMetrics', 'topPostsByViews', 'recentPosts'
        ));
    }
} 