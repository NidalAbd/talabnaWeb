@extends('adminlte::page')

@section('title', 'Edit User Role Assignments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-user-tag text-warning mr-2"></i>
            Edit User Role Assignments
        </h1>
        <div>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-info">
                <i class="fas fa-eye mr-1"></i> View User
            </a>
            <a href="{{ route('role-assignments.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>

        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- User Info Card -->
            <div class="col-lg-3 col-md-4">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-user-circle mr-1"></i> User Profile
                        </h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            @if($user->photos && $user->photos->count() > 0)
                                @php
                                    $photo = $user->photos->first();
                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                @endphp
                                <img class="profile-user-img img-fluid img-circle"
                                     src="{{ $imgSrc }}" alt="{{ $user->name }}">
                            @else
                                <img class="profile-user-img img-fluid img-circle"
                                     src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}"
                                     alt="User profile picture">
                            @endif
                        </div>

                        <h3 class="profile-username text-center">{{ $user->name }}</h3>
                        <p class="text-muted text-center">
                            <i class="fas fa-at text-secondary"></i> {{ $user->user_name ?? $user->email }}
                        </p>

                        <div class="list-group list-group-flush mb-3">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-id-badge text-primary mr-2"></i> ID</span>
                                <span class="badge badge-light">{{ $user->id }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope text-primary mr-2"></i> Email</span>
                                <span class="text-truncate ml-2" style="max-width: 160px;">{{ $user->email }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-toggle-on text-primary mr-2"></i> Status</span>
                                <span class="badge badge-{{ $user->is_active == 'active' ? 'success' : ($user->is_active == 'banned' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($user->is_active) }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-alt text-primary mr-2"></i> Joined</span>
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user-tag text-primary mr-2"></i> Roles</span>
                                <span class="badge badge-primary">{{ $user->roles->count() }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-key text-primary mr-2"></i> Permissions</span>
                                <span class="badge badge-info">{{ $user->permissions->count() }}</span>
                            </div>
                        </div>

                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-edit mr-1"></i> Edit User
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                <form action="{{ route('role-assignments.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Assignment Tabs -->
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header p-0">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="roles-tab" data-toggle="tab" href="#roles-content" role="tab">
                                        <i class="fas fa-user-tag mr-1"></i> Roles
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions-content" role="tab">
                                        <i class="fas fa-key mr-1"></i> Permissions
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Roles Tab -->
                                <div class="tab-pane fade show active" id="roles-content" role="tabpanel">
                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info-circle"></i>
                                        Select the roles to assign to this user. Each role includes a set of permissions.
                                    </div>

                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-xl-3 col-lg-4 col-md-6">
                                                <div class="card mb-3 {{ $role->assigned ? 'border-primary shadow-sm' : 'border' }}">
                                                    <div class="card-body p-3">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input"
                                                                   id="role_{{ $role->id }}" name="roles[]"
                                                                   value="{{ $role->id }}" {{ $role->assigned ? 'checked' : '' }}>
                                                            <label class="custom-control-label font-weight-bold" for="role_{{ $role->id }}">
                                                                {{ $role->display_name ?? $role->name }}
                                                            </label>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">{{ $role->description ?? $role->name }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Permissions Tab -->
                                <div class="tab-pane fade" id="permissions-content" role="tabpanel">
                                    @if($groupedPermissions)
                                        <div class="alert alert-warning">
                                            <i class="icon fas fa-exclamation-triangle"></i>
                                            <strong>Note:</strong> Direct permissions override role-based permissions.
                                            Only assign direct permissions when you need to give a user special access outside of their roles.
                                        </div>

                                        <div class="accordion" id="permissionsAccordion">
                                            @foreach($groupedPermissions as $group => $permissions)
                                                <div class="card mb-2">
                                                    <div class="card-header py-2 px-3" id="heading{{ $group }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h5 class="mb-0">
                                                                <button class="btn btn-link btn-block text-left text-decoration-none text-dark"
                                                                        type="button" data-toggle="collapse"
                                                                        data-target="#collapse{{ $group }}"
                                                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                                                        aria-controls="collapse{{ $group }}">
                                                                    <i class="fas fa-angle-down mr-2"></i>
                                                                    <span class="text-capitalize">{{ $group }} Permissions</span>
                                                                    <span class="badge badge-info ml-2">{{ count($permissions) }}</span>
                                                                </button>
                                                            </h5>
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input select-all-group"
                                                                       id="select-all-{{ $group }}" data-group="{{ $group }}">
                                                                <label class="custom-control-label" for="select-all-{{ $group }}">
                                                                    Select All
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="collapse{{ $group }}"
                                                         class="collapse {{ $loop->first ? 'show' : '' }}"
                                                         aria-labelledby="heading{{ $group }}"
                                                         data-parent="#permissionsAccordion">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                @foreach($permissions as $permission)
                                                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                                                        <div class="custom-control custom-switch mb-3">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input permission-checkbox {{ $group }}-checkbox"
                                                                                   id="permission_{{ $permission->id }}"
                                                                                   name="permissions[]"
                                                                                   value="{{ $permission->id }}"
                                                                                {{ $permission->assigned ? 'checked' : '' }}>
                                                                            <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                                                {{ $permission->display_name ?? $permission->name }}
                                                                                <small class="d-block text-muted">{{ $permission->name }}</small>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-1"></i> Direct permission assignment is disabled in system settings.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save Assignments
                            </button>
                            <button type="button" class="btn btn-warning ml-2" id="reset-permissions">
                                <i class="fas fa-sync-alt mr-1"></i> Reset to Role Permissions
                            </button>
                            <a href="{{ route('role-assignments.index') }}" class="btn btn-default float-right">
                                Cancel
                            </a>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .custom-switch .custom-control-label::before {
            width: 2.25rem;
        }
        .custom-switch .custom-control-label::after {
            width: calc(1rem - 4px);
        }
        .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
            transform: translateX(1.25rem);
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Select all permissions in a group
            $('.select-all-group').on('change', function() {
                const groupClass = $(this).data('group') + '-checkbox';
                $('.' + groupClass).prop('checked', $(this).prop('checked'));
            });

            // Update "select all" checkbox when individual permissions change
            $('.permission-checkbox').on('change', function() {
                const groupClass = $(this).attr('class').split(' ')[1];
                const group = groupClass.replace('-checkbox', '');
                const allChecked = $('.' + groupClass + ':not(:checked)').length === 0;
                $('#select-all-' + group).prop('checked', allChecked);
            });

            // Initialize "select all" checkboxes based on current state
            $('.select-all-group').each(function() {
                const group = $(this).data('group');
                const groupClass = group + '-checkbox';
                const allChecked = $('.' + groupClass + ':not(:checked)').length === 0;
                $(this).prop('checked', allChecked && $('.' + groupClass).length > 0);
            });

            // Add highlight to role cards when selected
            $('input[name="roles[]"]').on('change', function() {
                const card = $(this).closest('.card');
                if ($(this).is(':checked')) {
                    card.removeClass('border').addClass('border-primary shadow-sm');
                } else {
                    card.removeClass('border-primary shadow-sm').addClass('border');
                }
            });

            $('#reset-permissions').on('click', function() {
                const selectedRoles = $('input[name="roles[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedRoles.length === 0) {
                    // Fallback to browser alert if SweetAlert is not working
                    alert('Please select at least one role to reset permissions.');
                    return;
                }

                $.ajax({
                    url: '{{ route("role-assignments.default-permissions") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        roles: selectedRoles
                    },
                    success: function(response) {
                        try {
                            if (response.permissions) {
                                // Uncheck all current permissions
                                $('.permission-checkbox').prop('checked', false);

                                // Check permissions returned from server
                                response.permissions.forEach(function(permissionId) {
                                    $('#permission_' + permissionId).prop('checked', true);
                                });

                                // Update "select all" checkboxes
                                $('.select-all-group').each(function() {
                                    const group = $(this).data('group');
                                    const groupClass = group + '-checkbox';
                                    const allChecked = $('.' + groupClass + ':not(:checked)').length === 0;
                                    $(this).prop('checked', allChecked && $('.' + groupClass).length > 0);
                                });

                                // Fallback alert
                                alert('Permissions have been reset to the default for the selected roles.');
                            }
                        } catch (err) {
                            console.error('Error processing permissions:', err);
                            alert('An error occurred while resetting permissions.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Permission reset error:', xhr);

                        // Fallback error handling
                        const errorMessage = xhr.responseJSON && xhr.responseJSON.details
                            ? xhr.responseJSON.details
                            : 'Failed to retrieve default permissions.';

                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@stop
