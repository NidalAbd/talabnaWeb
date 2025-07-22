@extends('adminlte::page')

@section('title', 'Expense Management')

@section('content_header')
    <h1>{{('admin\accountant\expenses.expense_management') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{('admin\accountant\expenses.filters') }}</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accountant.expenses') }}" class="row">
                <div class="col-md-2">
                    <label for="status">{{('admin\accountant\expenses.status') }}</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">{{('admin\accountant\expenses.all_statuses') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category">{{('admin\accountant\expenses.category') }}</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">{{('admin\accountant\expenses.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from">{{('admin\accountant\expenses.from_date') }}</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to">{{('admin\accountant\expenses.to_date') }}</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label>{{('admin\accountant\expenses._nbsp_') }}</label>
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
            <h3 class="card-title">{{('admin\accountant\expenses.expenses') }}</h3>
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
                            <th>{{('admin\accountant\expenses.title') }}</th>
                            <th>{{('admin\accountant\expenses.amount') }}</th>
                            <th>{{('admin\accountant\expenses.category') }}</th>
                            <th>{{('admin\accountant\expenses.vendor') }}</th>
                            <th>{{('admin\accountant\expenses.status') }}</th>
                            <th>{{('admin\accountant\expenses.date') }}</th>
                            <th>{{('admin\accountant\expenses.approved_by') }}</th>
                            <th>{{('admin\accountant\expenses.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->title }}</td>
                            <td>${{ number_format($expense->amount, 2) }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($expense->category) }}</span>
                            </td>
                            <td>{{ $expense->vendor }}</td>
                            <td>
                                <span class="badge badge-{{ $expense->status === 'approved' ? 'success' : ($expense->status === 'pending' ? 'warning' : ($expense->status === 'rejected' ? 'danger' : 'secondary')) }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td>{{ $expense->date }}</td>
                            <td>
                                @if($expense->approver)
                                    {{ $expense->approver->name }}
                                    <br><small>{{ $expense->approved_at }}</small>
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
                <h5 class="modal-title">{{('admin\accountant\expenses.add_new_expense') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>{{('admin\accountant\expenses._times_') }}</span>
                </button>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_title">{{('admin\accountant\expenses.expense_title_') }}</label>
                                <input type="text" class="form-control" id="expense_title" name="expense_title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">{{('admin\accountant\expenses.amount_') }}</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_category">{{('admin\accountant\expenses.category_') }}</label>
                                <select class="form-control" id="expense_category" name="expense_category" required>
                                    <option value="">{{('admin\accountant\expenses.select_category') }}</option>
                                    <option value="advertising">{{('admin\accountant\expenses.advertising') }}</option>
                                    <option value="development">{{('admin\accountant\expenses.development') }}</option>
                                    <option value="license">{{('admin\accountant\expenses.license') }}</option>
                                    <option value="office">{{('admin\accountant\expenses.office') }}</option>
                                    <option value="marketing">{{('admin\accountant\expenses.marketing') }}</option>
                                    <option value="legal">{{('admin\accountant\expenses.legal') }}</option>
                                    <option value="salary">{{('admin\accountant\expenses.salary') }}</option>
                                    <option value="other">{{('admin\accountant\expenses.other') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_date">{{('admin\accountant\expenses.date_') }}</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendor_name">{{('admin\accountant\expenses.vendor') }}</label>
                                <input type="text" class="form-control" id="vendor_name" name="vendor_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method">{{('admin\accountant\expenses.payment_method') }}</label>
                                <select class="form-control" id="payment_method" name="payment_method">
                                    <option value="bank_transfer">{{('admin\accountant\expenses.bank_transfer') }}</option>
                                    <option value="credit_card">{{('admin\accountant\expenses.credit_card') }}</option>
                                    <option value="check">{{('admin\accountant\expenses.check') }}</option>
                                    <option value="cash">{{('admin\accountant\expenses.cash') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">{{('admin\accountant\expenses.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{('admin\accountant\expenses.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{('admin\accountant\expenses.add_expense') }}</button>
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






