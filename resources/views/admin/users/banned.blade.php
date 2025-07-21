@extends('adminlte::page')

@section('title', 'Banned Users')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-ban text-danger mr-2"></i> Banned Users</h1>
        <div>
            <a href="{{ route('devices.banned') }}" class="btn btn-warning">
                <i class="fas fa-mobile-alt mr-1"></i> Banned Devices
            </a>
            <a href="{{ route('bans.history') }}" class="btn btn-info ml-2">
                <i class="fas fa-history mr-1"></i> Ban History
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-primary ml-2">
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
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $bannedUsers->field</h3>
                        <p>{{ __('admin\users\banned.banned_users') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ App\Models\BannedDevice::whereNull('unban_at')->{{ __('admin\users\banned.count_') }}</h3>
                        <p>{{ __('admin\users\banned.banned_devices') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <a href="{{ route('devices.banned') }}" class="small-box-footer">
                        View devices <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ App\Models\BanHistory::where('action', 'ban')->{{ __('admin\users\banned.count_') }}</h3>
                        <p>{{ __('admin\users\banned.ban_actions') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <a href="{{ route('bans.history', ['action' => 'ban']) }}" class="small-box-footer">
                        View history <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ App\Models\BanHistory::where('action', 'unban')->{{ __('admin\users\banned.count_') }}</h3>
                        <p>{{ __('admin\users\banned.unban_actions') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="{{ route('bans.history', ['action' => 'unban']) }}" class="small-box-footer">
                        View history <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Card -->
        <div class="card card-outline card-danger mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-search mr-1"></i>
                    Search Banned Users
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.banned') }}" method="GET">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.users.banned') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Banned Users Card -->
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Banned Users List
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
                            <th style="width: 5%">{{ __('admin\users\banned.id') }}</th>
                            <th style="width: 15%">{{ __('admin\users\banned.user') }}</th>
                            <th style="width: 15%">{{ __('admin\users\banned.contact_info') }}</th>
                            <th style="width: 15%">{{ __('admin\users\banned.banned_devices') }}</th>
                            <th style="width: 20%">{{ __('admin\users\banned.ban_reason') }}</th>
                            <th style="width: 10%">{{ __('admin\users\banned.banned_at') }}</th>
                            <th style="width: 20%">{{ __('admin\users\banned.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bannedUsers as $user)
                            <tr>
                                <td>{{ $user->field</td>
                                <td>
                                    <div class="user-block">
                                        @if($user->photos && $user->photos->count() > 0)
                                            @php
                                                $photo = $user->photos->first();
                                                $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                            @endphp
                                            <img class="img-circle img-bordered-sm" src="{{ $imgSrc }}" alt="{{ $user->user_name }}">
                                        @else
                                            <img class="img-circle img-bordered-sm" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="User Image">
                                        @endif
                                        <span class="username">
                                            {{ $user->name }}
                                        </span>
                                        <span class="description">
                                            {{ $user->user_name }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ __('admin\users\banned.email_') }}</strong> {{ $user->email }}<br>
                                    <strong>{{ __('admin\users\banned.phone_') }}</strong> {{ $user->phones ?? 'N/A' }}
                                </td>
                                <td>
                                    @php
                                        $bannedDevices = $user->bannedDevices()->whereNull('unban_at')->get();
                                        $deviceCount = $bannedDevices->count();
                                    @endphp

                                    @if($deviceCount > 0)
                                        <span class="badge badge-warning">{{ $deviceCount }} banned {{ Str::plural('device', $deviceCount) }}</span><br>

                                        @foreach($bannedDevices->take(2) as $device)
                                            <small>
                                                <a href="#" data-toggle="modal" data-target="#deviceModal{{ $device->id }}">
                                                    {{ $device->device_brand ?? '' }} {{ $device->device_model ?? 'Device' }}
                                                    <i class="fas fa-info-circle text-info"></i>
                                                </a>
                                            </small><br>
                                        @endforeach

                                        @if($deviceCount > 2)
                                            <small>
                                                <a href="{{ route('devices.banned', ['user_id' => $user->id]) }}">
                                                    View all devices...
                                                </a>
                                            </small>
                                        @endif

                                        @foreach($bannedDevices as $device)
                                            @include('components.device-info-modal', ['device' => $device])
                                        @endforeach
                                    @else
                                        <span class="badge badge-secondary">{{ __('admin\users\banned.no_banned_devices') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $banHistory = App\Models\BanHistory::where('user_id', $user->id)
                                            ->where('action', 'ban')
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                        $banReason = $banHistory ? $banHistory->reason : 'No reason provided';
                                    @endphp
                                    {{ \Illuminate\Support\Str::limit($banReason, 100) }}
                                </td>
                                <td>
                                    @if($banHistory)
                                        {{ $banHistory->created_at->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">{{ __('admin\users\banned.unknown') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>

                                        <form action="{{ route('users.unban', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="reason" value="Manual unban by admin">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to unban this user?')">
                                                <i class="fas fa-user-check mr-1"></i> Unban
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#banHistoryModal{{ $user->id }}">
                                            <i class="fas fa-history mr-1"></i> History
                                        </button>
                                    </div>

                                    <!-- Ban History Modal -->
                                    <div class="modal fade" id="banHistoryModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-history mr-2"></i> Ban History for {{ $user->name }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">{{ __('admin\users\banned._times_') }}</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    @php
                                                        $banHistories = App\Models\BanHistory::where('user_id', $user->id)
                                                            ->orderBy('created_at', 'desc')
                                                            ->get();
                                                    @endphp

                                                    @if($banHistories->count() > 0)
                                                        <div class="timeline">
                                                            @foreach($banHistories as $history)
                                                                <div>
                                                                    <i class="fas fa-{{ $history->field</i>
                                                                    <div class="timeline-item">
                                                                        <span class="time">
                                                                            <i class="fas fa-clock"></i> {{ $history->created_at->format('Y-m-d H:i') }}
                                                                        </span>
                                                                        <h3 class="timeline-header">
                                                                            <strong>{{ ucfirst($history->field</strong> action
                                                                            @if($history->performer)
                                                                                by <a href="{{ route('users.show', $history->performed_by) }}">{{ $history->performer->{{ __('admin\users\banned.name_') }}</a>
                                                                            @endif
                                                                        </h3>
                                                                        <div class="timeline-body">
                                                                            {{ $history->reason }}

                                                                            @if($history->bannedDevice)
                                                                                <div class="mt-2">
                                                                                    <strong>{{ __('admin\users\banned.device_') }}</strong>
                                                                                    {{ $history->bannedDevice->device_brand }} {{ $history->bannedDevice->device_model }}
                                                                                    ({{ \Illuminate\Support\Str::limit($history->bannedDevice->device_id, 20) }})
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle mr-1"></i> No ban history found for this user.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin\users\banned.close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No banned users found
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
                    {{ $bannedUsers->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Timeline Styling */
        .timeline {
            position: relative;
            margin: 0 0 30px 0;
            padding: 0;
            list-style: none;
        }

        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #ddd;
            left: 31px;
            margin: 0;
            border-radius: 2px;
        }

        .timeline > div {
            position: relative;
            margin-right: 10px;
            margin-bottom: 15px;
        }

        .timeline > div > i {
            width: 30px;
            height: 30px;
            font-size: 15px;
            line-height: 30px;
            position: absolute;
            color: #fff;
            background: #d2d6de;
            border-radius: 50%;
            text-align: center;
            left: 18px;
            top: 0;
        }

        .timeline-item {
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 3px;
            margin-top: 0;
            background: #fff;
            color: #444;
            margin-left: 60px;
            margin-right: 15px;
            padding: 0;
            position: relative;
        }

        .timeline-item .time {
            color: #999;
            float: right;
            padding: 10px;
            font-size: 12px;
        }

        .timeline-item .timeline-header {
            margin: 0;
            color: #555;
            border-bottom: 1px solid #f4f4f4;
            padding: 10px;
            font-size: 16px;
            line-height: 1.1;
        }

        .timeline-item .timeline-body {
            padding: 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Confirm unban
            $('form button[type="submit"]').click(function(e) {
                if(!confirm('Are you sure you want to unban this user?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@stop
