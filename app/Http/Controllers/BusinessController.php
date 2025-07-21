<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentPayment;
use App\Models\BusinessExpense;
use App\Models\BusinessRevenue;
use App\Models\BusinessBudget;
use App\Models\PointPackage;
use App\Models\point_transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessController extends Controller
{
    public function dashboard()
    {
        // Get key metrics
        $totalInvestments = Investment::sum('investment_amount');
        $totalRevenue = BusinessRevenue::sum('amount');
        $totalExpenses = BusinessExpense::sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        
        // Get recent activities
        $recentInvestments = Investment::latest()->take(5)->get();
        $recentExpenses = BusinessExpense::with('investment')->latest()->take(5)->get();
        $recentRevenue = BusinessRevenue::latest()->take(5)->get();
        
        // Get monthly data for charts
        $monthlyRevenue = BusinessRevenue::selectRaw('MONTH(revenue_date) as month, SUM(amount) as total')
            ->whereYear('revenue_date', date('Y'))
            ->groupBy('month')
            ->get();
            
        $monthlyExpenses = BusinessExpense::selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->whereYear('expense_date', date('Y'))
            ->groupBy('month')
            ->get();
            
        $monthlyInvestments = Investment::selectRaw('MONTH(investment_date) as month, SUM(investment_amount) as total')
            ->whereYear('investment_date', date('Y'))
            ->groupBy('month')
            ->get();

        return view('admin.business.dashboard', compact(
            'totalInvestments',
            'totalRevenue', 
            'totalExpenses',
            'netProfit',
            'recentInvestments',
            'recentExpenses',
            'recentRevenue',
            'monthlyRevenue',
            'monthlyExpenses',
            'monthlyInvestments'
        ));
    }

    public function investorRelations()
    {
        $investments = Investment::with('payments')->latest()->get();
        $totalInvested = Investment::sum('investment_amount');
        $totalPaid = InvestmentPayment::sum('payment_amount');
        $totalRemaining = $totalInvested - $totalPaid;
        
        // Get investor statistics
        $investorStats = Investment::selectRaw('
            investor_name,
            COUNT(*) as investment_count,
            SUM(investment_amount) as total_invested,
            AVG(investment_amount) as avg_investment
        ')
        ->groupBy('investor_name')
        ->get();

        return view('admin.business.investor_relations', compact(
            'investments',
            'totalInvested',
            'totalPaid',
            'totalRemaining',
            'investorStats'
        ));
    }

    public function investmentTracking()
    {
        $investments = Investment::with(['payments', 'expenses'])->latest()->get();
        $pendingPayments = InvestmentPayment::where('status', 'pending')->get();
        $upcomingPayments = Investment::where('next_payment_date', '>=', now())
            ->where('next_payment_date', '<=', now()->addDays(30))
            ->get();

        // Calculate ROI for each investment
        foreach ($investments as $investment) {
            $totalPaid = $investment->payments->sum('payment_amount');
            $roi = $investment->investment_amount > 0 ? 
                (($totalPaid - $investment->investment_amount) / $investment->investment_amount) * 100 : 0;
            $investment->roi = round($roi, 2);
        }

        return view('admin.business.investment_tracking', compact(
            'investments',
            'pendingPayments',
            'upcomingPayments'
        ));
    }

    public function investmentWorkflow()
    {
        $investments = Investment::with(['payments'])
            ->latest()
            ->get();
            
        // Workflow statistics
        $activeInvestments = $investments->where('status', 'active')->count();
        $profitableInvestments = $investments->where('status', 'profitable')->count();
        $completedInvestments = $investments->where('status', 'completed')->count();
        $pendingInvestments = $investments->where('status', 'pending')->count();
        
        $totalProfitGenerated = $investments->sum('profit_generated');
        $totalProfitDistributed = $investments->sum('profit_distributed');
        $totalProfitRemaining = $totalProfitGenerated - $totalProfitDistributed;

        return view('admin.business.investment_workflow', compact(
            'investments',
            'activeInvestments',
            'profitableInvestments',
            'completedInvestments',
            'pendingInvestments',
            'totalProfitGenerated',
            'totalProfitDistributed',
            'totalProfitRemaining'
        ));
    }

    public function strategicPlanning()
    {
        $budgets = BusinessBudget::with(['creator', 'approver'])->latest()->get();
        $activeBudgets = BusinessBudget::active()->get();
        $expensesByCategory = BusinessExpense::selectRaw('expense_category, SUM(amount) as total')
            ->groupBy('expense_category')
            ->get();
        $revenueByType = BusinessRevenue::selectRaw('revenue_type, SUM(amount) as total')
            ->groupBy('revenue_type')
            ->get();

        return view('admin.business.strategic_planning', compact(
            'budgets',
            'activeBudgets',
            'expensesByCategory',
            'revenueByType'
        ));
    }

    public function monthlyBudgetPlanning()
    {
        $currentMonth = now()->format('Y-m');
        $budgets = BusinessBudget::where('budget_period', 'monthly')
            ->whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$currentMonth])
            ->with(['creator', 'approver'])
            ->get();
            
        $monthlyExpenses = BusinessExpense::whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$currentMonth])
            ->get();
            
        $monthlyRevenue = BusinessRevenue::whereRaw("DATE_FORMAT(revenue_date, '%Y-%m') = ?", [$currentMonth])
            ->get();

        return view('admin.business.monthly_budget_planning', compact(
            'budgets',
            'monthlyExpenses',
            'monthlyRevenue',
            'currentMonth'
        ));
    }

    public function expenseApprovals()
    {
        $pendingExpenses = BusinessExpense::where('status', 'pending')
            ->with(['investment', 'approver'])
            ->latest()
            ->get();
            
        $approvedExpenses = BusinessExpense::where('status', 'approved')
            ->with(['investment', 'approver'])
            ->latest()
            ->take(10)
            ->get();
            
        $rejectedExpenses = BusinessExpense::where('status', 'rejected')
            ->with(['investment', 'approver'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.business.expense_approvals', compact(
            'pendingExpenses',
            'approvedExpenses',
            'rejectedExpenses'
        ));
    }

    public function budgetLimits()
    {
        $budgets = BusinessBudget::with(['creator', 'approver', 'expenses'])
            ->latest()
            ->get();
            
        $overBudgetBudgets = $budgets->filter(function($budget) {
            return $budget->spent_amount > $budget->total_budget;
        });
        
        $nearLimitBudgets = $budgets->filter(function($budget) {
            $utilization = ($budget->spent_amount / $budget->total_budget) * 100;
            return $utilization >= 80 && $utilization < 100;
        });

        return view('admin.business.budget_limits', compact(
            'budgets',
            'overBudgetBudgets',
            'nearLimitBudgets'
        ));
    }

    public function revenueAnalysis()
    {
        // Point sales analysis
        $pointSales = point_transactions::with(['package', 'fromUser'])
            ->where('type', 'purchase')
            ->latest()
            ->get();
            
        $pointPackages = PointPackage::with('sales')->get();
        
        // Revenue by source
        $revenueBySource = BusinessRevenue::selectRaw('revenue_type, SUM(amount) as total')
            ->groupBy('revenue_type')
            ->get();
            
        // Monthly revenue trend
        $monthlyRevenueTrend = BusinessRevenue::selectRaw('
            DATE_FORMAT(revenue_date, "%Y-%m") as month,
            SUM(amount) as total
        ')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.business.revenue_analysis', compact(
            'pointSales',
            'pointPackages',
            'revenueBySource',
            'monthlyRevenueTrend'
        ));
    }

    public function expenseAnalysis()
    {
        $expenses = BusinessExpense::with(['investment', 'approver'])
            ->latest()
            ->get();
            
        // Expenses by category
        $expensesByCategory = BusinessExpense::selectRaw('expense_category, SUM(amount) as total')
            ->groupBy('expense_category')
            ->get();
            
        // Monthly expense trend
        $monthlyExpenseTrend = BusinessExpense::selectRaw('
            DATE_FORMAT(expense_date, "%Y-%m") as month,
            SUM(amount) as total
        ')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
        
        // Top vendors
        $topVendors = BusinessExpense::selectRaw('vendor_name, SUM(amount) as total')
            ->whereNotNull('vendor_name')
            ->groupBy('vendor_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return view('admin.business.expense_analysis', compact(
            'expenses',
            'expensesByCategory',
            'monthlyExpenseTrend',
            'topVendors'
        ));
    }

    public function profitLoss()
    {
        $currentYear = date('Y');
        
        // Monthly P&L
        $monthlyPL = DB::select("
            SELECT 
                month,
                COALESCE(revenue.total, 0) as revenue,
                COALESCE(expense.total, 0) as expenses,
                COALESCE(revenue.total, 0) - COALESCE(expense.total, 0) as profit
            FROM (
                SELECT DATE_FORMAT(revenue_date, '%Y-%m') as month, SUM(amount) as total
                FROM business_revenues 
                WHERE YEAR(revenue_date) = ?
                GROUP BY month
            ) revenue
            FULL OUTER JOIN (
                SELECT DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total
                FROM business_expenses 
                WHERE YEAR(expense_date) = ?
                GROUP BY month
            ) expense ON revenue.month = expense.month
            ORDER BY month
        ", [$currentYear, $currentYear]);
        
        // Yearly totals
        $yearlyRevenue = BusinessRevenue::whereYear('revenue_date', $currentYear)->sum('amount');
        $yearlyExpenses = BusinessExpense::whereYear('expense_date', $currentYear)->sum('amount');
        $yearlyProfit = $yearlyRevenue - $yearlyExpenses;
        
        // Revenue vs Investment
        $totalInvestments = Investment::sum('investment_amount');
        $roi = $totalInvestments > 0 ? ($yearlyProfit / $totalInvestments) * 100 : 0;

        return view('admin.business.profit_loss', compact(
            'monthlyPL',
            'yearlyRevenue',
            'yearlyExpenses',
            'yearlyProfit',
            'totalInvestments',
            'roi',
            'currentYear'
        ));
    }

    public function growthMetrics()
    {
        // User growth
        $userGrowth = User::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as new_users
        ')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
        
        // Revenue growth
        $revenueGrowth = BusinessRevenue::selectRaw('
            DATE_FORMAT(revenue_date, "%Y-%m") as month,
            SUM(amount) as revenue
        ')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
        
        // Point sales growth
        $pointSalesGrowth = point_transactions::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as sales_count,
            SUM(point) as total_points
        ')
        ->where('type', 'purchase')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.business.growth_metrics', compact(
            'userGrowth',
            'revenueGrowth',
            'pointSalesGrowth'
        ));
    }

    public function storeInvestment(Request $request)
    {
        $request->validate([
            'investor_name' => 'required|string|max:255',
            'investor_email' => 'required|email|max:255',
            'investment_type' => 'required|in:equity,loan,grant',
            'investment_amount' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'expected_roi' => 'required|numeric|min:0|max:100',
            'investment_period' => 'required|integer|min:1|max:60',
            'investor_share' => 'required|numeric|min:0|max:100',
            'owner_share' => 'required|numeric|min:0|max:100',
            'agreement_terms' => 'required|in:standard,custom,equity',
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        // Validate that shares add up to 100%
        if (($request->investor_share + $request->owner_share) != 100) {
            return response()->json([
                'success' => false,
                'message' => 'Investor and owner shares must add up to 100%'
            ], 422);
        }

        $investment = Investment::create([
            'investor_name' => $request->investor_name,
            'investor_email' => $request->investor_email,
            'investment_type' => $request->investment_type,
            'investment_amount' => $request->investment_amount,
            'currency' => $request->currency ?? 'USD',
            'investment_date' => $request->investment_date,
            'expected_roi' => $request->expected_roi,
            'investment_period' => $request->investment_period,
            'investor_share' => $request->investor_share,
            'owner_share' => $request->owner_share,
            'agreement_terms' => $request->agreement_terms,
            'purpose' => $request->purpose,
            'status' => 'active',
            'notes' => $request->notes,
            'total_paid' => 0,
            'remaining_amount' => $request->investment_amount,
            'profit_generated' => 0,
            'profit_distributed' => 0,
            'profit_remaining' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Investment added successfully! Expected ROI: ' . $request->expected_roi . '%, Period: ' . $request->investment_period . ' months'
        ]);
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'investment_id' => 'required|exists:investments,id',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:dividend,interest,principal,other',
            'notes' => 'nullable|string'
        ]);

        InvestmentPayment::create([
            'investment_id' => $request->investment_id,
            'payment_amount' => $request->payment_amount,
            'payment_date' => $request->payment_date,
            'payment_type' => $request->payment_type,
            'status' => 'completed',
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully'
        ]);
    }

    public function storeBudget(Request $request)
    {
        $request->validate([
            'budget_title' => 'required|string|max:255',
            'total_budget' => 'required|numeric|min:0',
            'budget_period' => 'required|in:monthly,quarterly,yearly',
            'category' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        BusinessBudget::create([
            'budget_title' => $request->budget_title,
            'budget_description' => $request->notes,
            'total_budget' => $request->total_budget,
            'currency' => 'USD',
            'budget_period' => $request->budget_period,
            'start_date' => now(),
            'end_date' => now()->addMonths($request->budget_period === 'monthly' ? 1 : ($request->budget_period === 'quarterly' ? 3 : 12)),
            'category' => $request->category,
            'status' => 'active',
            'allocated_amount' => 0,
            'spent_amount' => 0,
            'remaining_amount' => $request->total_budget,
            'created_by' => auth()->id(),
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget created successfully'
        ]);
    }

    public function approveExpense(Request $request, $id)
    {
        $expense = BusinessExpense::findOrFail($id);
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense approved successfully'
        ]);
    }

    public function rejectExpense(Request $request, $id)
    {
        $expense = BusinessExpense::findOrFail($id);
        $expense->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense rejected successfully'
        ]);
    }

    public function distributeProfit(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);
        
        if (!$investment->canDistributeProfit()) {
            return response()->json([
                'success' => false,
                'message' => 'Investment is not eligible for profit distribution'
            ], 422);
        }

        $distributionAmount = $request->distribution_amount;
        
        if ($distributionAmount > $investment->profit_remaining) {
            return response()->json([
                'success' => false,
                'message' => 'Distribution amount exceeds remaining profit'
            ], 422);
        }

        $profitSharing = $investment->profit_sharing;
        $investorAmount = $distributionAmount * ($investment->investor_share / 100);
        $ownerAmount = $distributionAmount * ($investment->owner_share / 100);

        $investment->distributeProfit($distributionAmount);

        // Create profit distribution records
        InvestmentPayment::create([
            'investment_id' => $investment->id,
            'payment_amount' => $investorAmount,
            'payment_date' => now(),
            'payment_type' => 'profit_distribution',
            'status' => 'completed',
            'notes' => 'Profit distribution to investor'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profit distributed successfully. Investor: $' . number_format($investorAmount, 2) . ', Owner: $' . number_format($ownerAmount, 2)
        ]);
    }

    public function updateInvestmentStatus(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);
        $newStatus = $request->status;

        $investment->update(['status' => $newStatus]);

        $message = 'Investment status updated to ' . ucfirst($newStatus);
        
        if ($newStatus === 'profitable') {
            $message .= '. Investment is now generating profit and can distribute earnings.';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function calculateProfitability()
    {
        $investments = Investment::where('status', 'active')->get();
        $totalProfit = 0;

        foreach ($investments as $investment) {
            // Calculate profit based on ROI and time passed
            $monthsPassed = $investment->investment_date->diffInMonths(now());
            $expectedProfit = $investment->expected_profit;
            $actualProfit = ($expectedProfit / $investment->investment_period) * min($monthsPassed, $investment->investment_period);
            
            if ($actualProfit > 0) {
                $investment->update([
                    'profit_generated' => $actualProfit,
                    'profit_remaining' => $actualProfit - $investment->profit_distributed
                ]);
                
                if ($actualProfit >= $investment->expected_profit * 0.8) { // 80% of expected profit
                    $investment->update(['status' => 'profitable']);
                }
                
                $totalProfit += $actualProfit;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profitability calculated. Total profit generated: $' . number_format($totalProfit, 2)
        ]);
    }
} 