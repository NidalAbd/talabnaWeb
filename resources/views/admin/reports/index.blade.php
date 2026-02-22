@extends('adminlte::page')

@section('title', 'Reports Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-flag text-danger mr-2"></i> Reports Management</h1>
        <div>
            <a href="{{ route('statistics.index') }}" class="btn btn-primary">
                <i class="fas fa-chart-bar mr-1"></i> View Statistics
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Stats Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-flag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Reports</span>
                        <span class="info-box-number">{{ number_format($stats['total_reports']) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">User Reports</span>
                        <span class="info-box-number">{{ number_format($stats['user_reports']) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $stats['total_reports'] > 0 ? ($stats['user_reports'] / $stats['total_reports'] * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $stats['total_reports'] > 0 ? round(($stats['user_reports'] / $stats['total_reports'] * 100), 1) : 0 }}% of total
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Post Reports</span>
                        <span class="info-box-number">{{ number_format($stats['post_reports']) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $stats['total_reports'] > 0 ? ($stats['post_reports'] / $stats['total_reports'] * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $stats['total_reports'] > 0 ? round(($stats['post_reports'] / $stats['total_reports'] * 100), 1) : 0 }}% of total
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Review</span>
                        <span class="info-box-number">{{ number_format($stats['total_reports']) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            All reports require review
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Reports Table -->
            <div class="col-md-8">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Most Reported Items
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr class="bg-dark">
                                    <th style="width: 5%">#</th>
                                    <th style="width: 12%">Type</th>
                                    <th style="width: 28%">Item</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 15%" class="text-center">Reports</th>
                                    <th style="width: 25%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($reports as $report)
                                    @if ($report->reportable)
                                        @php
                                            $reportable = $report->reportable;
                                            $isUser = $report->reportable_type == App\Models\User::class;
                                            $type = $isUser ? 'user' : 'post';
                                            $typeLabel = $isUser ? 'User' : 'Service Post';
                                            $typeIcon = $isUser ? 'user' : 'file-alt';
                                            $typeClass = $isUser ? 'info' : 'warning';
                                            $name = $isUser ? $reportable->name : $reportable->title;

                                            // Get status based on model type
                                            if ($isUser) {
                                                if ($reportable->is_active === 'banned') {
                                                    $statusBadge = '<span class="badge badge-danger">Banned</span>';
                                                    $statusClass = 'danger';
                                                    $statusText = 'Banned';
                                                } elseif ($reportable->is_active === 'inactive') {
                                                    $statusBadge = '<span class="badge badge-warning">Inactive</span>';
                                                    $statusClass = 'warning';
                                                    $statusText = 'Inactive';
                                                } else {
                                                    $statusBadge = '<span class="badge badge-success">Active</span>';
                                                    $statusClass = 'success';
                                                    $statusText = 'Active';
                                                }
                                            } else {
                                                if ($reportable->state === 'archive') {
                                                    $statusBadge = '<span class="badge badge-warning">Archived</span>';
                                                    $statusClass = 'warning';
                                                    $statusText = 'Archived';
                                                } elseif ($reportable->state === 'not published') {
                                                    $statusBadge = '<span class="badge badge-secondary">Not Published</span>';
                                                    $statusClass = 'secondary';
                                                    $statusText = 'Not Published';
                                                } elseif ($reportable->state === 'rejected') {
                                                    $statusBadge = '<span class="badge badge-danger">Rejected</span>';
                                                    $statusClass = 'danger';
                                                    $statusText = 'Rejected';
                                                } else {
                                                    $statusBadge = '<span class="badge badge-success">Published</span>';
                                                    $statusClass = 'success';
                                                    $statusText = 'Published';
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="badge badge-dark">{{ $reportable->id }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $typeClass }} px-2 py-1">
                                                    <i class="fas fa-{{ $typeIcon }} mr-1"></i>
                                                    {{ $typeLabel }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($isUser && $reportable->photos->first())
                                                        <img src="{{ asset($reportable->photos->first()->src) }}"
                                                             alt="{{ $reportable->name }}"
                                                             class="img-circle mr-2"
                                                             style="width: 40px; height: 40px;">
                                                    @elseif (!$isUser && $reportable->photos->first())
                                                        <img src="{{ asset($reportable->photos->first()->src) }}"
                                                             alt="{{ $reportable->title }}"
                                                             class="img-thumbnail mr-2"
                                                             style="width: 40px; height: 40px;">
                                                    @else
                                                        <div class="bg-secondary mr-2 d-flex align-items-center justify-content-center"
                                                             style="width: 40px; height: 40px; border-radius: {{ $isUser ? '50%' : '3px' }}">
                                                            <i class="fas fa-{{ $typeIcon }}"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-weight-bold text-truncate" style="max-width: 250px;">
                                                            {{ $name }}
                                                        </div>
                                                        <small class="text-muted">
                                                            ID: {{ $reportable->id }} |
                                                            @if ($isUser)
                                                                Email: {{ $reportable->email }}
                                                            @else
                                                                Created: {{ $reportable->created_at->format('M d, Y') }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge badge-{{ $statusClass }} px-2 py-1" style="font-size: 0.9rem;">
                                                        @if ($statusClass === 'success')
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                        @elseif ($statusClass === 'warning')
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        @elseif ($statusClass === 'danger')
                                                            <i class="fas fa-ban mr-1"></i>
                                                        @else
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                        @endif
                                                        {{ $statusText }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-danger badge-pill px-3 py-2" style="font-size: 14px;">
                                                    {{ $report->total }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('reports.details', [$type, $reportable->id]) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye mr-1"></i> Details
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#takeActionModal"
                                                            data-type="{{ $type }}"
                                                            data-id="{{ $reportable->id }}"
                                                            data-name="{{ $name }}">
                                                        <i class="fas fa-gavel mr-1"></i> Take Action
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="fas fa-check-circle text-success display-4"></i>
                                                <p class="mt-3 mb-0">No reports found! Everything looks good.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer clearfix">
                        <div class="float-right">
                            {{ $reports->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-1"></i>
                            Recent Reports
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($stats['recent_reports'] as $recentReport)
                                @php
                                    $isUser = $recentReport->reportable_type == App\Models\User::class;
                                    $reportable = $recentReport->reportable;
                                    $name = $isUser ? ($reportable->name ?? 'Deleted User') : ($reportable->title ?? 'Deleted Post');
                                    $reporter = $recentReport->reporter->name ?? 'Unknown User';
                                @endphp
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge badge-{{ $isUser ? 'info' : 'warning' }} mr-1">
                                                <i class="fas fa-{{ $isUser ? 'user' : 'file-alt' }}"></i>
                                                {{ $isUser ? 'User' : 'Post' }}
                                            </span>
                                            <span class="font-weight-bold">{{ $name }}</span>
                                        </div>
                                        <small class="text-muted">{{ $recentReport->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Reported by:</small>
                                        <span class="ml-1">{{ $reporter }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">Reason:</small>
                                        <span class="ml-1 text-truncate d-inline-block" style="max-width: 100%;">
                                            {{ Str::limit($recentReport->reason, 50) }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">
                                    No recent reports
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    @if(count($stats['recent_reports']) > 0)
                        <div class="card-footer text-center">
                            <a href="{{ route('statistics.index') }}" class="btn btn-sm btn-default">
                                View All Activity
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Quick Help Card -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-question-circle mr-1"></i>
                            Report Handling Guide
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5>Common Actions:</h5>
                        <ul class="pl-3">
                            <li><strong>Review Details</strong>: Check all reports against an item before taking action</li>
                            <li><strong>Warning</strong>: For first-time or minor offenses</li>
                            <li><strong>Ban/Archive</strong>: For repeated or moderate violations</li>
                            <li><strong>Deletion</strong>: For severe violations or harmful content</li>
                        </ul>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Remember to document your decisions when handling reports for accountability.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Take Action Modal -->
    <div class="modal fade" id="takeActionModal" tabindex="-1" role="dialog" aria-labelledby="takeActionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="takeActionModalLabel">
                        <i class="fas fa-gavel mr-1"></i> Take Action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="takeActionForm" action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            You are about to take action on <strong id="reportedItemName"></strong>
                        </div>

                        <div class="form-group">
                            <label for="action">Select Action</label>
                            <select class="form-control" id="action" name="action" required>
                                <option value="">-- Select Action --</option>
                                <option value="warning">Send Warning</option>
                                <option value="suspend" id="suspendOption">Ban/Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Action</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required
                                      placeholder="Explain why you're taking this action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning action-btn">
                            <i class="fas fa-gavel mr-1"></i> Proceed with Action
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-pill {
            border-radius: 50rem;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .empty-state {
            padding: 20px;
            text-align: center;
        }
        .pagination {
            margin-bottom: 0;
        }
        .info-box-content .progress {
            height: 5px;
            margin: 5px 0;
        }
        .info-box-content .progress-description {
            white-space: nowrap;
            font-size: 12px;
        }
        .badge {
            font-weight: 500;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Handle action modal
            $('#takeActionModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var type = button.data('type');
                var id = button.data('id');
                var name = button.data('name');

                var modal = $(this);
                modal.find('#reportedItemName').text(name);

                // Update form action URL
                var actionUrl = "{{ url('admin/reports/handle') }}/" + type + "/" + id;
                modal.find('#takeActionForm').attr('action', actionUrl);

                // Update suspend option text based on type
                if (type === 'user') {
                    modal.find('#suspendOption').text('Ban User');
                } else {
                    modal.find('#suspendOption').text('Archive Post');
                }
            });

            // Change button text based on selected action
            $('#action').on('change', function() {
                var action = $(this).val();
                var buttonText = 'Proceed with Action';
                var suspendText = $('#suspendOption').text();

                switch(action) {
                    case 'warning':
                        buttonText = 'Send Warning';
                        break;
                    case 'suspend':
                        buttonText = suspendText;
                        break;
                    case 'delete':
                        buttonText = 'Delete Item';
                        break;
                }

                $('.modal-footer button[type="submit"]').html('<i class="fas fa-gavel mr-1"></i> ' + buttonText);
            });
        });
    </script>
@stop
