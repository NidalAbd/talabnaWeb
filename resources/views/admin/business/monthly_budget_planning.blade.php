@extends('adminlte::page')

@section('title', 'Monthly Budget Planning')

@section('content_header')
    <h1>Monthly Budget Planning - {{ $currentMonth }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Monthly Overview -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($monthlyExpenses->sum('amount'), 2) }}</h3>
                    <p>Total Expenses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($monthlyRevenue->sum('amount'), 2) }}</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box {{ ($monthlyRevenue->sum('amount') - $monthlyExpenses->sum('amount')) >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner">
                    <h3>${{ number_format($monthlyRevenue->sum('amount') - $monthlyExpenses->sum('amount'), 2) }}</h3>
                    <p>Net Profit/Loss</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $budgets->count() }}</h3>
                    <p>Active Budgets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-piggy-bank"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget vs Actual -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Budget vs Actual Performance</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Budget Title</th>
                                    <th>Category</th>
                                    <th>Planned Budget</th>
                                    <th>Actual Spent</th>
                                    <th>Variance</th>
                                    <th>Utilization</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                @php
                                    $actualSpent = $monthlyExpenses->where('budget_id', $budget->id)->sum('amount');
                                    $variance = $budget->total_budget - $actualSpent;
                                    $utilization = $budget->total_budget > 0 ? ($actualSpent / $budget->total_budget) * 100 : 0;
                                @endphp
                                <tr class="{{ $actualSpent > $budget->total_budget ? 'table-danger' : ($utilization >= 80 ? 'table-warning' : '') }}">
                                    <td>{{ $budget->budget_title }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($budget->category) }}</span>
                                    </td>
                                    <td>${{ number_format($budget->total_budget, 2) }}</td>
                                    <td>${{ number_format($actualSpent, 2) }}</td>
                                    <td class="{{ $variance < 0 ? 'text-danger' : 'text-success' }}">
                                        ${{ number_format($variance, 2) }}
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar {{ $utilization >= 100 ? 'bg-danger' : ($utilization >= 80 ? 'bg-warning' : 'bg-success') }}" 
                                                 style="width: {{ min($utilization, 100) }}%">
                                                {{ number_format($utilization, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($actualSpent > $budget->total_budget)
                                            <span class="badge badge-danger">Over Budget</span>
                                        @elseif($utilization >= 80)
                                            <span class="badge badge-warning">Near Limit</span>
                                        @else
                                            <span class="badge badge-success">On Track</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Expenses by Category -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Expenses by Category</h3>
                </div>
                <div class="card-body">
                    <canvas id="expensesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Revenue by Type</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Expenses -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Expenses for {{ $currentMonth }}</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyExpenses->sortByDesc('amount')->take(10) as $expense)
                                <tr>
                                    <td>{{ $expense->expense_title }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($expense->expense_category) }}</span>
                                    </td>
                                    <td>${{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ $expense->vendor_name }}</td>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Planning Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Budget Planning Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#createBudgetModal">
                                    <i class="fas fa-plus"></i><br>
                                    Create New Budget
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <button class="btn btn-success btn-lg" data-toggle="modal" data-target="#adjustBudgetModal">
                                    <i class="fas fa-edit"></i><br>
                                    Adjust Budget
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <button class="btn btn-info btn-lg" onclick="exportBudgetReport()">
                                    <i class="fas fa-download"></i><br>
                                    Export Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Budget Modal -->
<div class="modal fade" id="createBudgetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Budget</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createBudgetForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="budget_title">Budget Title *</label>
                                <input type="text" class="form-control" id="budget_title" name="budget_title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_budget">Total Budget *</label>
                                <input type="number" step="0.01" class="form-control" id="total_budget" name="total_budget" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Category *</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="development">Development</option>
                                    <option value="operations">Operations</option>
                                    <option value="office">Office</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="budget_period">Budget Period *</label>
                                <select class="form-control" id="budget_period" name="budget_period" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .icon {
        color: rgba(0,0,0,.15);
    }
    .progress {
        height: 20px;
    }
    .progress-bar {
        line-height: 20px;
        font-size: 12px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Expenses Chart
    const expensesCtx = document.getElementById('expensesChart').getContext('2d');
    const expensesChart = new Chart(expensesCtx, {
        type: 'doughnut',
        data: {
            labels: @json($monthlyExpenses->groupBy('expense_category')->keys()),
            datasets: [{
                data: @json($monthlyExpenses->groupBy('expense_category')->map->sum('amount')->values()),
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: @json($monthlyRevenue->groupBy('revenue_type')->keys()),
            datasets: [{
                data: @json($monthlyRevenue->groupBy('revenue_type')->map->sum('amount')->values()),
                backgroundColor: [
                    '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56', '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Create budget form handler
    $('#createBudgetForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("business.budgets.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createBudgetModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Please fix the following errors:\n';
                for (let field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                alert(errorMessage);
            }
        });
    });
});

function exportBudgetReport() {
    // Implement export functionality
    alert('Exporting budget report for {{ $currentMonth }}...');
}
</script>
@stop 