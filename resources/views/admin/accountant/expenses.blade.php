@extends('adminlte::page')

@section('title', 'Expense Management')

@section('content_header')
    <h1>Expense Management</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accountant.expenses') }}" class="row">
                <div class="col-md-2">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('accountant.expenses') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Expenses</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addExpenseModal">
                    <i class="fas fa-plus"></i> Add Expense
                </button>
            </div>
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
                            <th>Status</th>
                            <th>Date</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_title }}</td>
                            <td>${{ number_format($expense->amount, 2) }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($expense->expense_category) }}</span>
                            </td>
                            <td>{{ $expense->vendor_name }}</td>
                            <td>
                                <span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'pending' ? 'warning' : ($expense->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td>
                                @if($expense->approver)
                                    {{ $expense->approver->name }}
                                    <br><small>{{ $expense->approved_at ? $expense->approved_at->format('M d, Y H:i') : '' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($expense->status === 'pending')
                                    <button class="btn btn-sm btn-success approve-expense" data-id="{{ $expense->id }}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-expense" data-id="{{ $expense->id }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                @endif
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
        <div class="card-footer">
            {{ $expenses->links() }}
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_title">Expense Title *</label>
                                <input type="text" class="form-control" id="expense_title" name="expense_title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_category">Category *</label>
                                <select class="form-control" id="expense_category" name="expense_category" required>
                                    <option value="">Select Category</option>
                                    <option value="advertising">Advertising</option>
                                    <option value="development">Development</option>
                                    <option value="license">License</option>
                                    <option value="office">Office</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="legal">Legal</option>
                                    <option value="salary">Salary</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_date">Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendor_name">Vendor</label>
                                <input type="text" class="form-control" id="vendor_name" name="vendor_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="check">Check</option>
                                    <option value="cash">Cash</option>
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
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge {
        font-size: 0.8em;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Set default date to today
    $('#expense_date').val(new Date().toISOString().split('T')[0]);

    // Expense approval handlers
    $('.approve-expense').click(function() {
        const expenseId = $(this).data('id');
        if (confirm('Are you sure you want to approve this expense?')) {
            $.ajax({
                url: `/accountant-expenses/${expenseId}/approve`,
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

    $('.reject-expense').click(function() {
        const expenseId = $(this).data('id');
        if (confirm('Are you sure you want to reject this expense?')) {
            $.ajax({
                url: `/accountant-expenses/${expenseId}/reject`,
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
                    alert('Error rejecting expense');
                }
            });
        }
    });

    // Add expense form handler
    $('#addExpenseForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("accountant.expenses") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#addExpenseModal').modal('hide');
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
</script>
@stop 