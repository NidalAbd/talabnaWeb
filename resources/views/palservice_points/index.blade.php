@extends('adminlte::page')

@section('title', 'User Points')

@section('content_header')
    @include('partials.alert')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-coins text-warning mr-2"></i> User Points</h1>
        <div>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#userSelectModal">
                <i class="fas fa-plus mr-1"></i> Add Points
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Points Summary Boxes -->
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('palservice_points\index.total_points') }}</span>
                                <span class="info-box-number">{{ $palservicePoints->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-user-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('palservice_points\index.total_users') }}</span>
                                <span class="info-box-number">{{ $palservicePoints->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('palservice_points\index.avg_points') }}</span>
                                <span class="info-box-number">{{ $palservicePoints->field</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-trophy"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('palservice_points\index.top_score') }}</span>
                                <span class="info-box-number">{{ $palservicePoints->field</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="{{ route('palservice_points.index') }}" method="GET" id="searchForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search">{{ __('palservice_points\index.search_users') }}</label>
                                        <input type="text" class="form-control" id="search" name="search"
                                               placeholder="Search by name, username, or ID"
                                               value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="min_points">{{ __('palservice_points\index.min_points') }}</label>
                                        <input type="number" class="form-control" id="min_points" name="min_points"
                                               placeholder="Minimum points"
                                               value="{{ request('min_points') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="max_points">{{ __('palservice_points\index.max_points') }}</label>
                                        <input type="number" class="form-control" id="max_points" name="max_points"
                                               placeholder="Maximum points"
                                               value="{{ request('max_points') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-group w-100">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search mr-1"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    @if(request('search') || request('min_points') || request('max_points'))
                                        <a href="{{ route('palservice_points.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times mr-1"></i> Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Main Cakkkkrd -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            User Points List
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th style="width: 10%">{{ __('palservice_points\index.id') }}</th>
                                    <th style="width: 30%">{{ __('palservice_points\index.user') }}</th>
                                    <th style="width: 20%">{{ __('palservice_points\index.role') }}</th>
                                    <th style="width: 15%">{{ __('palservice_points\index.points') }}</th>
                                    <th style="width: 25%">{{ __('palservice_points\index.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($palservicePoints) && count($palservicePoints) > 0)
                                    @foreach($palservicePoints as $palservicePoint)
                                        <tr>
                                            <td>{{ $palservicePoint->id }}</td>
                                            <td>
                                                <div class="user-block">
                                                    @if($palservicePoint->user && $palservicePoint->user->hasProfilePhoto())
                                                        <img src="{{ $palservicePoint->user->profileImage }}"
                                                             alt="Profile"
                                                             class="rounded-circle me-2"
                                                             width="40"
                                                             height="40"
                                                             onerror="this.src='{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}'">
                                                    @else
                                                        <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                                                             alt="Default Profile"
                                                             class="rounded-circle me-2"
                                                             width="40"
                                                             height="40">
                                                    @endif
                                                    <span class="username">
                        <a href="#">{{ $palservicePoint->user->name ?? 'N/A' }}</a>
                    </span>
                                                    <span class="description">
                        ID: {{ $palservicePoint->user?->id ?? 'N/A' }} |
                        Username: {{ $palservicePoint->user?->user_name ?? 'N/A' }}
                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($palservicePoint->user && count($palservicePoint->user->roles) > 0)
                                                    @foreach($palservicePoint->user->roles as $role)
                                                        <span class="badge badge-info">{{ $role->name ?? 'FIXME' }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="badge badge-secondary">{{ $palservicePoint->user?->roles->count() == 0 ? 'No Role' : 'FIXME' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-warning badge-pill px-3">{{ $palservicePoint->point ?? 'FIXME' }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('palservice_points.show', $palservicePoint->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                    @if(auth()->user()->hasPermission('grant_points') || auth()->user()->hasPermission('point_transfer'))
                                                        @if($palservicePoint->user)
                                                            <a href="{{ url('palservice_points') }}/{{ $palservicePoint->user->id }}" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-plus-circle mr-1"></i> Add/Deduct
                                                            </a>
                                                        @else
                                                            <span class="btn btn-sm btn-warning disabled" title="User has been deleted">
                                <i class="fas fa-user-times mr-1"></i> Deleted User
                            </span>
                                                        @endif
                                                    @endif
                                                    <a href="{{ route('palservice_points.edit', $palservicePoint->id) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-edit mr-1"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="confirmDelete('{{ route('palservice_points.destroy', $palservicePoint->id) }}')">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
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
                            {{ $palservicePoints->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('palservice_points\index.confirm_delete') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('palservice_points\index._times_') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this points record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('palservice_points\index.cancel') }}</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('palservice_points\index.delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Select Modal -->
    <div class="modal fade" id="userSelectModal" tabindex="-1" role="dialog" aria-labelledby="userSelectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userSelectModalLabel">{{ __('palservice_points\index.select_user') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{ __('palservice_points\index._times_') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="userSelectForm" method="GET">
                        <div class="form-group">
                            <label for="user_id">{{ __('palservice_points\index.enter_user_id_') }}</label>
                            <input type="number" class="form-control" id="user_id" name="user_id" placeholder="Enter user ID" required>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('palservice_points\index.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('palservice_points\index.continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-pill {
            padding-right: 0.8em;
            padding-left: 0.8em;
            font-size: 95%;
        }
        .user-block .username {
            font-weight: 600;
        }
        .user-block .description {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .btn-group .btn {
            margin-right: 3px;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Handle user select form submission
            $('#userSelectForm').on('submit', function(e) {
                e.preventDefault();
                var userId = $('#user_id').val();
                if (userId) {
                    window.location.href = "{{ url('palservice_points') }}/" + userId;
                }
            });
        });

        function confirmDelete(deleteUrl) {
            $('#deleteForm').attr('action', deleteUrl);
            $('#deleteModal').modal('show');
        }
    </script>
@stop
