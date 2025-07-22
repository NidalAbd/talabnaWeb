@extends('adminlte::page')

@section('title', 'Transaction Points')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exchange-alt text-info mr-2"></i> Point Transactions</h1>
        <div class="d-flex">
            <a href="{{ route('point_transactions.fix') }}" class="btn btn-warning mr-2">
                <i class="fas fa-wrench mr-1"></i> Fix Transaction Records
            </a>
            <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-file-export mr-1"></i> Export Data
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Transaction Summary Boxes -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-sync-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Total Transactions') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->total() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Points Transferred') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->sum('point') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-week"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('This Week') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('point') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-user-friends"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Active Users') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->distinct('from_user_id')->count('from_user_id') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="card card-outline card-info collapsed-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-1"></i>
                            {{ __('Filter Transactions') }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="#" method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('From Date') }}</label>
                                        <div class="input-group date" id="from-date" data-target-input="nearest">
                                            <input type="text" class="form-control datetimepicker-input" data-target="#from-date" placeholder="From date"/>
                                            <div class="input-group-append" data-target="#from-date" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('To Date') }}</label>
                                        <div class="input-group date" id="to-date" data-target-input="nearest">
                                            <input type="text" class="form-control datetimepicker-input" data-target="#to-date" placeholder="To date"/>
                                            <div class="input-group-append" data-target="#to-date" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('Transaction Type') }}</label>
                                        <select class="form-control">
                                            <option value="">{{ __('All Types') }}</option>
                                            <option value="purchase">{{ __('Purchase') }}</option>
                                            <option value="transfer">{{ __('Transfer') }}</option>
                                            <option value="reward">{{ __('Reward') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('User') }}</label>
                                        <select class="form-control select2">
                                            <option value="">{{ __('All Users') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> {{ __('Apply Filters') }}
                                    </button>
                                    <button type="reset" class="btn btn-default">
                                        <i class="fas fa-redo mr-1"></i> {{ __('Reset') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transactions Table Card -->
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            {{ __('Transaction History') }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="table_search" class="form-control float-right" placeholder="Search transactions...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%">{{ __('ID') }}</th>
                                    <th style="width: 20%">{{ __('From User') }}</th>
                                    <th style="width: 20%">{{ __('To User') }}</th>
                                    <th style="width: 10%">{{ __('Points') }}</th>
                                    <th style="width: 15%">{{ __('Type') }}</th>
                                    <th style="width: 20%">{{ __('Created At') }}</th>
                                    <th style="width: 10%">{{ __('Actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($pointTransactions) && count($pointTransactions) > 0)
                                    @foreach ($pointTransactions as $pointTransaction)
                                        <tr>
                                            <td><span class="badge badge-secondary">{{ $pointTransaction->id }}</span></td>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name">{{ $pointTransaction->fromUser->name ?? 'N/A' }}</span>
                                                    @if($pointTransaction->fromUser)
                                                        <small class="d-block text-muted">ID: {{ $pointTransaction->fromUser->id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name">{{ $pointTransaction->toUser->name ?? 'N/A' }}</span>
                                                    @if($pointTransaction->toUser)
                                                        <small class="d-block text-muted">ID: {{ $pointTransaction->toUser->id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-success badge-pill px-3">{{ $pointTransaction->point ?? 0 }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $typeClass = [
                                                        'purchase' => 'primary',
                                                        'transfer' => 'info',
                                                        'reward' => 'warning',
                                                        'refund' => 'danger'
                                                    ][$pointTransaction->type] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $typeClass }}">{{ ucfirst($pointTransaction->type) }}</span>
                                            </td>
                                            <td>
                                                <i class="far fa-clock mr-1"></i>
                                                {{ $pointTransaction->created_at->format('M d, Y g:i A') }}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form action="{{ route('point_transactions.destroy', $pointTransaction) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-toggle="tooltip" title="Delete Transaction">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="alert alert-info m-0">
                                                <i class="fas fa-info-circle mr-1"></i> {{ __('No records found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            {{ $pointTransactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">{{ __('Export Transactions') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('times') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('Export Format') }}</label>
                        <select class="form-control">
                            <option value="csv">{{ __('CSV') }}</option>
                            <option value="excel">{{ __('Excel') }}</option>
                            <option value="pdf">{{ __('PDF') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Date Range') }}</label>
                        <select class="form-control">
                            <option value="all">{{ __('All Time') }}</option>
                            <option value="today">{{ __('Today') }}</option>
                            <option value="week">{{ __('This Week') }}</option>
                            <option value="month">{{ __('This Month') }}</option>
                            <option value="year">{{ __('This Year') }}</option>
                            <option value="custom">{{ __('Custom Range') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary">{{ __('Export') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .user-info .user-name {
            font-weight: 600;
        }
        .badge-pill {
            padding-right: 0.8em;
            padding-left: 0.8em;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize datepickers if they exist
            if ($.fn.datetimepicker) {
                $('#from-date, #to-date').datetimepicker({
                    format: 'L'
                });
            }

            // Initialize select2 if it exists
            if ($.fn.select2) {
                $('.select2').select2();
            }

            // Handle delete button clicks with confirmation
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();

                if (confirm('{{ __('Are you sure you want to delete this transaction? This action cannot be undone.') }}')) {
                    // Submit the parent form when confirmed
                    $(this).closest('form.delete-form').submit();
                }
            });

            // Debug: Log when a form is submitted
            $('.delete-form').on('submit', function() {
                console.log('Delete form submitted for transaction');
            });
        });
        $(function() {
            //Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            //Initialize datepickers if they exist
            if ($.fn.datetimepicker) {
                $('#from-date, #to-date').datetimepicker({
                    format: 'L'
                });
            }

            //Initialize select2 if it exists
            if ($.fn.select2) {
                $('.select2').select2();
            }
        });
    </script>
@stop







