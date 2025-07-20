<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\point_transactions;
use App\Models\palservice_points;
use App\Models\point_purchase_requests;
use Illuminate\Support\Facades\DB;
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
        $newUsersThisMonth = User::whereMonth('created_at', $currentMonth->month)->count();
        $newUsersLastMonth = User::whereMonth('created_at', $previousMonth->month)->count();
        
        // Growth calculations
        $userGrowthRate = $newUsersLastMonth > 0 ? 
            (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 : 0;
        
        // User engagement metrics
        $usersWithPosts = User::whereHas('servicePosts')->count();
        $usersWithPoints = User::whereHas('palservicePoints')->count();
        $premiumUsers = User::whereHas('servicePosts', function($query) {
            $query->where('have_badge', '!=', 'عادي');
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
            ->sum('amount');
        $monthlyPointsUsed = point_transactions::where('type', 'used')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('point');
            
        // Previous month for comparison
        $previousMonthPointsSold = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $previousMonth->month)
            ->sum('amount');
        $pointsGrowthRate = $previousMonthPointsSold > 0 ? 
            (($monthlyPointsSold - $previousMonthPointsSold) / $previousMonthPointsSold) * 100 : 0;
        
        // Point usage by type
        $pointsUsedForBadges = ServicePost::where('have_badge', '!=', 'عادي')->count() * 10; // Assuming 10 points per badge
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
            'used_for_badges' => $pointsUsedForBadges,
            'used_for_features' => $pointsUsedForFeatures,
            'total_used' => $totalPointsUsed
        ];

        return view('admin.analytics.point_analytics', compact(
            'totalPointsInSystem', 'totalTransactions', 'totalPurchaseRequests',
            'monthlyPointsSold', 'monthlyPointsUsed', 'pointsGrowthRate',
            'pendingRequests', 'approvedRequests', 'cancelledRequests',
            'topPointUsers', 'recentTransactions', 'pointMetrics'
        ));
    }
} 