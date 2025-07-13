<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\point_purchase_requests;
use App\Models\point_transactions;
use App\Models\palservice_points;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'investor']);
    }

    public function index()
    {
        // Get current month and previous month
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();

        // User Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 'active')->count();
        $newUsersThisMonth = User::whereMonth('created_at', $currentMonth->month)->count();
        $newUsersLastMonth = User::whereMonth('created_at', $previousMonth->month)->count();
        $userGrowthRate = $newUsersLastMonth > 0 ? (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 : 0;

        // Financial Statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue($currentMonth);
        $previousMonthRevenue = $this->calculateMonthlyRevenue($previousMonth);
        $revenueGrowthRate = $previousMonthRevenue > 0 ? (($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;

        // Point System Statistics
        $totalPointsInSystem = palservice_points::sum('points');
        $pointsSoldThisMonth = $this->calculatePointsSold($currentMonth);
        $pointsSoldLastMonth = $this->calculatePointsSold($previousMonth);
        $pointsGrowthRate = $pointsSoldLastMonth > 0 ? (($pointsSoldThisMonth - $pointsSoldLastMonth) / $pointsSoldLastMonth) * 100 : 0;

        // Service Post Statistics
        $totalPosts = ServicePost::count();
        $publishedPosts = ServicePost::where('state', 'published')->count();
        $premiumPosts = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])->count();
        $postsThisMonth = ServicePost::whereMonth('created_at', $currentMonth->month)->count();

        // Monthly Revenue Chart Data
        $monthlyRevenueData = $this->getMonthlyRevenueData();

        // User Growth Chart Data
        $userGrowthData = $this->getUserGrowthData();

        // Top Performing Categories
        $topCategories = $this->getTopPerformingCategories();

        // Financial Metrics
        $financialMetrics = [
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'revenue_growth_rate' => $revenueGrowthRate,
            'average_revenue_per_user' => $totalUsers > 0 ? $totalRevenue / $totalUsers : 0,
            'revenue_per_post' => $publishedPosts > 0 ? $totalRevenue / $publishedPosts : 0,
        ];

        // Business Health Indicators
        $businessHealth = [
            'user_retention_rate' => $this->calculateUserRetentionRate(),
            'post_engagement_rate' => $this->calculatePostEngagementRate(),
            'premium_adoption_rate' => $totalPosts > 0 ? ($premiumPosts / $totalPosts) * 100 : 0,
            'point_utilization_rate' => $this->calculatePointUtilizationRate(),
        ];

        return view('admin.investor.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'newUsersThisMonth',
            'userGrowthRate',
            'totalRevenue',
            'monthlyRevenue',
            'revenueGrowthRate',
            'totalPointsInSystem',
            'pointsSoldThisMonth',
            'pointsGrowthRate',
            'totalPosts',
            'publishedPosts',
            'premiumPosts',
            'postsThisMonth',
            'monthlyRevenueData',
            'userGrowthData',
            'topCategories',
            'financialMetrics',
            'businessHealth'
        ));
    }

    private function calculateTotalRevenue()
    {
        // Calculate revenue from point sales
        $pointRevenue = point_purchase_requests::where('status', 'completed')
            ->sum(DB::raw('points * price_per_point'));

        // Add premium post revenue (if you have this data)
        $premiumRevenue = 0; // You can add premium post revenue calculation here

        return $pointRevenue + $premiumRevenue;
    }

    private function calculateMonthlyRevenue($month)
    {
        return point_purchase_requests::where('status', 'completed')
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->sum(DB::raw('points * price_per_point'));
    }

    private function calculatePointsSold($month)
    {
        return point_purchase_requests::where('status', 'completed')
            ->whereMonth('created_at', $month->month)
            ->whereYear('created_at', $month->year)
            ->sum('points');
    }

    private function getMonthlyRevenueData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = $this->calculateMonthlyRevenue($month);
            $data[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue
            ];
        }
        return $data;
    }

    private function getUserGrowthData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $users = User::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $data[] = [
                'month' => $month->format('M Y'),
                'users' => $users
            ];
        }
        return $data;
    }

    private function getTopPerformingCategories()
    {
        return ServicePost::select('categories.name', DB::raw('COUNT(*) as post_count'))
            ->join('categories', 'service_posts.categories_id', '=', 'categories.id')
            ->where('service_posts.state', 'published')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('post_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function calculateUserRetentionRate()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 'active')->count();
        
        return $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
    }

    private function calculatePostEngagementRate()
    {
        $totalPosts = ServicePost::where('state', 'published')->count();
        $postsWithViews = ServicePost::where('state', 'published')
            ->where('view_count', '>', 0)
            ->count();
        
        return $totalPosts > 0 ? ($postsWithViews / $totalPosts) * 100 : 0;
    }

    private function calculatePointUtilizationRate()
    {
        $totalPointsSold = point_purchase_requests::where('status', 'completed')->sum('points');
        $totalPointsUsed = point_transactions::where('type', 'used')->sum('points');
        
        return $totalPointsSold > 0 ? ($totalPointsUsed / $totalPointsSold) * 100 : 0;
    }

    public function financialReport()
    {
        // Detailed financial report for investors
        $currentYear = Carbon::now()->year;
        
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthDate = Carbon::createFromDate($currentYear, $month, 1);
            $revenue = $this->calculateMonthlyRevenue($monthDate);
            $expenses = $this->calculateMonthlyExpenses($monthDate);
            $profit = $revenue - $expenses;
            
            $monthlyData[] = [
                'month' => $monthDate->format('F'),
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $profit,
                'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0
            ];
        }

        return view('admin.investor.financial-report', compact('monthlyData'));
    }

    private function calculateMonthlyExpenses($month)
    {
        // This is a placeholder - you should implement actual expense calculation
        // based on your expense tracking system
        return 0;
    }

    public function businessMetrics()
    {
        // Key Performance Indicators (KPIs)
        $kpis = [
            'monthly_active_users' => User::where('is_active', 'active')
                ->whereMonth('updated_at', Carbon::now()->month)
                ->count(),
            'average_revenue_per_user' => $this->calculateAverageRevenuePerUser(),
            'customer_acquisition_cost' => $this->calculateCustomerAcquisitionCost(),
            'lifetime_value' => $this->calculateCustomerLifetimeValue(),
            'churn_rate' => $this->calculateChurnRate(),
        ];

        return view('admin.investor.business-metrics', compact('kpis'));
    }

    private function calculateAverageRevenuePerUser()
    {
        $totalRevenue = $this->calculateTotalRevenue();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? $totalRevenue / $totalUsers : 0;
    }

    private function calculateCustomerAcquisitionCost()
    {
        // Placeholder - implement based on your marketing costs
        return 0;
    }

    private function calculateCustomerLifetimeValue()
    {
        // Placeholder - implement based on your user behavior data
        return 0;
    }

    private function calculateChurnRate()
    {
        // Placeholder - implement based on your user activity data
        return 0;
    }
} 