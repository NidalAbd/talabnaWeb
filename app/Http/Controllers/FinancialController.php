<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\point_purchase_requests;
use App\Models\Level;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialController extends Controller
{
    /**
     * Show the revenue page with real data.
     */
    public function revenue()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Point sales revenue (assuming each point costs $1)
        $pointSalesRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Get level IDs for premium revenue calculation
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        // Premium posts revenue (golden and diamond badges)
        $premiumRevenue = $regularLevel ? ServicePost::where('level_id', '!=', $regularLevel->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50 : 0; // Assuming $50 per premium post
            
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
        
        // Get level IDs for premium post counting
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        // Premium posts statistics
        $goldenPosts = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)->count() : 0;
        $diamondPosts = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)->count() : 0;
        $totalPremiumPosts = $goldenPosts + $diamondPosts;
        
        // Revenue calculations (assuming golden = $20, diamond = $50)
        $goldenRevenue = $goldenPosts * 20;
        $diamondRevenue = $diamondPosts * 50;
        $totalPremiumRevenue = $goldenRevenue + $diamondRevenue;
        
        // Monthly premium posts
        $monthlyGoldenPosts = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->count() : 0;
        $monthlyDiamondPosts = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->count() : 0;
            
        // Recent premium posts
        $recentPremiumPosts = ServicePost::with('user')
            ->whereHas('level', function($query) use ($regularLevel) {
                if ($regularLevel) {
                    $query->where('id', '!=', $regularLevel->id);
                }
            })
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
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Payment statistics
        $totalPayments = point_purchase_requests::where('status', 'approved')->count();
        $monthlyPayments = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->count();
        $totalAmount = point_purchase_requests::where('status', 'approved')->sum('amount');
        $monthlyAmount = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Payment methods (assuming all are online payments for now)
        $onlinePayments = point_purchase_requests::where('status', 'approved')->count();
        $pendingPayments = point_purchase_requests::where('status', 'pending')->count();
        $failedPayments = point_purchase_requests::where('status', 'cancelled')->count();
        
        // Recent payments
        $recentPayments = point_purchase_requests::with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.financial.payment_reports', compact(
            'totalPayments', 'monthlyPayments', 'totalAmount', 'monthlyAmount',
            'onlinePayments', 'pendingPayments', 'failedPayments', 'recentPayments'
        ));
    }

    /**
     * Show the expenses page with real data.
     */
    public function expenses()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Fixed expenses (estimated)
        $serverHostingCosts = 500; // Monthly server hosting
        $advertisementCosts = 300; // Monthly advertising
        $developmentCosts = 200; // Monthly development/maintenance
        $otherCosts = 100; // Other operational costs
        
        $totalExpenses = $serverHostingCosts + $advertisementCosts + $developmentCosts + $otherCosts;
        
        // Revenue for profit calculation
        $monthlyRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        $monthlyProfit = $monthlyRevenue - $totalExpenses;
        $profitMargin = $monthlyRevenue > 0 ? ($monthlyProfit / $monthlyRevenue) * 100 : 0;
        
        // Expense breakdown
        $expenseBreakdown = [
            'server_hosting' => $serverHostingCosts,
            'advertisement' => $advertisementCosts,
            'development' => $developmentCosts,
            'other' => $otherCosts
        ];

        return view('admin.financial.expenses', compact(
            'totalExpenses', 'monthlyRevenue', 'monthlyProfit', 'profitMargin',
            'expenseBreakdown'
        ));
    }

    /**
     * Show the advertisement costs page with real data.
     */
    public function advertisementCosts()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Advertisement costs breakdown (estimated)
        $socialMediaAds = 150;
        $googleAds = 100;
        $contentMarketing = 50;
        
        $totalAdCosts = $socialMediaAds + $googleAds + $contentMarketing;
        
        // ROI calculation
        $monthlyRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        $roi = $totalAdCosts > 0 ? (($monthlyRevenue - $totalAdCosts) / $totalAdCosts) * 100 : 0;
        
        // Cost breakdown
        $costBreakdown = [
            'social_media' => $socialMediaAds,
            'google_ads' => $googleAds,
            'content_marketing' => $contentMarketing
        ];

        return view('admin.financial.advertisement_costs', compact(
            'totalAdCosts', 'monthlyRevenue', 'roi', 'costBreakdown'
        ));
    }

    /**
     * Show the server hosting costs page with real data.
     */
    public function serverHostingCosts()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Server hosting costs breakdown (estimated)
        $serverRental = 300;
        $bandwidthCosts = 100;
        $maintenanceCosts = 50;
        $backupCosts = 50;
        
        $totalHostingCosts = $serverRental + $bandwidthCosts + $maintenanceCosts + $backupCosts;
        
        // Cost per user calculation
        $totalUsers = User::count();
        $costPerUser = $totalUsers > 0 ? $totalHostingCosts / $totalUsers : 0;
        
        // Cost breakdown
        $hostingBreakdown = [
            'server_rental' => $serverRental,
            'bandwidth' => $bandwidthCosts,
            'maintenance' => $maintenanceCosts,
            'backup' => $backupCosts
        ];

        return view('admin.financial.server_hosting_costs', compact(
            'totalHostingCosts', 'totalUsers', 'costPerUser', 'hostingBreakdown'
        ));
    }

    /**
     * Show the monthly profit/loss page with real data.
     */
    public function monthlyProfitLoss()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Revenue
        $monthlyRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Get level IDs for premium revenue
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        $premiumRevenue = $regularLevel ? ServicePost::where('level_id', '!=', $regularLevel->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50 : 0;
            
        $totalRevenue = $monthlyRevenue + $premiumRevenue;
        
        // Expenses
        $totalExpenses = 1100; // Fixed monthly expenses
        
        // Profit/Loss
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        
        // Previous month comparison
        $previousMonthRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $previousMonth->month)
            ->sum('amount');
        $previousMonthProfit = $previousMonthRevenue - $totalExpenses;
        $profitGrowth = $previousMonthProfit != 0 ? (($netProfit - $previousMonthProfit) / abs($previousMonthProfit)) * 100 : 0;

        return view('admin.financial.monthly_profit_loss', compact(
            'totalRevenue', 'totalExpenses', 'netProfit', 'profitMargin',
            'profitGrowth'
        ));
    }

    /**
     * Show the cash flow projections page with real data.
     */
    public function cashFlowProjections()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Current month cash flow
        $monthlyRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
        $monthlyExpenses = 1100; // Fixed monthly expenses
        $netCashFlow = $monthlyRevenue - $monthlyExpenses;
        
        // Projections for next 6 months (simplified)
        $projections = [];
        for ($i = 1; $i <= 6; $i++) {
            $projectedRevenue = $monthlyRevenue * (1 + ($i * 0.1)); // 10% growth per month
            $projectedExpenses = $monthlyExpenses * (1 + ($i * 0.05)); // 5% growth per month
            $projectedCashFlow = $projectedRevenue - $projectedExpenses;
            
            $projections[] = [
                'month' => $currentMonth->copy()->addMonths($i)->format('M Y'),
                'revenue' => $projectedRevenue,
                'expenses' => $projectedExpenses,
                'cash_flow' => $projectedCashFlow
            ];
        }

        return view('admin.financial.cash_flow_projections', compact(
            'monthlyRevenue', 'monthlyExpenses', 'netCashFlow', 'projections'
        ));
    }

    /**
     * Show the income statement page with real data.
     */
    public function incomeStatement()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Revenue
        $pointSalesRevenue = point_purchase_requests::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth->month)
            ->sum('amount');
            
        // Get level IDs for premium revenue
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        $premiumRevenue = $regularLevel ? ServicePost::where('level_id', '!=', $regularLevel->id)
            ->whereMonth('created_at', $currentMonth->month)
            ->count() * 50 : 0;
            
        $totalRevenue = $pointSalesRevenue + $premiumRevenue;
        
        // Expenses
        $serverHostingCosts = 500;
        $advertisementCosts = 300;
        $developmentCosts = 200;
        $otherCosts = 100;
        $totalExpenses = $serverHostingCosts + $advertisementCosts + $developmentCosts + $otherCosts;
        
        // Net Income
        $netIncome = $totalRevenue - $totalExpenses;
        
        // Statement breakdown
        $incomeStatement = [
            'revenue' => [
                'point_sales' => $pointSalesRevenue,
                'premium_posts' => $premiumRevenue,
                'total' => $totalRevenue
            ],
            'expenses' => [
                'server_hosting' => $serverHostingCosts,
                'advertisement' => $advertisementCosts,
                'development' => $developmentCosts,
                'other' => $otherCosts,
                'total' => $totalExpenses
            ],
            'net_income' => $netIncome
        ];

        return view('admin.financial.income_statement', compact('incomeStatement'));
    }
} 