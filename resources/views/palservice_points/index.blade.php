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
                                <span class="info-box-text">Total Points</span>
                                <span class="info-box-number">{{ $palservicePoints->sum('point') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-user-tag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Users</span>
                                <span class="info-box-number">{{ $palservicePoints->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg. Points</span>
                                <span class="info-box-number">{{ $palservicePoints->count() > 0 ? round($palservicePoints->sum('point') / $palservicePoints->count(), 2) : 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-trophy"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Top Score</span>
                                <span class="info-box-number">{{ $palservicePoints->max('point') }}</span>
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
                                        <label for="search">Search Users</label>
                                        <input type="text" class="form-control" id="search" name="search"
                                               placeholder="Search by name, username, or ID"
                                               value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="min_points">Min Points</label>
                                        <input type="number" class="form-control" id="min_points" name="min_points"
                                               placeholder="Minimum points"
                                               value="{{ request('min_points') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="max_points">Max Points</label>
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

                <!-- Main Card -->
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
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 30%">User</th>
                                    <th style="width: 20%">Role</th>
                                    <th style="width: 15%">Points</th>
                                    <th style="width: 25%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($palservicePoints) && count($palservicePoints) > 0)
                                    @foreach($palservicePoints as $palservicePoint)
                                        <tr>
                                            <td>{{ $palservicePoint->id }}</td>
                                            <td>
                                                <div class="user-block">
                                                    @if($palservicePoint->user && $palservicePoint->user->photos && $palservicePoint->user->photos->count() > 0)
                                                        @php
                                                            $photo = $palservicePoint->user->photos->first();
                                                            $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                        @endphp
                                                        <img class="img-circle" src="{{ $imgSrc }}" alt="{{ $palservicePoint->user->name }}">
                                                    @else
                                                        <img class="img-circle" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="User Image">
                                                    @endif
                                                    <span class="username">
                                                    <a href="#">{{ $palservicePoint->user?->name ?? 'Passed User' }}</a>
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
                                                        <span class="badge badge-info">{{ $role->name ?? 'Passed User' }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="badge badge-secondary">Passed User</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-warning badge-pill px-3">{{ $palservicePoint->point }} pts</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('palservice_points.show', $palservicePoint->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                    @if(auth()->user()->hasPermission('grant_points') || auth()->user()->hasPermission('point_transfer'))
                                                        <a href="{{ url('palservice_points') }}/{{ $palservicePoint->user->id }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-plus-circle mr-1"></i> Add/Deduct
                                                        </a>
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
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this points record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
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
                    <h5 class="modal-title" id="userSelectModalLabel">Select User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="userSelectForm" method="GET">
                        <div class="form-group">
                            <label for="user_id">Enter User ID:</label>
                            <input type="number" class="form-control" id="user_id" name="user_id" placeholder="Enter user ID" required>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Continue</button>
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
