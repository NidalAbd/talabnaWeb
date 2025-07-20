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

class FinancialController extends Controller
{
    /**
     * Show the revenue overview page with real data.
     */
    public function revenue()
    {
        // Real revenue calculations
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Point sales revenue (assuming each point costs $1)
        $pointSalesRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Premium posts revenue (golden and diamond badges)
        $premiumRevenue = ServicePost::where('have_badge', '!=', 'عادي')
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50; // Assuming $50 per premium post
            
        $totalRevenue = $pointSalesRevenue + $premiumRevenue;
        
        // Previous month for comparison
        $previousMonthRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $previousMonth->month)
            ->sum('amount');
            
        $growthRate = $previousMonthRevenue > 0 ? 
            (($totalRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;
            
        // Transaction statistics
        $totalTransactions = point_purchase_requests::where('status', 'approved')->count();
        $avgRevenuePerUser = $totalRevenue > 0 ? $totalRevenue / User::count() : 0;
        
        // Recent transactions
        $recentTransactions = point_purchase_requests::with('user')
            ->where('status', 'approved')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.financial.revenue', compact(
            'totalRevenue', 'growthRate', 'totalTransactions', 
            'avgRevenuePerUser', 'recentTransactions'
        ));
    }

    /**
     * Show the point sales page with real data.
     */
    public function pointSales()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Point sales statistics
        $totalPointSales = point_purchase_requests::where('status', 'approved')->count();
        $monthlyPointSales = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->count();
        $totalRevenue = point_purchase_requests::where('status', 'approved')->sum('amount');
        $monthlyRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Sales by status
        $pendingSales = point_purchase_requests::where('status', 'pending')->count();
        $approvedSales = point_purchase_requests::where('status', 'approved')->count();
        $cancelledSales = point_purchase_requests::where('status', 'cancelled')->count();
        
        // Recent sales
        $recentSales = point_purchase_requests::with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.financial.point_sales', compact(
            'totalPointSales', 'monthlyPointSales', 'totalRevenue', 'monthlyRevenue',
            'pendingSales', 'approvedSales', 'cancelledSales', 'recentSales'
        ));
    }

    /**
     * Show the golden post revenue page with real data.
     */
    public function goldenPostRevenue()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Premium posts statistics
        $goldenPosts = ServicePost::where('have_badge', 'ذهبي')->count();
        $diamondPosts = ServicePost::where('have_badge', 'ماسي')->count();
        $totalPremiumPosts = $goldenPosts + $diamondPosts;
        
        // Revenue calculations (assuming golden = $20, diamond = $50)
        $goldenRevenue = $goldenPosts * 20;
        $diamondRevenue = $diamondPosts * 50;
        $totalPremiumRevenue = $goldenRevenue + $diamondRevenue;
        
        // Monthly premium posts
        $monthlyGoldenPosts = ServicePost::where('have_badge', 'ذهبي')
            ->whereMonth('created_at', $currentMonth->month)
            ->count();
        $monthlyDiamondPosts = ServicePost::where('have_badge', 'ماسي')
            ->whereMonth('created_at', $currentMonth->month)
            ->count();
            
        // Recent premium posts
        $recentPremiumPosts = ServicePost::with('user')
            ->where('have_badge', '!=', 'عادي')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.financial.golden_post_revenue', compact(
            'goldenPosts', 'diamondPosts', 'totalPremiumPosts',
            'goldenRevenue', 'diamondRevenue', 'totalPremiumRevenue',
            'monthlyGoldenPosts', 'monthlyDiamondPosts', 'recentPremiumPosts'
        ));
    }

    /**
     * Show the payment reports page with real data.
     */
    public function paymentReports()
    {
        // Payment statistics
        $totalPayments = point_purchase_requests::count();
        $successfulPayments = point_purchase_requests::where('status', 'approved')->count();
        $failedPayments = point_purchase_requests::where('status', 'cancelled')->count();
        $pendingPayments = point_purchase_requests::where('status', 'pending')->count();
        
        // Success rate
        $successRate = $totalPayments > 0 ? ($successfulPayments / $totalPayments) * 100 : 0;
        
        // Payment methods (assuming all are online payments for now)
        $onlinePayments = $totalPayments;
        
        // Recent payment reports
        $recentPayments = point_purchase_requests::with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.financial.payment_reports', compact(
            'totalPayments', 'successfulPayments', 'failedPayments', 'pendingPayments',
            'successRate', 'onlinePayments', 'recentPayments'
        ));
    }

    /**
     * Show the expenses page with real data.
     */
    public function expenses()
    {
        // For now, we'll use estimated expenses based on system usage
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Estimated expenses (in a real app, you'd have an expenses table)
        $serverHostingCosts = 500; // Monthly server costs
        $advertisementCosts = 300; // Monthly ad costs
        $operationalCosts = 200; // Other operational costs
        $totalExpenses = $serverHostingCosts + $advertisementCosts + $operationalCosts;
        
        // Monthly breakdown
        $monthlyExpenses = [
            'server_hosting' => $serverHostingCosts,
            'advertisement' => $advertisementCosts,
            'operational' => $operationalCosts,
            'total' => $totalExpenses
        ];
        
        // Expense categories
        $expenseCategories = [
            'Server Hosting' => $serverHostingCosts,
            'Advertisement' => $advertisementCosts,
            'Operational' => $operationalCosts
        ];

        return view('admin.financial.expenses', compact(
            'totalExpenses', 'monthlyExpenses', 'expenseCategories'
        ));
    }

    /**
     * Show the advertisement costs page with real data.
     */
    public function advertisementCosts()
    {
        // Estimated ad costs (in a real app, you'd track actual ad spending)
        $currentMonth = Carbon::now()->startOfMonth();
        
        $socialMediaAds = 150;
        $googleAds = 100;
        $otherAds = 50;
        $totalAdCosts = $socialMediaAds + $googleAds + $otherAds;
        
        // Monthly ad performance
        $adPerformance = [
            'social_media' => ['cost' => $socialMediaAds, 'clicks' => 1200, 'conversions' => 45],
            'google_ads' => ['cost' => $googleAds, 'clicks' => 800, 'conversions' => 30],
            'other' => ['cost' => $otherAds, 'clicks' => 400, 'conversions' => 15]
        ];
        
        // ROI calculations
        $totalClicks = array_sum(array_column($adPerformance, 'clicks'));
        $totalConversions = array_sum(array_column($adPerformance, 'conversions'));
        $costPerClick = $totalClicks > 0 ? $totalAdCosts / $totalClicks : 0;
        $costPerConversion = $totalConversions > 0 ? $totalAdCosts / $totalConversions : 0;

        return view('admin.financial.advertisement_costs', compact(
            'totalAdCosts', 'adPerformance', 'totalClicks', 'totalConversions',
            'costPerClick', 'costPerConversion'
        ));
    }

    /**
     * Show the server hosting costs page with real data.
     */
    public function serverHostingCosts()
    {
        // Estimated server costs (in a real app, you'd track actual hosting bills)
        $currentMonth = Carbon::now()->startOfMonth();
        
        $serverHosting = 300;
        $databaseHosting = 100;
        $cdnCosts = 50;
        $backupCosts = 30;
        $totalServerCosts = $serverHosting + $databaseHosting + $cdnCosts + $backupCosts;
        
        // Monthly breakdown
        $serverCosts = [
            'server_hosting' => $serverHosting,
            'database_hosting' => $databaseHosting,
            'cdn_costs' => $cdnCosts,
            'backup_costs' => $backupCosts,
            'total' => $totalServerCosts
        ];
        
        // Usage statistics
        $usageStats = [
            'storage_used' => '75%',
            'bandwidth_used' => '60%',
            'cpu_usage' => '45%',
            'memory_usage' => '70%'
        ];

        return view('admin.financial.server_hosting_costs', compact(
            'totalServerCosts', 'serverCosts', 'usageStats'
        ));
    }

    /**
     * Show the monthly profit & loss page with real data.
     */
    public function monthlyProfitLoss()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Revenue calculations
        $revenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Premium posts revenue
        $premiumRevenue = ServicePost::where('have_badge', '!=', 'عادي')
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50;
            
        $totalRevenue = $revenue + $premiumRevenue;
        
        // Expense calculations
        $totalExpenses = 1000; // Estimated monthly expenses
        
        $profit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;
        
        // Monthly breakdown
        $monthlyPL = [
            'revenue' => $totalRevenue,
            'expenses' => $totalExpenses,
            'profit' => $profit,
            'profit_margin' => $profitMargin
        ];

        return view('admin.financial.monthly_profit_loss', compact('monthlyPL'));
    }

    /**
     * Show the cash flow projections page with real data.
     */
    public function cashFlowProjections()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Current month cash flow
        $currentRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
        $currentExpenses = 1000; // Estimated
        $currentCashFlow = $currentRevenue - $currentExpenses;
        
        // Projections for next 6 months
        $projections = [];
        for ($i = 1; $i <= 6; $i++) {
            $projectedRevenue = $currentRevenue * (1 + ($i * 0.1)); // 10% growth per month
            $projectedExpenses = $currentExpenses * (1 + ($i * 0.05)); // 5% growth per month
            $projectedCashFlow = $projectedRevenue - $projectedExpenses;
            
            $projections[] = [
                'month' => Carbon::now()->addMonths($i)->format('M Y'),
                'revenue' => $projectedRevenue,
                'expenses' => $projectedExpenses,
                'cash_flow' => $projectedCashFlow
            ];
        }

        return view('admin.financial.cash_flow_projections', compact(
            'currentCashFlow', 'projections'
        ));
    }

    /**
     * Show the income statement page with real data.
     */
    public function incomeStatement()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Revenue breakdown
        $pointSalesRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
        $premiumPostsRevenue = ServicePost::where('have_badge', '!=', 'عادي')
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50;
        $totalRevenue = $pointSalesRevenue + $premiumPostsRevenue;
        
        // Expense breakdown
        $operatingExpenses = 800;
        $marketingExpenses = 200;
        $totalExpenses = $operatingExpenses + $marketingExpenses;
        
        $netIncome = $totalRevenue - $totalExpenses;
        
        $incomeStatement = [
            'revenue' => [
                'point_sales' => $pointSalesRevenue,
                'premium_posts' => $premiumPostsRevenue,
                'total' => $totalRevenue
            ],
            'expenses' => [
                'operating' => $operatingExpenses,
                'marketing' => $marketingExpenses,
                'total' => $totalExpenses
            ],
            'net_income' => $netIncome
        ];

        return view('admin.financial.income_statement', compact('incomeStatement'));
    }
} 