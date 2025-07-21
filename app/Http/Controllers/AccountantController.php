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

class AccountantController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accountant_dashboard')->only(['dashboard']);
        $this->middleware('permission:accountant_expenses')->only(['expenses', 'approveExpense', 'rejectExpense']);
        $this->middleware('permission:accountant_revenues')->only(['revenues', 'addRevenue']);
        $this->middleware('permission:accountant_budgets')->only(['budgets', 'createBudget']);
        $this->middleware('permission:accountant_investments')->only(['investments']);
        $this->middleware('permission:accountant_financial_reports')->only(['financialReports']);
        $this->middleware('permission:accountant_tax_reports')->only(['taxReports']);
        $this->middleware('permission:accountant_audit_trail')->only(['auditTrail']);
    }

    public function dashboard()
    {
        // Get key financial metrics
        $totalRevenue = BusinessRevenue::sum('amount');
        $totalExpenses = BusinessExpense::sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $pendingExpenses = BusinessExpense::where('status', 'pending')->count();
        $approvedExpenses = BusinessExpense::where('status', 'approved')->count();
        
        // Get monthly data for charts
        $monthlyRevenue = BusinessRevenue::selectRaw('MONTH(revenue_date) as month, SUM(amount) as total')
            ->whereYear('revenue_date', date('Y'))
            ->groupBy('month')
            ->get();
            
        $monthlyExpenses = BusinessExpense::selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->whereYear('expense_date', date('Y'))
            ->groupBy('month')
            ->get();

        // Get recent activities
        $recentExpenses = BusinessExpense::with('investment')->latest()->take(10)->get();
        $recentRevenue = BusinessRevenue::latest()->take(10)->get();
        $pendingApprovals = BusinessExpense::where('status', 'pending')->latest()->take(5)->get();

        return view('admin.accountant.dashboard', compact(
            'totalRevenue',
            'totalExpenses', 
            'netProfit',
            'pendingExpenses',
            'approvedExpenses',
            'monthlyRevenue',
            'monthlyExpenses',
            'recentExpenses',
            'recentRevenue',
            'pendingApprovals'
        ));
    }

    public function expenses(Request $request)
    {
        $query = BusinessExpense::with(['investment', 'budget', 'approver']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('expense_category', $request->category);
        }
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->latest()->paginate(15);
        $categories = BusinessExpense::distinct()->pluck('expense_category');
        $statuses = ['pending', 'approved', 'paid', 'rejected'];

        return view('admin.accountant.expenses', compact('expenses', 'categories', 'statuses'));
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
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense rejected successfully'
        ]);
    }

    public function revenues(Request $request)
    {
        $query = BusinessRevenue::with(['pointPackage', 'user']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('revenue_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->where('revenue_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('revenue_date', '<=', $request->date_to);
        }

        $revenues = $query->latest()->paginate(15);
        $types = ['point_sales', 'advertising', 'premium_features', 'other'];
        $statuses = ['received', 'pending', 'failed'];

        return view('admin.accountant.revenues', compact('revenues', 'types', 'statuses'));
    }

    public function addRevenue(Request $request)
    {
        $request->validate([
            'revenue_title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'revenue_type' => 'required|in:point_sales,advertising,premium_features,other',
            'revenue_date' => 'required|date',
            'payment_method' => 'required|string',
            'customer_name' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        BusinessRevenue::create([
            'revenue_title' => $request->revenue_title,
            'revenue_description' => $request->notes,
            'amount' => $request->amount,
            'currency' => 'USD',
            'revenue_type' => $request->revenue_type,
            'revenue_date' => $request->revenue_date,
            'payment_method' => $request->payment_method,
            'customer_name' => $request->customer_name,
            'status' => 'received',
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Revenue added successfully'
        ]);
    }

    public function budgets(Request $request)
    {
        $query = BusinessBudget::with(['creator', 'approver']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('period')) {
            $query->where('budget_period', $request->period);
        }

        $budgets = $query->latest()->paginate(15);
        $categories = BusinessBudget::distinct()->pluck('category');
        $periods = ['monthly', 'quarterly', 'yearly'];
        $statuses = ['active', 'completed', 'cancelled'];

        return view('admin.accountant.budgets', compact('budgets', 'categories', 'periods', 'statuses'));
    }

    public function createBudget(Request $request)
    {
        $request->validate([
            'budget_title' => 'required|string|max:255',
            'total_budget' => 'required|numeric|min:0',
            'budget_period' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'category' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        BusinessBudget::create([
            'budget_title' => $request->budget_title,
            'budget_description' => $request->notes,
            'total_budget' => $request->total_budget,
            'currency' => 'USD',
            'budget_period' => $request->budget_period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
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

    public function investments()
    {
        $investments = Investment::with(['payments'])->latest()->get();
        $totalInvested = Investment::sum('investment_amount');
        $totalPaid = InvestmentPayment::sum('payment_amount');
        $totalRemaining = $totalInvested - $totalPaid;

        // Calculate ROI for each investment
        foreach ($investments as $investment) {
            $totalPaid = $investment->payments->sum('payment_amount');
            $roi = $investment->investment_amount > 0 ? 
                (($totalPaid - $investment->investment_amount) / $investment->investment_amount) * 100 : 0;
            $investment->roi = $roi;
        }

        return view('admin.accountant.investments', compact('investments', 'totalInvested', 'totalPaid', 'totalRemaining'));
    }

    public function financialReports(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year = $request->get('year', date('Y'));

        // Get financial data based on period
        if ($period === 'monthly') {
            $revenues = BusinessRevenue::selectRaw('MONTH(revenue_date) as period, SUM(amount) as total')
                ->whereYear('revenue_date', $year)
                ->groupBy('period')
                ->get();
                
            $expenses = BusinessExpense::selectRaw('MONTH(expense_date) as period, SUM(amount) as total')
                ->whereYear('expense_date', $year)
                ->groupBy('period')
                ->get();
        } else {
            $revenues = BusinessRevenue::selectRaw('QUARTER(revenue_date) as period, SUM(amount) as total')
                ->whereYear('revenue_date', $year)
                ->groupBy('period')
                ->get();
                
            $expenses = BusinessExpense::selectRaw('QUARTER(expense_date) as period, SUM(amount) as total')
                ->whereYear('expense_date', $year)
                ->groupBy('period')
                ->get();
        }

        // Calculate profit/loss
        $profitLoss = [];
        foreach ($revenues as $revenue) {
            $expense = $expenses->where('period', $revenue->period)->first();
            $expenseAmount = $expense ? $expense->total : 0;
            $profitLoss[] = [
                'period' => $revenue->period,
                'revenue' => $revenue->total,
                'expenses' => $expenseAmount,
                'profit' => $revenue->total - $expenseAmount
            ];
        }

        return view('admin.accountant.financial_reports', compact('profitLoss', 'period', 'year'));
    }

    public function taxReports(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        // Calculate tax-related data
        $totalRevenue = BusinessRevenue::whereYear('revenue_date', $year)->sum('amount');
        $totalExpenses = BusinessExpense::whereYear('expense_date', $year)->sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;
        
        // Tax calculations (example - adjust based on your tax rules)
        $taxRate = 0.15; // 15% tax rate
        $estimatedTax = $netIncome * $taxRate;
        
        // Revenue by type for tax reporting
        $revenueByType = BusinessRevenue::whereYear('revenue_date', $year)
            ->selectRaw('revenue_type, SUM(amount) as total')
            ->groupBy('revenue_type')
            ->get();
            
        // Expenses by category for tax deductions
        $expensesByCategory = BusinessExpense::whereYear('expense_date', $year)
            ->selectRaw('expense_category, SUM(amount) as total')
            ->groupBy('expense_category')
            ->get();

        return view('admin.accountant.tax_reports', compact(
            'totalRevenue',
            'totalExpenses',
            'netIncome',
            'estimatedTax',
            'revenueByType',
            'expensesByCategory',
            'year'
        ));
    }

    public function auditTrail(Request $request)
    {
        $query = DB::table('audit_logs')->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $auditLogs = $query->paginate(20);
        $actions = ['create', 'update', 'delete', 'approve', 'reject'];
        $users = User::pluck('name', 'id');

        return view('admin.accountant.audit_trail', compact('auditLogs', 'actions', 'users'));
    }
} 