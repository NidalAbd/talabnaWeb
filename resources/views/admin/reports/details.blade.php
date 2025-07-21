@extends('adminlte::page')

@section('title', 'Report Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left"></i>
            </a>
            <i class="fas fa-flag text-danger mr-2"></i> Report Details
        </h1>
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#takeActionModal">
            <i class="fas fa-gavel mr-1"></i> Take Action
        </button>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Reported Item Info -->
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-1"></i>
                            Reported {{ ucfirst($type) }} Information
                        </h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            @php
                                $isUser = $type === 'user';
                                $photo = $reportable->photos->first();
                            @endphp

                            @if ($photo)
                                <img class="{{ $isUser ? 'profile-user-img img-fluid img-circle' : 'img-fluid img-thumbnail' }}"
                                     src="{{ asset($photo->src) }}"
                                     alt="{{ $isUser ? $reportable->name : $reportable->title }}"
                                     style="{{ $isUser ? 'width: 100px; height: 100px;' : 'max-height: 150px;' }}">
                            @else
                                <div class="bg-secondary d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width: {{ $isUser ? '100px' : '150px' }}; height: {{ $isUser ? '100px' : '150px' }}; border-radius: {{ $isUser ? '50%' : '3px' }}">
                                    <i class="fas fa-{{ $isUser ? 'user' : 'file-alt' }} fa-3x text-white"></i>
                                </div>
                            @endif
                        </div>

                        <h3 class="profile-username text-center">
                            {{ $isUser ? $reportable->name : $reportable->title }}
                        </h3>

                        <p class="text-muted text-center">
                            <span class="badge badge-{{ $isUser ? 'info' : 'warning' }} px-2 py-1">
                                <i class="fas fa-{{ $isUser ? 'user' : 'file-alt' }} mr-1"></i>
                                {{ $isUser ? 'User Account' : 'Service Post' }}
                            </span>
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>{{ __('admin\reports\details.id') }}</b> <a class="float-right">{{ $reportable->field</a>
                            </li>
                            @if ($isUser)
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.email') }}</b> <a class="float-right">{{ $reportable->field</a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.phone') }}</b> <a class="float-right">{{ $reportable->field</a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.joined') }}</b> <a class="float-right">{{ $reportable->field</a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.status') }}</b>
                                    <a class="float-right">
                                        @if ($reportable->is_active === 'banned')
                                            <span class="badge badge-danger">{{ __('admin\reports\details.banned') }}</span>
                                        @elseif ($reportable->is_active === 'inactive')
                                            <span class="badge badge-warning">{{ __('admin\reports\details.inactive') }}</span>
                                        @else
                                            <span class="badge badge-success">{{ __('admin\reports\details.active') }}</span>
                                        @endif
                                    </a>
                                </li>
                            @else
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.owner') }}</b>
                                    <a class="float-right">
                                        {{ $reportable->user->name ?? 'Unknown' }}
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.category') }}</b> <a class="float-right">{{ $reportable->field</a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.created') }}</b> <a class="float-right">{{ $reportable->field</a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ __('admin\reports\details.status') }}</b>
                                    <a class="float-right">
                                        @if ($reportable->state === 'published')
                                            <span class="badge badge-success">{{ __('admin\reports\details.published') }}</span>
                                        @elseif ($reportable->state === 'archive')
                                            <span class="badge badge-warning">{{ __('admin\reports\details.archived') }}</span>
                                        @elseif ($reportable->state === 'not published')
                                            <span class="badge badge-secondary">{{ __('admin\reports\details.not_published') }}</span>
                                        @elseif ($reportable->state === 'rejected')
                                            <span class="badge badge-danger">{{ __('admin\reports\details.rejected') }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <div class="btn-group w-100">
                            <a href="{{ route($isUser ? 'users.show' : 'service_posts.show', $reportable->id) }}" class="btn btn-primary">
                                <i class="fas fa-eye mr-1"></i> View {{ ucfirst($type) }}
                            </a>
                            @if ($isUser && $reportable->is_active !== 'banned')
                                <button type="button" class="btn btn-warning suspend-btn" data-toggle="modal" data-target="#suspendModal">
                                    <i class="fas fa-ban mr-1"></i> Ban User
                                </button>
                            @elseif ($isUser && $reportable->is_active === 'banned')
                                <form action="{{ route('user.unsuspend', $reportable->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to unban this user?')">
                                        <i class="fas fa-check-circle mr-1"></i> Unban User
                                    </button>
                                </form>
                            @elseif (!$isUser && $reportable->state !== 'archive')
                                <button type="button" class="btn btn-warning suspend-btn" data-toggle="modal" data-target="#suspendModal">
                                    <i class="fas fa-ban mr-1"></i> Archive Post
                                </button>
                            @elseif (!$isUser && $reportable->state === 'archive')
                                <form action="{{ route('post.unsuspend', $reportable->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to republish this post?')">
                                        <i class="fas fa-check-circle mr-1"></i> Republish
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports List -->
            <div class="col-md-8">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            All Reports ({{ $reports->total() }})
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr class="bg-light">
                                    <th style="width: 25%">{{ __('admin\reports\details.reporter') }}</th>
                                    <th style="width: 45%">{{ __('admin\reports\details.reason') }}</th>
                                    <th style="width: 15%">{{ __('admin\reports\details.date') }}</th>
                                    <th style="width: 15%">{{ __('admin\reports\details.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($report->reporter && $report->reporter->photos->first())
                                                    <img src="{{ asset($report->reporter->photos->first()->src) }}"
                                                         alt="{{ $report->reporter->name }}"
                                                         class="img-circle mr-2"
                                                         style="width: 35px; height: 35px;">
                                                @else
                                                    <div class="bg-secondary mr-2 d-flex align-items-center justify-content-center"
                                                         style="width: 35px; height: 35px; border-radius: 50%;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold">
                                                        {{ $report->reporter->name ?? 'Unknown User' }}
                                                    </div>
                                                    <small class="text-muted">ID: {{ $report->reporter_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="report-reason">
                                                {{ $report->reason }}
                                            </div>
                                        </td>
                                        <td>
                                            <div data-toggle="tooltip" title="{{ $report->created_at }}">
                                                {{ $report->created_at->format('M d, Y') }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $report->created_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <form action="{{ route('reports.destroy', $report->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this report?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="fas fa-search text-secondary display-4"></i>
                                                <p class="mt-3 mb-0">No reports found for this {{ $type }}.</p>
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
        </div>
    </div>

    <!-- Take Action Modal -->
    <div class="modal fade" id="takeActionModal" tabindex="-1" role="dialog" aria-labelledby="takeActionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="takeActionModalLabel">
                        <i class="fas fa-gavel mr-1"></i> Take Action on {{ ucfirst($type) }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('admin\reports\details._times_') }}</span>
                    </button>
                </div>
                <form action="{{ route('reports.handle-reported', [$type, $reportable->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            You are about to take action on: <strong>{{ $isUser ? $reportable->field</strong>
                        </div>

                        <div class="form-group">
                            <label for="action">{{ __('admin\reports\details.select_action') }}</label>
                            <select class="form-control" id="action" name="action" required>
                                <option value="">{{ __('admin\reports\details._select_action_') }}</option>
                                <option value="warning">{{ __('admin\reports\details.send_warning') }}</option>
                                <option value="suspend">{{ $isUser ? 'Ban User' : 'Archive Post' }}</option>
                                <option value="delete">Delete {{ ucfirst($type) }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reason">{{ __('admin\reports\details.reason_for_action') }}</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required
                                      placeholder="Explain why you're taking this action..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning action-btn">
                            <i class="fas fa-gavel mr-1"></i> Take Action
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" role="dialog" aria-labelledby="suspendModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="suspendModalLabel">
                        <i class="fas fa-ban mr-1"></i> {{ $isUser ? 'Ban User' : 'Archive Post' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('admin\reports\details._times_') }}</span>
                    </button>
                </div>
                <form action="{{ route('reports.handle-reported', [$type, $reportable->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="suspend">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            You are about to {{ $isUser ? 'ban' : 'archive' }}: <strong>{{ $isUser ? $reportable->field</strong>
                        </div>

                        <div class="form-group">
                            <label for="suspend-reason">Reason for {{ $isUser ? 'Banning' : 'Archiving' }}</label>
                            <textarea class="form-control" id="suspend-reason" name="reason" rows="3" required
                                      placeholder="Explain why you're {{ $isUser ? 'banning' : 'archiving' }} this {{ $type }}..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin\reports\details.cancel') }}</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-ban mr-1"></i> {{ $isUser ? 'Ban User' : 'Archive Post' }}
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
        .report-reason {
            white-space: normal;
            word-break: break-word;
        }
        .profile-username {
            font-size: 1.5rem;
            word-break: break-word;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Change action button text based on selected action
            $('#action').on('change', function() {
                var action = $(this).val();
                var buttonText = 'Take Action';
                var isUser = "{{ $isUser }}" === "1";

                switch(action) {
                    case 'warning':
                        buttonText = 'Send Warning';
                        break;
                    case 'suspend':
                        buttonText = isUser ? 'Ban User' : 'Archive Post';
                        break;
                    case 'delete':
                        buttonText = 'Delete {{ ucfirst($type) }}';
                        break;
                }

                $('.action-btn').html('<i class="fas fa-gavel mr-1"></i> ' + buttonText);
            });
        });
    </script>
@stop
