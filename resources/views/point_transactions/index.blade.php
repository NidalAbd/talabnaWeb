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
                                <span class="info-box-text">{{ __('point_transactions\index.total_transactions') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('point_transactions\index.points_transferred') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-week"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('point_transactions\index.this_week') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-user-friends"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('point_transactions\index.active_users') }}</span>
                                <span class="info-box-number">{{ $pointTransactions->field</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="card card-outline card-info collapsed-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter mr-1"></i>
                            Filter Transactions
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
                                        <label>{{ __('point_transactions\index.from_date_') }}</label>
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
                                        <label>{{ __('point_transactions\index.to_date_') }}</label>
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
                                        <label>{{ __('point_transactions\index.transaction_type_') }}</label>
                                        <select class="form-control">
                                            <option value="">{{ __('point_transactions\index.all_types') }}</option>
                                            <option value="purchase">{{ __('point_transactions\index.purchase') }}</option>
                                            <option value="transfer">{{ __('point_transactions\index.transfer') }}</option>
                                            <option value="reward">{{ __('point_transactions\index.reward') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('point_transactions\index.user_') }}</label>
                                        <select class="form-control select2">
                                            <option value="">{{ __('point_transactions\index.all_users') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i> Apply Filters
                                    </button>
                                    <button type="reset" class="btn btn-default">
                                        <i class="fas fa-redo mr-1"></i> Reset
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
                            Transaction History
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
                                    <th style="width: 5%">{{ __('point_transactions\index.id') }}</th>
                                    <th style="width: 20%">{{ __('point_transactions\index.from_user') }}</th>
                                    <th style="width: 20%">{{ __('point_transactions\index.to_user') }}</th>
                                    <th style="width: 10%">{{ __('point_transactions\index.points') }}</th>
                                    <th style="width: 15%">{{ __('point_transactions\index.type') }}</th>
                                    <th style="width: 20%">{{ __('point_transactions\index.created_at') }}</th>
                                    <th style="width: 10%">{{ __('point_transactions\index.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($pointTransactions) && count($pointTransactions) > 0)
                                    @foreach ($pointTransactions as $pointTransaction)
                                        <tr>
                                            <td><span class="badge badge-secondary">{{ $pointTransaction->field</span></td>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name">{{ $pointTransaction->field</span>
                                                    @if($pointTransaction->fromUser)
                                                        <small class="d-block text-muted">ID: {{ $pointTransaction->fromUser->id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name">{{ $pointTransaction->field</span>
                                                    @if($pointTransaction->toUser)
                                                        <small class="d-block text-muted">ID: {{ $pointTransaction->toUser->id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-success badge-pill px-3">{{ $pointTransaction->field</span>
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
                                                <span class="badge badge-{{ $typeClass }}">{{ ucfirst($pointTransaction->field</span>
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
                    <h5 class="modal-title" id="exportModalLabel">{{ __('point_transactions\index.export_transactions') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('point_transactions\index._times_') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('point_transactions\index.export_format') }}</label>
                        <select class="form-control">
                            <option value="csv">{{ __('point_transactions\index.csv') }}</option>
                            <option value="excel">{{ __('point_transactions\index.excel') }}</option>
                            <option value="pdf">{{ __('point_transactions\index.pdf') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('point_transactions\index.date_range') }}</label>
                        <select class="form-control">
                            <option value="all">{{ __('point_transactions\index.all_time') }}</option>
                            <option value="today">{{ __('point_transactions\index.today') }}</option>
                            <option value="week">{{ __('point_transactions\index.this_week') }}</option>
                            <option value="month">{{ __('point_transactions\index.this_month') }}</option>
                            <option value="year">{{ __('point_transactions\index.this_year') }}</option>
                            <option value="custom">{{ __('point_transactions\index.custom_range') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('point_transactions\index.cancel') }}</button>
                    <button type="button" class="btn btn-primary">{{ __('point_transactions\index.export') }}</button>
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

                if (confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
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
