<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function investorRelations()
    {
        return view('admin.business.investor_relations');
    }

    public function investmentTracking()
    {
        return view('admin.business.investment_tracking');
    }

    public function strategicPlanning()
    {
        return view('admin.planning.strategic_planning');
    }

    public function monthlyBudgetPlanning()
    {
        return view('admin.budget.monthly_budget_planning');
    }

    public function expenseApprovals()
    {
        return view('admin.budget.expense_approvals');
    }

    public function budgetLimits()
    {
        return view('admin.budget.budget_limits');
    }
} 