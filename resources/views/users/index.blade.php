@extends('adminlte::page')
@section('title', 'Users')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users text-primary mr-2"></i> Users Management</h1>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fas fa-user-plus mr-1"></i> Add User
            </a>
            <a href="{{ route('users.banned') }}" class="btn btn-danger ml-2">
                <i class="fas fa-ban mr-1"></i> Banned Users
            </a>
            <a href="{{ route('devices.banned') }}" class="btn btn-warning ml-2">
                <i class="fas fa-mobile-alt mr-1"></i> Banned Devices
            </a>
            <a href="{{ route('role-assignments.index') }}" class="btn btn-info ml-2">
                <i class="fas fa-user-shield mr-1"></i> Roles & Permissions
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid">
        <!-- User Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3>{{ $Users->total() }}</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>{{ App\Models\User::where('is_active', 'active')->count() }}</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger shadow-sm">
                    <div class="inner">
                        <h3>{{ App\Models\User::where('is_active', 'banned')->count() }}</h3>
                        <p>Banned Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                    <a href="{{ route('users.banned') }}" class="small-box-footer">
                        View all <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner">
                        <h3>{{ App\Models\User::where('referred_by', '!=', null)->count() }}</h3>
                        <p>Referred Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <a href="{{ route('users.index', ['sort' => 'referrals']) }}" class="small-box-footer">
                        Top referrers <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modern Filter Section -->
        <div class="card card-outline card-light mb-3 shadow-sm">
            <div class="card-body py-2">
                <form action="{{ route('users.index') }}" method="GET" id="search-form" class="row align-items-end">
                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <select class="form-control" name="status" id="status-filter">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-2">
                        <select class="form-control" name="role" id="role-filter">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="search-input" value="{{ request('search') }}"
                                   placeholder="Search by name, email, user ID...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" id="search-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <div class="col-md-2 text-right">
                        <a href="{{ route('users.index') }}" class="btn btn-default">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Users Card -->
        <div class="card card-outline card-primary shadow">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Users List
                    @if(request('search') || request('status') || request('role'))
                        <span class="badge badge-info ml-2">Filtered Results</span>
                    @endif
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                    <button type="button" class="btn btn-tool ml-2" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 10%">Username</th>
                            <th style="width: 8%">Status</th>
                            <th style="width: 8%">Role</th>
                            <th style="width: 15%">Email</th>
                            <th style="width: 6%">Reports</th>
                            <th style="width: 6%">Posts</th>
                            <th style="width: 8%">Referral Code</th>
                            <th style="width: 6%">Referrals</th>
                            <th style="width: 28%">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(is_countable($Users) && count($Users) > 0)
                            @foreach($Users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="user-block">
                                            @if($user->photos && $user->photos->count() > 0)
                                                @php
                                                    $photo = $user->photos->first();
                                                    $imgSrc = isset($photo->is_external) && $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img class="img-circle img-bordered-sm" src="{{ $imgSrc }}" alt="{{ $user->user_name }}">
                                            @else
                                                <img class="img-circle img-bordered-sm" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="User Image">
                                            @endif
                                            <span class="username">
                                                {{ $user->user_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->is_active === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($user->is_active === 'banned')
                                            <span class="badge badge-danger">Banned</span>
                                        @else
                                            <span class="badge badge-warning">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($user->roles) > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-info">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-danger badge-pill">{{ $user->reports_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary badge-pill">{{ $user->service_posts_count }}</span>
                                    </td>
                                    <td>
                                        <code class="text-muted" style="font-size: 11px;">{{ $user->referral_code }}</code>
                                    </td>
                                    <td>
                                        @if(($user->total_referrals_count ?? 0) > 0)
                                            <span class="badge badge-success badge-pill" data-toggle="tooltip" title="Direct: {{ $user->direct_referrals_count ?? 0 }} | Total: {{ $user->total_referrals_count ?? 0 }}">
                                                <i class="fas fa-user-plus mr-1"></i>{{ $user->total_referrals_count }}
                                            </span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="{{ route('user.profile', ['user' => $user->id]) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-id-card"></i>
                                            </a>

                                            <a href="{{ route('role-assignments.edit', $user->id) }}" class="btn btn-sm btn-dark">
                                                <i class="fas fa-user-tag"></i>
                                            </a>

                                            <a href="{{ route('palservice_points.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-coins"></i>
                                            </a>

                                            <!-- Toggle Ban Button -->
                                            <button type="button"
                                                    class="btn btn-sm {{ $user->is_active === 'banned' ? 'btn-success' : 'btn-danger' }} toggle-ban-btn"
                                                    data-user-id="{{ $user->id }}"
                                                    data-current-status="{{ $user->is_active }}"
                                                    data-toggle="tooltip"
                                                    title="{{ $user->is_active === 'banned' ? 'Unban User' : 'Ban User' }}">
                                                <i class="fas {{ $user->is_active === 'banned' ? 'fa-check-circle' : 'fa-ban' }}"></i>
                                            </button>

                                            @include('components.report-button', ['reportableType' => 'user','reportableId' => $user->id,'buttonText' => ''])

                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this user?')"
                                                        data-toggle="tooltip" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> {{ __('No users found') }}
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
                    {{ $Users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Modern styling for buttons */
        .btn-group .btn {
            margin-right: 2px;
            border-radius: 4px;
        }

        /* Add subtle animations */
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        /* Make status pills more modern */
        .badge {
            padding: 5px 8px;
            font-weight: 500;
        }

        /* Stats cards enhancements */
        .small-box {
            border-radius: 6px;
            overflow: hidden;
        }

        .small-box .icon i {
            transition: all 0.3s linear;
        }

        .small-box:hover .icon i {
            transform: scale(1.1);
        }

        /* Table improvements */
        .table th {
            background-color: #f9f9f9;
            border-top: none;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.03);
        }

        /* User image enhancements */
        .user-block img {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-submit form when filters change
            $('#status-filter, #role-filter').on('change', function() {
                $('#search-form').submit();
            });

            // Confirm delete
            $('.btn-danger[type="submit"]').click(function(e) {
                if(!confirm('Are you sure you want to delete this user?')) {
                    e.preventDefault();
                }
            });

            // Toggle ban button click handler
            $('.toggle-ban-btn').on('click', function() {
                const btn = $(this);
                const userId = btn.data('user-id');
                const currentStatus = btn.data('current-status');
                const action = currentStatus === 'banned' ? 'unban' : 'ban';

                // Show loading state
                const originalHtml = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                btn.prop('disabled', true);

                // Get CSRF token from meta tag
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                // Send AJAX request to toggle ban status
                $.ajax({
                    url: '/users/' + userId + '/toggle-ban',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        action: action
                    },
                    success: function(response) {
                        // Update button appearance
                        const newStatus = action === 'ban' ? 'banned' : 'active';

                        if (action === 'ban') {
                            // Update to banned state
                            btn.removeClass('btn-danger').addClass('btn-success');
                            btn.html('<i class="fas fa-check-circle"></i>');
                            btn.attr('title', 'Unban User').tooltip('dispose').tooltip();

                            // Update status badge
                            btn.closest('tr').find('td:eq(2) span')
                                .removeClass('badge-success badge-warning')
                                .addClass('badge-danger')
                                .text('Banned');
                        } else {
                            // Update to active state
                            btn.removeClass('btn-success').addClass('btn-danger');
                            btn.html('<i class="fas fa-ban"></i>');
                            btn.attr('title', 'Ban User').tooltip('dispose').tooltip();

                            // Update status badge
                            btn.closest('tr').find('td:eq(2) span')
                                .removeClass('badge-danger badge-warning')
                                .addClass('badge-success')
                                .text('Active');
                        }

                        // Update data attribute
                        btn.attr('data-current-status', newStatus);
                        btn.data('current-status', newStatus);

                        // Re-enable the button
                        btn.prop('disabled', false);

                        // Show success message
                        if(typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        // Reset button
                        btn.html(originalHtml);
                        btn.prop('disabled', false);

                        // Show error message
                        const errorMessage = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message : 'An error occurred while processing your request.';

                        if(typeof toastr !== 'undefined') {
                            toastr.error(errorMessage);
                        } else {
                            alert('Error: ' + errorMessage);
                        }
                    }
                });
            });
        });
    </script>
@stop
