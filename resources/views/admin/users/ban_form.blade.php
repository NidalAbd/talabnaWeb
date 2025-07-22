@extends('adminlte::page')

@section('title', 'Ban User')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-ban text-danger mr-2"></i> Ban User: {{ $user->name }}</h1>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-slash mr-1"></i>
                            User Ban Form
                        </h3>
                    </div>
                    <form action="{{ route('users.ban', $user->id) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                                </div>
                            @endif

                            @if(session('warning'))
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
                                </div>
                            @endif

                            <!-- User Information Summary -->
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info-circle mr-1"></i> User Information</h5>
                                <table class="table table-striped">
                                    <tr>
                                        <th width="20%">User ID</th>
                                        <td>{{ $user->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Username</th>
                                        <td>{{ $user->user_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $user->phones ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($user->is_active === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @elseif($user->is_active === 'banned')
                                                <span class="badge badge-danger">Already Banned</span>
                                            @else
                                                <span class="badge badge-warning">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Posts</th>
                                        <td>{{ $user->service_posts_count ?? 0 }}</td>
                                    </tr>
                                    <tr>
                                        <th>Reports</th>
                                        <td>{{ $user->reports_count ?? 0 }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if($user->is_active === 'banned')
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <strong>Warning:</strong> This user is already banned. Submitting this form will update the ban reason.
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="reason">Ban Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                                <small class="form-text text-muted">The reason for banning this user. This will be used for administrative purposes.</small>
                                @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <h5 class="mt-4 mb-3"><i class="fas fa-mobile-alt mr-1"></i> Device Banning</h5>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="ban_devices" name="ban_devices" value="1" checked>
                                    <label class="custom-control-label" for="ban_devices">Also ban known devices used by this user</label>
                                </div>
                                <small class="form-text text-muted">This will ban all known devices associated with this user.</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="ban_current_device" name="ban_current_device" value="1">
                                    <label class="custom-control-label" for="ban_current_device">Add specific device ID</label>
                                </div>
                            </div>

                            <div id="device_id_container" style="display:none;">
                                <div class="form-group">
                                    <label for="device_id">Device ID</label>
                                    <input type="text" class="form-control @error('device_id') is-invalid @enderror" id="device_id" name="device_id" value="{{ old('device_id') }}">
                                    <small class="form-text text-muted">Specific device ID to add to the ban list.</small>
                                    @error('device_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm_ban" name="confirm_ban" required>
                                    <label class="custom-control-label" for="confirm_ban">I confirm that I want to ban this user</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban mr-1"></i> Ban User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Help Card -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-question-circle mr-1"></i>
                            Help & Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Warning:</strong> Banning a user will prevent them from accessing the app on any device. They will be shown a ban message when they attempt to log in.
                        </div>

                        <h5 class="mt-3"><i class="fas fa-info-circle mr-1"></i> What Happens When a User is Banned?</h5>
                        <ul>
                            <li>User cannot log in to the app</li>
                            <li>All future login attempts are tracked</li>
                            <li>New devices used by this user are automatically banned</li>
                            <li>User will see a banned screen with the provided reason</li>
                        </ul>

                        <h5 class="mt-3"><i class="fas fa-mobile-alt mr-1"></i> Device Banning</h5>
                        <p>When you ban a user, you can also ban all their known devices. This prevents them from simply logging in with a different account on the same device.</p>

                        <h5 class="mt-3"><i class="fas fa-undo mr-1"></i> Unbanning</h5>
                        <p>Users can be unbanned from the Banned Users management page. You can choose to unban just the user or both the user and their devices.</p>
                    </div>
                </div>

                <!-- User's Devices Card -->
                @php
                    $devices = $user->bannedDevices()->get();
                @endphp

                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-mobile-alt mr-1"></i>
                            User's Devices ({{ $devices->count() }})
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @if($devices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover m-0">
                                    <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($devices as $device)
                                        <tr>
                                            <td>
                                                <div>{{ $device->device_brand ?? 'Unknown' }} {{ $device->device_model ?? 'Device' }}</div>
                                                <small class="text-muted">ID: {{ Str::limit($device->device_id, 15) }}</small>
                                            </td>
                                            <td>
                                                @if($device->isActive())
                                                    <span class="badge badge-danger">Banned</span>
                                                @else
                                                    <span class="badge badge-success">Active</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-center">
                                <i class="fas fa-info-circle text-warning mb-2" style="font-size: 24px;"></i>
                                <p class="mb-0">No devices currently associated with this user.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Toggle device ID field
            $('#ban_current_device').change(function() {
                if($(this).is(':checked')) {
                    $('#device_id_container').slideDown();
                    $('#device_id').attr('required', true);
                } else {
                    $('#device_id_container').slideUp();
                    $('#device_id').attr('required', false);
                }
            });

            // Form validation
            $('form').submit(function(e) {
                if (!$('#reason').val().trim()) {
                    e.preventDefault();
                    alert('Ban reason is required');
                    $('#reason').focus();
                    return false;
                }

                if (!$('#confirm_ban').is(':checked')) {
                    e.preventDefault();
                    alert('You must confirm that you want to ban this user');
                    return false;
                }

                if ($('#ban_current_device').is(':checked') && !$('#device_id').val().trim()) {
                    e.preventDefault();
                    alert('Device ID is required when adding a specific device');
                    $('#device_id').focus();
                    return false;
                }

                return true;
            });
        });
    </script>
@stop
