@extends('adminlte::page')

@section('title', 'Banned Devices')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-mobile-alt text-warning mr-2"></i> Banned Devices</h1>
        <div>
            <a href="{{ route('devices.ban.form') }}" class="btn btn-danger">
                <i class="fas fa-ban mr-1"></i> Ban New Device
            </a>
            <a href="{{ route('admin.users.banned') }}" class="btn btn-primary ml-2">
                <i class="fas fa-user-slash mr-1"></i> Banned Users
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-info ml-2">
                <i class="fas fa-users mr-1"></i> All Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $bannedDevices->field</h3>
                        <p>{{ __('admin\devices\banned.total_banned_devices') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $activeDevicesCount }}</h3>
                        <p>{{ __('admin\devices\banned.currently_banned') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $unbannedDevicesCount }}</h3>
                        <p>{{ __('admin\devices\banned.unbanned_devices') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ App\Models\User::where('is_active', 'banned')->{{ __('admin\devices\banned.count_') }}</h3>
                        <p>{{ __('admin\devices\banned.banned_users') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <a href="{{ route('admin.users.banned') }}" class="small-box-footer">
                        View users <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card card-outline card-warning mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i>
                    Search & Filters
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('devices.banned') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('admin\devices\banned.device_status_') }}</label>
                                <select class="form-control" name="status">
                                    <option value="">{{ __('admin\devices\banned.all_devices') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin\devices\banned.currently_banned') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('admin\devices\banned.unbanned') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('admin\devices\banned.user_filter_') }}</label>
                                <select class="form-control" name="user_filter">
                                    <option value="">{{ __('admin\devices\banned.all_devices') }}</option>
                                    <option value="with_user" {{ request('user_filter') == 'with_user' ? 'selected' : '' }}>{{ __('admin\devices\banned.with_user') }}</option>
                                    <option value="without_user" {{ request('user_filter') == 'without_user' ? 'selected' : '' }}>{{ __('admin\devices\banned.without_user') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('admin\devices\banned.search_') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by device ID, email, phone...">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                        <a href="{{ route('devices.banned') }}" class="btn btn-default">
                                            <i class="fas fa-sync-alt"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Banned Devices Card -->
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Banned Devices List
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
                            <th style="width: 5%">{{ __('admin\devices\banned.id') }}</th>
                            <th style="width: 15%">{{ __('admin\devices\banned.device_info') }}</th>
                            <th style="width: 15%">{{ __('admin\devices\banned.user') }}</th>
                            <th style="width: 20%">{{ __('admin\devices\banned.ban_reason') }}</th>
                            <th style="width: 10%">{{ __('admin\devices\banned.status') }}</th>
                            <th style="width: 15%">{{ __('admin\devices\banned.dates') }}</th>
                            <th style="width: 20%">{{ __('admin\devices\banned.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bannedDevices as $device)
                            <tr>
                                <td>{{ $device->field</td>
                                <td>
                                    <strong>{{ __('admin\devices\banned.id_') }}</strong> <code class="text-truncate d-block" style="max-width: 150px;">{{ $device->field</code>
                                    <small>{{ $device->device_brand ?? 'Unknown' }} {{ $device->device_model ?? 'Device' }}</small><br>
                                    <small>OS: {{ $device->os_version ?? 'Unknown' }}</small>
                                </td>
                                <td>
                                    @if($device->user)
                                        <div class="user-block">
                                            @if($device->user->photos && $device->user->photos->count() > 0)
                                                @php
                                                    $photo = $device->user->photos->first();
                                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img class="img-circle img-bordered-sm" src="{{ $imgSrc }}" alt="{{ $device->user->user_name }}">
                                            @else
                                                <img class="img-circle img-bordered-sm" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="User Image">
                                            @endif
                                            <span class="username">
                                                <a href="{{ route('users.show', $device->user_id) }}">{{ $device->user->name }}</a>
                                            </span>
                                            <span class="description">
                                                {{ $device->user->email }}
                                            </span>
                                        </div>
                                    @else
                                        <em>{{ __('admin\devices\banned.no_associated_user') }}</em><br>
                                        @if($device->email)
                                            <small>Email: {{ $device->email }}</small><br>
                                        @endif
                                        @if($device->phone)
                                            <small>Phone: {{ $device->phone }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($device->field</td>
                                <td>
                                    @if($device->isActive())
                                        <span class="badge badge-danger">{{ __('admin\devices\banned.banned') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('admin\devices\banned.unbanned') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ __('admin\devices\banned.banned_') }}</strong> {{ $device->banned_at->format('Y-m-d H:i') }}<br>
                                    @if($device->unban_at)
                                        <strong>{{ __('admin\devices\banned.unbanned_') }}</strong> {{ $device->unban_at->format('Y-m-d H:i') }}
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#deviceModal{{ $device->id }}">
                                            <i class="fas fa-info-circle mr-1"></i> Details
                                        </button>

                                        @if($device->isActive())
                                            <form action="{{ route('devices.unban', $device->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="reason" value="Manual unban by admin">
                                                <button type="submit" class="btn btn-sm btn-success unban-btn" data-id="{{ $device->id }}">
                                                    <i class="fas fa-check-circle mr-1"></i> Unban
                                                </button>
                                            </form>
                                        @endif

                                        @if($device->user && $device->user->is_active === 'banned')
                                            <form action="{{ route('users.unban', $device->user_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="reason" value="Unbanned with device">
                                                <button type="submit" class="btn btn-sm btn-primary unban-user-btn" data-id="{{ $device->user_id }}">
                                                    <i class="fas fa-user-check mr-1"></i> Unban User
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @include('components.device-info-modal', ['device' => $device])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No banned devices found
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
                    {{ $bannedDevices->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Confirm unban device
            $('.unban-btn').click(function(e) {
                e.preventDefault();
                const deviceId = $(this).data('id');

                if(confirm('Are you sure you want to unban this device?')) {
                    $(this).closest('form').submit();
                }
            });

            // Confirm unban user
            $('.unban-user-btn').click(function(e) {
                e.preventDefault();
                const userId = $(this).data('id');

                if(confirm('Are you sure you want to unban this user?')) {
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
@stop
