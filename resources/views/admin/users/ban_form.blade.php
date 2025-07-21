@extends('adminlte::page')

@section('title', 'Ban User')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-ban text-danger mr-2"></i> Ban User: {{ $user->field</h1>
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
                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST">
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
                                        <th width="20%">{{ __('admin\users\ban_form.user_id') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.name') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.username') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.email') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.phone') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.status') }}</th>
                                        <td>
                                            @if($user->is_active === 'active')
                                                <span class="badge badge-success">{{ __('admin\users\ban_form.active') }}</span>
                                            @elseif($user->is_active === 'banned')
                                                <span class="badge badge-danger">{{ __('admin\users\ban_form.already_banned') }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ __('admin\users\ban_form.inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.posts') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('admin\users\ban_form.reports') }}</th>
                                        <td>{{ $user->field</td>
                                    </tr>
                                </table>
                            </div>

                            @if($user->is_active === 'banned')
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <strong>{{ __('admin\users\ban_form.warning_') }}</strong> This user is already banned. Submitting this form will update the ban reason.
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="reason">Ban Reason <span class="text-danger">{{ __('admin\users\ban_form._') }}</span></label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                                <small class="form-text text-muted">{{ __('admin\users\ban_form.the_reason_for_banning_this_user_this_w') }}</small>
                                @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <h5 class="mt-4 mb-3"><i class="fas fa-mobile-alt mr-1"></i> Device Banning</h5>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="ban_devices" name="ban_devices" value="1" checked>
                                    <label class="custom-control-label" for="ban_devices">{{ __('admin\users\ban_form.also_ban_known_devices_used_by_this_user') }}</label>
                                </div>
                                <small class="form-text text-muted">{{ __('admin\users\ban_form.this_will_ban_all_known_devices_associat') }}</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="ban_current_device" name="ban_current_device" value="1">
                                    <label class="custom-control-label" for="ban_current_device">{{ __('admin\users\ban_form.add_specific_device_id') }}</label>
                                </div>
                            </div>

                            <div id="device_id_container" style="display:none;">
                                <div class="form-group">
                                    <label for="device_id">{{ __('admin\users\ban_form.device_id') }}</label>
                                    <input type="text" class="form-control @error('device_id') is-invalid @enderror" id="device_id" name="device_id" value="{{ old('device_id') }}">
                                    <small class="form-text text-muted">{{ __('admin\users\ban_form.specific_device_id_to_add_to_the_ban_lis') }}</small>
                                    @error('device_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm_ban" name="confirm_ban" required>
                                    <label class="custom-control-label" for="confirm_ban">{{ __('admin\users\ban_form.i_confirm_that_i_want_to_ban_this_user') }}</label>
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
                            <strong>{{ __('admin\users\ban_form.warning_') }}</strong> Banning a user will prevent them from accessing the app on any device. They will be shown a ban message when they attempt to log in.
                        </div>

                        <h5 class="mt-3"><i class="fas fa-info-circle mr-1"></i> What Happens When a User is Banned?</h5>
                        <ul>
                            <li>{{ __('admin\users\ban_form.user_cannot_log_in_to_the_app') }}</li>
                            <li>{{ __('admin\users\ban_form.all_future_login_attempts_are_tracked') }}</li>
                            <li>{{ __('admin\users\ban_form.new_devices_used_by_this_user_are_automa') }}</li>
                            <li>{{ __('admin\users\ban_form.user_will_see_a_banned_screen_with_the_p') }}</li>
                        </ul>

                        <h5 class="mt-3"><i class="fas fa-mobile-alt mr-1"></i> Device Banning</h5>
                        <p>{{ __('admin\users\ban_form.when_you_ban_a_user_you_can_also_ban_al') }}</p>

                        <h5 class="mt-3"><i class="fas fa-undo mr-1"></i> Unbanning</h5>
                        <p>{{ __('admin\users\ban_form.users_can_be_unbanned_from_the_banned_us') }}</p>
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
                                        <th>{{ __('admin\users\ban_form.device') }}</th>
                                        <th>{{ __('admin\users\ban_form.status') }}</th>
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
                                                    <span class="badge badge-danger">{{ __('admin\users\ban_form.banned') }}</span>
                                                @else
                                                    <span class="badge badge-success">{{ __('admin\users\ban_form.active') }}</span>
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
                                <p class="mb-0">{{ __('admin\users\ban_form.no_devices_currently_associated_with_thi') }}</p>
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
