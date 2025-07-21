@extends('adminlte::page')

@section('title', 'Expense Approvals')

@section('content_header')
    <h1>Expense Approval Management</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingExpenses->count() }}</h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $approvedExpenses->count() }}</h3>
                    <p>Approved This Month</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $rejectedExpenses->count() }}</h3>
                    <p>Rejected This Month</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($pendingExpenses->sum('amount'), 2) }}</h3>
                    <p>Pending Amount</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Expense Approvals</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Category</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Submitted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_title }}</td>
                                    <td>${{ number_format($expense->amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($expense->expense_category) }}</span>
                                    </td>
                                    <td>{{ $expense->vendor_name }}</td>
                                    <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expense->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success approve-expense" data-id="{{ $expense->id }}">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger reject-expense" data-id="{{ $expense->id }}">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        <button class="btn btn-sm btn-info view-expense" data-id="{{ $expense->id }}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
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

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recently Approved Expenses</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Approved By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_title }}</td>
                                    <td>${{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ $expense->approver ? $expense->approver->name : 'System' }}</td>
                                    <td>{{ $expense->approved_at ? $expense->approved_at->format('M d, Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recently Rejected Expenses</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Rejected By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rejectedExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_title }}</td>
                                    <td>${{ number_format($expense->amount, 2) }}</td>
                                    <td>{{ $expense->approver ? $expense->approver->name : 'System' }}</td>
                                    <td>{{ $expense->approved_at ? $expense->approved_at->format('M d, Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Workflow -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Approval Workflow</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="bg-warning p-3 rounded">
                                    <i class="fas fa-clock fa-2x text-white"></i>
                                    <h5 class="mt-2 text-white">Pending</h5>
                                    <p class="text-white">{{ $pendingExpenses->count() }} expenses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="bg-success p-3 rounded">
                                    <i class="fas fa-check-circle fa-2x text-white"></i>
                                    <h5 class="mt-2 text-white">Approved</h5>
                                    <p class="text-white">{{ $approvedExpenses->count() }} expenses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="bg-danger p-3 rounded">
                                    <i class="fas fa-times-circle fa-2x text-white"></i>
                                    <h5 class="mt-2 text-white">Rejected</h5>
                                    <p class="text-white">{{ $rejectedExpenses->count() }} expenses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="bg-info p-3 rounded">
                                    <i class="fas fa-chart-line fa-2x text-white"></i>
                                    <h5 class="mt-2 text-white">Total</h5>
                                    <p class="text-white">{{ $pendingExpenses->count() + $approvedExpenses->count() + $rejectedExpenses->count() }} expenses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .icon {
        color: rgba(0,0,0,.15);
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Approve expense
    $('.approve-expense').click(function() {
        const expenseId = $(this).data('id');
        if (confirm('Are you sure you want to approve this expense?')) {
            $.ajax({
                url: `/business/expenses/${expenseId}/approve`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error approving expense');
                }
            });
        }
    });

    // Reject expense
    $('.reject-expense').click(function() {
        const expenseId = $(this).data('id');
        const reason = prompt('Please provide a reason for rejection:');
        if (reason !== null) {
            $.ajax({
                url: `/business/expenses/${expenseId}/reject`,
                method: 'POST',
                data: { reason: reason },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error rejecting expense');
                }
            });
        }
    });

    // View expense details
    $('.view-expense').click(function() {
        const expenseId = $(this).data('id');
        // Implement view expense functionality
        alert('View expense details for ID: ' + expenseId);
    });
});
</script>
@stop 