@extends('adminlte::page')

@section('title', 'Create Permission')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-plus-circle text-success mr-2"></i> Create New Permission</h1>
        <a href="{{ route('permissions.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Permissions
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">{{('admin\permissions\create.permission_information') }}</h3>
                    </div>

                    <form action="{{ route('permissions.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Permission Name <span class="text-danger">{{('admin\permissions\create._') }}</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            </div>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name') }}"
                                                   placeholder="Enter a unique permission name (e.g. view_users)" required>
                                        </div>
                                        <small class="form-text text-muted">
                                            Use lowercase letters and underscores (e.g. view_users, manage_settings)
                                        </small>
                                        @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="display_name">{{('admin\permissions\create.display_name') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-eye"></i></span>
                                            </div>
                                            <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                                   id="display_name" name="display_name" value="{{ old('display_name') }}"
                                                   placeholder="Human readable name (e.g. View Users)">
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
                                <label for="description">{{('admin\permissions\create.description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3"
                                          placeholder="Enter a description of this permission">{{ old('description') }}</textarea>
                                @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="icon fas fa-info-circle"></i>
                                <h5>{{('admin\permissions\create.permission_naming_conventions') }}</h5>
                                <p class="mb-0">{{('admin\permissions\create.it_s_recommended_to_follow_a_consistent_') }}</p>
                                <ul>
                                    <li><strong>{{('admin\permissions\create.view_resource_') }}</strong> - For viewing/listing a resource</li>
                                    <li><strong>{{('admin\permissions\create.create_resource_') }}</strong> - For creating a new resource</li>
                                    <li><strong>{{('admin\permissions\create.edit_resource_') }}</strong> - For updating an existing resource</li>
                                    <li><strong>{{('admin\permissions\create.delete_resource_') }}</strong> - For deleting a resource</li>
                                    <li><strong>{{('admin\permissions\create.manage_resource_') }}</strong> - For full control over a resource</li>
                                </ul>
                                <p class="mb-0">{{('admin\permissions\create.examples_view_users_edit_products_man') }}</p>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Create Permission
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-default float-right">
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
            // Auto-generate display name based on permission name
            $('#name').on('input', function() {
                const name = $(this).val();
                if (name && !$('#display_name').val()) {
                    // Convert view_users to View Users
                    const displayName = name
                        .split('_')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');

                    $('#display_name').val(displayName);
                }
            });
        });
    </script>
@stop







