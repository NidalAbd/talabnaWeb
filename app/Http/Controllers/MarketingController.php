<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServicePost;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        // Marketing metrics
        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', 'active')->count(),
            'total_posts' => ServicePost::count(),
            'premium_posts' => ServicePost::where('is_premium', true)->count(),
            'monthly_growth' => $this->calculateMonthlyGrowth(),
            'engagement_rate' => $this->calculateEngagementRate(),
        ];

        // Recent marketing activities
        $recentActivities = $this->getRecentActivities();

        return view('admin.marketing.dashboard', compact('metrics', 'recentActivities'));
    }

    private function calculateMonthlyGrowth()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function calculateEngagementRate()
    {
        $totalViews = ServicePost::sum('view_count');
        $totalPosts = ServicePost::count();
        
        if ($totalPosts == 0) return 0;
        
        return round($totalViews / $totalPosts, 2);
    }

    private function getRecentActivities()
    {
        return [
            'new_users' => User::latest()->take(5)->get(),
            'new_posts' => ServicePost::latest()->take(5)->get(),
            'top_posts' => ServicePost::orderBy('view_count', 'desc')->take(5)->get(),
        ];
    }
} 