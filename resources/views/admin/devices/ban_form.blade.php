@extends('te::page')

@section('title', 'Ban Device')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-ban text-danger mr-2"></i> Ban Device</h1>
        <div>
            <a href="{{ route('devices.banned') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Bacwk to Banned Devices
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
                            <i class="fas fa-mobile-alt mr-1"></i>
                            Device Ban Form
                        </h3>
                    </div>
                    <form action="{{ route('devices.ban') }}" method="POST">
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

                            <div class="form-group">
                                <label for="device_id">Device ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('device_id') is-invalid @enderror" id="device_id" name="device_id" value="{{ old('device_id') }}" required>
                                <small class="form-text text-muted">The unique identifier for the device. This is required and must be unique.</small>
                                @error('device_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Ban Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                                <small class="form-text text-muted">The reason for banning the device. This will be shown to the user.</small>
                                @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <h5 class="mt-4 mb-3"><i class="fas fa-user mr-1"></i> Associated User Information</h5>

                            <div class="form-group">
                                <label for="user_id">User ID</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" value="{{ old('user_id') }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" id="findUserBtn">
                                            <i class="fas fa-search"></i> Find User
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">If provided, this user will also be banned.</small>
                                @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                        @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3"><i class="fas fa-cog mr-1"></i> Device Details (Optional)</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="device_brand">Device Brand</label>
                                        <input type="text" class="form-control" id="device_brand" name="device_brand" value="{{ old('device_brand') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="device_model">Device Model</label>
                                        <input type="text" class="form-control" id="device_model" name="device_model" value="{{ old('device_model') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="device_name">Device Name</label>
                                        <input type="text" class="form-control" id="device_name" name="device_name" value="{{ old('device_name') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="os_version">OS Version</label>
                                        <input type="text" class="form-control" id="os_version" name="os_version" value="{{ old('os_version') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm_ban" name="confirm_ban" required>
                                    <label class="custom-control-label" for="confirm_ban">I confirm that I want to ban this device</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban mr-1"></i> Ban Device
                            </button>
                            <a href="{{ route('devices.banned') }}" class="btn btn-secondary ml-2">
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
                            <strong>Warning:</strong> Banning a device will prevent that device from accessing the app. If a user ID is provided, that user will also be banned.
                        </div>

                        <h5 class="mt-3"><i class="fas fa-info-circle mr-1"></i> What is a Device ID?</h5>
                        <p>A Device ID is a unique identifier for a mobile device. In the app, this is generated and stored for each installation.</p>

                        <h5 class="mt-3"><i class="fas fa-mobile-alt mr-1"></i> Device Information</h5>
                        <p>Providing additional device information is optional but can help with administration and tracking.</p>

                        <h5 class="mt-3"><i class="fas fa-user-slash mr-1"></i> User Banning</h5>
                        <p>When a user is banned:</p>
                        <ul>
                            <li>Their account status is set to "banned"</li>
                            <li>They cannot log in on any device</li>
                            <li>All devices they log in with will also be banned</li>
                        </ul>

                        <h5 class="mt-3"><i class="fas fa-undo mr-1"></i> Unbanning</h5>
                        <p>Users and devices can be unbanned from their respective management pages.</p>
                    </div>
                </div>

                <!-- User Search Results Card (Initially Hidden) -->
                <div class="card card-primary card-outline mt-3" id="userSearchCard" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-search mr-1"></i>
                            User Search Results
                        </h3>
                    </div>
                    <div class="card-body" id="userSearchResults">
                        <!-- Results will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Find User Button Click
            $('#findUserBtn').click(function() {
                const userId = $('#user_id').val();
                const email = $('#email').val();
                const phone = $('#phone').val();

                // Check if we have at least one search parameter
                if (!userId && !email && !phone) {
                    alert('Please enter a User ID, Email, or Phone to search');
                    return;
                }

                // Show loading in the results card
                $('#userSearchCard').show();
                $('#userSearchResults').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');

                // Make AJAX request to search for users
                $.ajax({
                    url: '{{ route("api.users.search") }}',
                    type: 'GET',
                    data: {
                        user_id: userId,
                        email: email,
                        phone: phone
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.users.length > 0) {
                                // Build the results HTML
                                let resultsHtml = '<div class="list-group">';

                                response.users.forEach(function(user) {
                                    let userStatus = '';
                                    if (user.is_active === 'active') {
                                        userStatus = '<span class="badge badge-success">Active</span>';
                                    } else if (user.is_active === 'banned') {
                                        userStatus = '<span class="badge badge-danger">Banned</span>';
                                    } else {
                                        userStatus = '<span class="badge badge-warning">Inactive</span>';
                                    }

                                    resultsHtml += `
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">${user.name}</h5>
                                            ${userStatus}
                                        </div>
                                        <p class="mb-1">ID: ${user.id} | Username: ${user.user_name || 'N/A'}</p>
                                        <small>Email: ${user.email} | Phone: ${user.phones || 'N/A'}</small>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-primary select-user"
                                                data-id="${user.id}"
                                                data-email="${user.email}"
                                                data-phone="${user.phones || ''}">
                                                <i class="fas fa-check mr-1"></i> Select User
                                            </button>
                                            <a href="${user.id}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i> View Profile
                                            </a>
                                        </div>
                                    </div>
                                `;
                                });

                                resultsHtml += '</div>';
                                $('#userSearchResults').html(resultsHtml);

                                // Add event handler for selecting users
                                $('.select-user').click(function() {
                                    const userId = $(this).data('id');
                                    const email = $(this).data('email');
                                    const phone = $(this).data('phone');

                                    $('#user_id').val(userId);
                                    $('#email').val(email);
                                    $('#phone').val(phone);

                                    // Hide the results card
                                    $('#userSearchCard').fadeOut();
                                });
                            } else {
                                $('#userSearchResults').html('<div class="alert alert-info m-0"><i class="fas fa-info-circle mr-1"></i> No users found matching your search criteria.</div>');
                            }
                        } else {
                            $('#userSearchResults').html('<div class="alert alert-danger m-0"><i class="fas fa-times-circle mr-1"></i> Error: ' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'An error occurred while searching for users.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            // Keep default error message
                        }

                        $('#userSearchResults').html('<div class="alert alert-danger m-0"><i class="fas fa-times-circle mr-1"></i> Error: ' + errorMessage + '</div>');
                    }
                });
            });

            // Form validation
            $('form').submit(function(e) {
                if (!$('#device_id').val().trim()) {
                    e.preventDefault();
                    alert('Device ID is required');
                    $('#device_id').focus();
                    return false;
                }

                if (!$('#reason').val().trim()) {
                    e.preventDefault();
                    alert('Ban reason is required');
                    $('#reason').focus();
                    return false;
                }

                if (!$('#confirm_ban').is(':checked')) {
                    e.preventDefault();
                    alert('You must confirm that you want to ban this device');
                    return false;
                }

                return true;
            });
        });
    </script>
@stop
