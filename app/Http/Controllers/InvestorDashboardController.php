<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\Categories;
use App\Models\palservice_points;
use App\Models\point_purchase_requests;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|investor');
    }

    public function index()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        // User Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 'active')->count();
        $newUsersThisMonth = User::whereMonth('created_at', $currentMonth->month)->count();
        $newUsersLastMonth = User::whereMonth('created_at', $previousMonth->month)->count();
        $userGrowthRate = $newUsersLastMonth > 0 ? (($newUsersThisMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 : 0;

        // Revenue Statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue($currentMonth);
        $previousMonthRevenue = $this->calculateMonthlyRevenue($previousMonth);
        $revenueGrowthRate = $previousMonthRevenue > 0 ? (($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;

        // Point System Statistics
        $totalPointsInSystem = palservice_points::sum('points');
        $pointsSoldThisMonth = $this->calculatePointsSold($currentMonth);
        $pointsSoldLastMonth = $this->calculatePointsSold($previousMonth);
        $pointsGrowthRate = $pointsSoldLastMonth > 0 ? (($pointsSoldThisMonth - $pointsSoldLastMonth) / $pointsSoldLastMonth) * 100 : 0;

        // Get level IDs for premium post counting
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();

        // Service Post Statistics
        $totalPosts = ServicePost::count();
        $publishedPosts = ServicePost::where('state', 'published')->count();
        $premiumPosts = 0;
        if ($goldLevel && $diamondLevel) {
            $premiumPosts = ServicePost::whereIn('level_id', [$goldLevel->id, $diamondLevel->id])->count();
        }
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
        return Categories::withCount('servicePosts')
            ->orderBy('service_posts_count', 'desc')
            ->take(5)
            ->get();
    }

    private function calculateUserRetentionRate()
    {
        // Simplified calculation - in a real app, you'd track user activity over time
        $activeUsers = User::where('is_active', 'active')->count();
        $totalUsers = User::count();
        return $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
    }

    private function calculatePostEngagementRate()
    {
        // Simplified calculation - average views per post
        $totalViews = ServicePost::sum('view_count');
        $totalPosts = ServicePost::count();
        return $totalPosts > 0 ? $totalViews / $totalPosts : 0;
    }

    private function calculatePointUtilizationRate()
    {
        // Simplified calculation - points used vs points sold
        $pointsUsed = DB::table('point_transactions')->where('type', 'used')->sum('point');
        $pointsSold = point_purchase_requests::where('status', 'completed')->sum('points');
        return $pointsSold > 0 ? ($pointsUsed / $pointsSold) * 100 : 0;
    }

    public function financialReport()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        // Revenue Analysis
        $currentMonthRevenue = $this->calculateMonthlyRevenue($currentMonth);
        $previousMonthRevenue = $this->calculateMonthlyRevenue($previousMonth);
        $revenueGrowth = $previousMonthRevenue > 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;

        // Expense Analysis (estimated)
        $currentMonthExpenses = $this->calculateMonthlyExpenses($currentMonth);
        $previousMonthExpenses = $this->calculateMonthlyExpenses($previousMonth);
        $expenseGrowth = $previousMonthExpenses > 0 ? (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100 : 0;

        // Profit Analysis
        $currentMonthProfit = $currentMonthRevenue - $currentMonthExpenses;
        $previousMonthProfit = $previousMonthRevenue - $previousMonthExpenses;
        $profitGrowth = $previousMonthProfit != 0 ? (($currentMonthProfit - $previousMonthProfit) / abs($previousMonthProfit)) * 100 : 0;

        // Key Financial Ratios
        $profitMargin = $currentMonthRevenue > 0 ? ($currentMonthProfit / $currentMonthRevenue) * 100 : 0;
        $expenseRatio = $currentMonthRevenue > 0 ? ($currentMonthExpenses / $currentMonthRevenue) * 100 : 0;

        return view('admin.investor.financial_report', compact(
            'currentMonthRevenue', 'previousMonthRevenue', 'revenueGrowth',
            'currentMonthExpenses', 'previousMonthExpenses', 'expenseGrowth',
            'currentMonthProfit', 'previousMonthProfit', 'profitGrowth',
            'profitMargin', 'expenseRatio'
        ));
    }

    private function calculateMonthlyExpenses($month)
    {
        // Estimated monthly expenses - in a real app, you'd have an expenses table
        return 1000; // Fixed monthly expenses
    }

    public function businessMetrics()
    {
        $currentMonth = Carbon::now()->startOfMonth();

        // Customer Metrics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 'active')->count();
        $newUsersThisMonth = User::whereMonth('created_at', $currentMonth->month)->count();

        // Revenue Metrics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue($currentMonth);

        // Business Metrics
        $averageRevenuePerUser = $this->calculateAverageRevenuePerUser();
        $customerAcquisitionCost = $this->calculateCustomerAcquisitionCost();
        $customerLifetimeValue = $this->calculateCustomerLifetimeValue();
        $churnRate = $this->calculateChurnRate();

        return view('admin.investor.business_metrics', compact(
            'totalUsers', 'activeUsers', 'newUsersThisMonth',
            'totalRevenue', 'monthlyRevenue',
            'averageRevenuePerUser', 'customerAcquisitionCost',
            'customerLifetimeValue', 'churnRate'
        ));
    }

    private function calculateAverageRevenuePerUser()
    {
        $totalRevenue = $this->calculateTotalRevenue();
        $totalUsers = User::count();
        return $totalUsers > 0 ? $totalRevenue / $totalUsers : 0;
    }

    private function calculateCustomerAcquisitionCost()
    {
        // Simplified calculation - total marketing spend / new users
        $marketingSpend = 1000; // Estimated monthly marketing spend
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->startOfMonth())->count();
        return $newUsersThisMonth > 0 ? $marketingSpend / $newUsersThisMonth : 0;
    }

    private function calculateCustomerLifetimeValue()
    {
        // Simplified calculation - average revenue per user * average customer lifespan
        $averageRevenuePerUser = $this->calculateAverageRevenuePerUser();
        $averageLifespan = 12; // Estimated months
        return $averageRevenuePerUser * $averageLifespan;
    }

    private function calculateChurnRate()
    {
        // Simplified calculation - inactive users / total users
        $inactiveUsers = User::where('is_active', '!=', 'active')->count();
        $totalUsers = User::count();
        return $totalUsers > 0 ? ($inactiveUsers / $totalUsers) * 100 : 0;
    }
} 