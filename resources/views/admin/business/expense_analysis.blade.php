@extends('adminlte::page')

@section('title', 'Expense Analysis')

@section('content_header')
    <h1>Expense Analysis</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Expense Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($expenses->sum('amount'), 2) }}</h3>
                    <p>Total Expenses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $expenses->count() }}</h3>
                    <p>Total Transactions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $expenses->where('status', 'approved')->count() }}</h3>
                    <p>Approved Expenses</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $expenses->where('status', 'pending')->count() }}</h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Expense Trend</h3>
                </div>
                <div class="card-body">
                    <canvas id="expenseTrendChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Expenses by Category</h3>
                </div>
                <div class="card-body">
                    <canvas id="expenseCategoryChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Analysis -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Expense Categories</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Amount</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expensesByCategory as $category)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($category->expense_category) }}</strong>
                                    </td>
                                    <td>${{ number_format($category->total, 2) }}</td>
                                    <td>{{ $category->count ?? 0 }}</td>
                                    <td>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-info" style="width: {{ $expenses->sum('amount') > 0 ? ($category->total / $expenses->sum('amount') * 100) : 0 }}%"></div>
                                        </div>
                                        <small>{{ number_format($expenses->sum('amount') > 0 ? ($category->total / $expenses->sum('amount') * 100) : 0, 1) }}%</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No expense categories found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Vendors</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Total Spent</th>
                                    <th>Transactions</th>
                                    <th>Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVendors as $vendor)
                                <tr>
                                    <td>
                                        <strong>{{ $vendor->vendor_name }}</strong>
                                    </td>
                                    <td>${{ number_format($vendor->total, 2) }}</td>
                                    <td>{{ $vendor->count ?? 0 }}</td>
                                    <td>${{ number_format($vendor->total / ($vendor->count ?? 1), 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No vendor data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Expenses Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Expenses</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addExpenseModal">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="expensesTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Investment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                <tr>
                                    <td>
                                        <strong>{{ $expense->expense_title }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($expense->expense_description, 50) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $expense->expense_category == 'license' ? 'primary' : ($expense->expense_category == 'advertising' ? 'info' : ($expense->expense_category == 'salary' ? 'success' : 'warning')) }}">
                                            {{ ucfirst($expense->expense_category) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ $expense->vendor_name ?? 'N/A' }}</td>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $expense->status == 'approved' ? 'success' : ($expense->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($expense->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($expense->investment)
                                            <span class="badge badge-info">{{ $expense->investment->investor_name }}</span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewExpenseModal{{ $expense->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($expense->status == 'pending')
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveExpenseModal{{ $expense->id }}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editExpenseModal{{ $expense->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No expenses found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Insights -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Expense Insights</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-chart-pie"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Highest Category</span>
                            <span class="info-box-number">
                                {{ $expensesByCategory->sortByDesc('total')->first()->expense_category ?? 'N/A' }}
                            </span>
                            <span class="info-box-text">
                                ${{ $expensesByCategory->sortByDesc('total')->first()->total ?? '0.00' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Month Expenses</span>
                            <span class="info-box-number">
                                ${{ number_format($monthlyExpenseTrend->where('month', now()->format('Y-m'))->first()->total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Top Vendor</span>
                            <span class="info-box-number">
                                {{ $topVendors->first()->vendor_name ?? 'N/A' }}
                            </span>
                            <span class="info-box-text">
                                ${{ number_format($topVendors->first()->total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Expense Growth Analysis</h3>
                </div>
                <div class="card-body">
                    <canvas id="expenseGrowthChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('business.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expense Title</label>
                                <input type="text" name="expense_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expense Category</label>
                                <select name="expense_category" class="form-control" required>
                                    <option value="license">License</option>
                                    <option value="advertising">Advertising</option>
                                    <option value="salary">Salary</option>
                                    <option value="development">Development</option>
                                    <option value="office">Office</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="legal">Legal</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor Name</label>
                                <input type="text" name="vendor_name" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="expense_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Receipt File</label>
                        <input type="file" name="receipt_file" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        margin-bottom: 20px;
    }
    .card {
        margin-bottom: 20px;
    }
    .info-box {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#expensesTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[4, "desc"]]
    });

    // Expense Trend Chart
    const trendCtx = document.getElementById('expenseTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyExpenseTrend->pluck('month')),
            datasets: [{
                label: 'Expenses',
                data: @json($monthlyExpenseTrend->pluck('total')),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Expense Category Chart
    const categoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($expensesByCategory->pluck('expense_category')),
            datasets: [{
                data: @json($expensesByCategory->pluck('total')),
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d',
                    '#17a2b8', '#fd7e14', '#6f42c1', '#e83e8c', '#20c997'
                ],
                borderWidth: 2
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

    // Expense Growth Chart
    const growthCtx = document.getElementById('expenseGrowthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyExpenseTrend->pluck('month')),
            datasets: [{
                label: 'Expense Growth',
                data: @json($monthlyExpenseTrend->pluck('total')),
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>
@stop 