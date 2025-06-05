@extends('adminlte::page')

@section('title', 'Marketing Notification History')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-history text-primary mr-2"></i> Marketing Notification History</h1>
        <div>
            <a href="{{ route('admin.notifications.marketing.index') }}" class="btn btn-primary">
                <i class="fas fa-paper-plane mr-1"></i> Send New Notification
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Dashboard Summary Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow">
                    <span class="info-box-icon bg-primary"><i class="fas fa-paper-plane"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Campaigns</span>
                        <span class="info-box-number">{{ number_format($notificationLogs->total()) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Recipients</span>
                        <span class="info-box-number">{{ number_format($notificationLogs->sum('total_recipients')) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Successful Deliveries</span>
                        <span class="info-box-number">{{ number_format($notificationLogs->sum('successful_count')) }}</span>
                        <div class="progress">
                            @php
                                $totalRecipients = $notificationLogs->sum('total_recipients');
                                $successRate = $totalRecipients > 0 ? ($notificationLogs->sum('successful_count') / $totalRecipients) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ round($successRate) }}% success rate
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Failed Deliveries</span>
                        <span class="info-box-number">{{ number_format($notificationLogs->sum('failed_count')) }}</span>
                        <div class="progress">
                            @php
                                $failRate = $totalRecipients > 0 ? ($notificationLogs->sum('failed_count') / $totalRecipients) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-danger" style="width: {{ $failRate }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ round($failRate) }}% failure rate
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Notification Campaign History
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <div class="input-group input-group-sm float-right" style="width: 250px;">
                        <form action="{{ route('admin.notifications.marketing.history') }}" method="GET" class="d-flex w-100">
                            <input type="text" class="form-control" name="search" placeholder="Search...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Sent By</th>
                            <th>Recipients</th>
                            <th>Success Rate</th>
                            <th>Date Sent</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($notificationLogs) > 0)
                            @foreach($notificationLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $log->title }}">
                                                {{ $log->title }}
                                            </span>
                                    </td>
                                    <td>
                                        @php
                                            $admin = App\Models\User::find($log->admin_id);
                                        @endphp

                                        <div class="user-info">
                                            @if($admin && $admin->photos && $admin->photos->count() > 0)
                                                @php
                                                    $photo = $admin->photos->first();
                                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img class="img-circle img-size-32 mr-2" src="{{ $imgSrc }}" alt="{{ $admin->user_name }}">
                                            @else
                                                <img class="img-circle img-size-32 mr-2" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="Admin Image">
                                            @endif
                                            <span>{{ $admin->user_name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($log->total_recipients) }}</td>
                                    <td>
                                        @php
                                            $successRate = $log->total_recipients > 0 ? ($log->successful_count / $log->total_recipients) * 100 : 0;
                                            $rateColor = $successRate > 90 ? 'success' : ($successRate > 70 ? 'info' : ($successRate > 50 ? 'warning' : 'danger'));
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $rateColor }}" role="progressbar"
                                                 style="width: {{ $successRate }}%;"
                                                 aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($successRate) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal{{ $log->id }}">
                                            <i class="fas fa-eye"></i> Details
                                        </button>

                                        <!-- Details Modal -->
                                        <div class="modal fade" id="detailsModal{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel{{ $log->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="detailsModalLabel{{ $log->id }}">
                                                            <i class="fas fa-bell mr-2"></i>Notification Details
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th style="width: 30%">Title</th>
                                                                <td>{{ $log->title }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Message</th>
                                                                <td>{{ $log->body }}</td>
                                                            </tr>
                                                            @if($log->image_url)
                                                                <tr>
                                                                    <th>Image</th>
                                                                    <td>
                                                                        <img src="{{ $log->image_url }}" alt="Notification Image" class="img-fluid rounded" style="max-height: 200px">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @if($log->deep_link)
                                                                <tr>
                                                                    <th>Deep Link</th>
                                                                    <td><code>{{ $log->deep_link }}</code></td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <th>Sent At</th>
                                                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Recipients</th>
                                                                <td>{{ number_format($log->total_recipients) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Successful</th>
                                                                <td>
                                                                        <span class="badge badge-success">
                                                                            {{ number_format($log->successful_count) }}
                                                                        </span>
                                                                    ({{ round($successRate) }}%)
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Failed</th>
                                                                <td>
                                                                        <span class="badge badge-danger">
                                                                            {{ number_format($log->failed_count) }}
                                                                        </span>
                                                                    ({{ round(100 - $successRate) }}%)
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="button" class="btn btn-primary" onclick="resendNotification({{ $log->id }})">
                                                            <i class="fas fa-redo mr-1"></i> Resend This Notification
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No notification campaigns found
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
                    {{ $notificationLogs->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* User info styling */
        .user-info {
            display: flex;
            align-items: center;
        }

        .img-circle {
            object-fit: cover;
            width: 32px;
            height: 32px;
        }

        /* Progress bar styling */
        .progress {
            height: 4px;
            margin: 5px 0;
            border-radius: 2px;
        }

        .table .progress {
            height: 20px;
            margin: 0;
        }

        .info-box .progress-bar {
            height: 4px;
        }

        /* Card styling */
        .card {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .card .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize DataTable for better filtering/sorting if needed
            // $('.table').DataTable({
            //     "paging": false,
            //     "lengthChange": false,
            //     "searching": false,
            //     "ordering": true,
            //     "info": false,
            //     "autoWidth": false,
            //     "responsive": true,
            // });
        });

        function resendNotification(id) {
            if (confirm('Are you sure you want to resend this notification to all recipients?')) {
                // Implement the resend functionality
                window.location.href = "{{ route('admin.notifications.marketing.index') }}?resend=" + id;
            }
        }
    </script>
@stop
