@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit text-warning mr-2"></i> Edit Role: {{ $role->display_name ?? $role->name }}</h1>
        <div>
            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-info">
                <i class="fas fa-eye mr-1"></i> View Role
            </a>
            <a href="{{ route('roles.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-arrow-left mr-1"></i> Back to Roles
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Role Information</h3>
                    </div>

                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if(!$isEditable)
                                <div class="alert alert-warning">
                                    <i class="icon fas fa-exclamation-triangle"></i>
                                    This is a system role and has limited editing capabilities. You can only modify the display name and description.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Role Name</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="name" value="{{ $role->name }}" readonly disabled>
                                        </div>
                                        <small class="form-text text-muted">
                                            Role name cannot be changed once created
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="display_name">Display Name</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                            </div>
                                            <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                                   id="display_name" name="display_name"
                                                   value="{{ old('display_name', $role->display_name) }}"
                                                   placeholder="Human readable name (e.g. Content Editor)">
                                        </div>
                                        <small class="form-text text-muted">
                                            This is the name that will be displayed in the UI
                                        </small>
                                        @error('display_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Enter a description of this role's purpose and capabilities">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <hr>

                            <h4><i class="fas fa-key mr-2"></i> Role Permissions</h4>
                            <p class="text-muted">Select the permissions that will be assigned to this role:</p>

                            <div class="permissions-container">
                                @foreach($groupedPermissions as $group => $permissions)
                                    <div class="card card-outline card-secondary mb-4">
                                        <div class="card-header">
                                            <h3 class="card-title text-capitalize">{{ $group }} Permissions</h3>
                                            <div class="card-tools">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input select-all-group"
                                                           id="select-all-{{ $group }}" data-group="{{ $group }}"
                                                        {{ !$isEditable ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="select-all-{{ $group }}">
                                                        Select All
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input permission-checkbox {{ $group }}-checkbox"
                                                                       id="permission_{{ $permission->id }}" name="permissions[]"
                                                                       value="{{ $permission->id }}"
                                                                    {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                                                    {{ !$isEditable ? 'disabled' : '' }}>
                                                                <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                                    {{ $permission->display_name ?? $permission->name }}
                                                                </label>
                                                                <small class="d-block text-muted">{{ $permission->name }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i> Update Role
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-default float-right">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
            $('.permissions-container').on('change', '.permission-checkbox', function() {
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
        });
    </script>
@stop
