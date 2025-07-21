@extends('adminlte::page')

@section('title', 'Permission Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-key text-info mr-2"></i> Permission Details: {{ $permission->display_name ?? $permission->field</h1>
        <div>
            @can('edit_permission')
                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit Permission
                </a>
            @endcan
            <a href="{{ route('permissions.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-arrow-left mr-1"></i> Back to Permissions
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- Permission Info Card -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin\permissions\show.permission_information') }}</h3>
                    </div>
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">
                            <span class="badge badge-info px-3 py-2">
                                {{ $permission->name }}
                            </span>
                        </h3>
                        <p class="text-muted text-center">{{ $permission->field</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.id') }}</b> <a class="float-right">{{ $permission->field</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.name') }}</b> <a class="float-right">{{ $permission->field</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.display_name') }}</b> <a class="float-right">{{ $permission->field</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.created') }}</b> <a class="float-right">{{ $permission->field</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.last_updated') }}</b> <a class="float-right">{{ $permission->field</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('admin\permissions\show.assigned_to_roles') }}</b> <a class="float-right">{{ count($roles) }}</a>
                            </li>
                        </ul>

                        <div class="text-center mt-4">
                            @php
                                $isSystemPermission = in_array($permission->name, [
                                    'view_role', 'create_role', 'edit_role', 'delete_role',
                                    'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
                                ]);
                            @endphp

                            @can('delete_permission')
                                @if(!$isSystemPermission && count($roles) == 0)
                                    <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-block" type="submit" onclick="return confirm('Are you sure you want to delete this permission?')">
                                            <i class="fas fa-trash mr-1"></i> Delete Permission
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-danger btn-block" disabled>
                                        <i class="fas fa-lock mr-1"></i> Cannot Delete
                                    </button>
                                    <small class="text-muted d-block mt-1">
                                        @if($isSystemPermission)
                                            System permissions cannot be deleted
                                        @else
                                            Permission is in use by one or more roles
                                        @endif
                                    </small>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Permission Description Card -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin\permissions\show.permission_description') }}</h3>
                    </div>
                    <div class="card-body">
                        @if($permission->description)
                            {{ $permission->description }}
                        @else
                            <div class="text-muted">{{ __('admin\permissions\show.no_description_available_for_this_permis') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Roles Using Permission Card -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin\permissions\show.roles_using_this_permission') }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">
                                {{ count($roles) }} Roles
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($roles) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>{{ __('admin\permissions\show.id') }}</th>
                                        <th>{{ __('admin\permissions\show.role_name') }}</th>
                                        <th>{{ __('admin\permissions\show.display_name') }}</th>
                                        <th>{{ __('admin\permissions\show.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($roles as $role)
                                        <tr>
                                            <td>{{ $role->field</td>
                                            <td>
                                                    <span class="badge badge-{{ $role->name == 'superadmin' ? 'danger' : ($role->name == 'admin' ? 'warning' : 'info') }}">
                                                        {{ $role->name }}
                                                    </span>
                                            </td>
                                            <td>{{ $role->field</td>
                                            <td>
                                                <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye mr-1"></i> View Role
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="icon fas fa-info-circle"></i>
                                This permission is not assigned to any roles.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Permission Usage Guide Card -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin\permissions\show.how_to_use_this_permission') }}</h3>
                    </div>
                    <div class="card-body">
                        <h5>{{ __('admin\permissions\show.in_blade_templates') }}</h5>
                        <pre><code>@can('{{ $permission->name }}')
                                    <!-- Your protected content here -->
                                @endcan</code></pre>

                        <h5 class="mt-4">{{ __('admin\permissions\show.in_controllers') }}</h5>
                        <pre><code>// Check if user has permission
if (auth()->user()->can('{{ $permission->name }}')) {
    // User has permission
}

// Or use middleware in routes/controller methods
$this->middleware('permission:{{ $permission->name }}');
</code></pre>

                        <h5 class="mt-4">{{ __('admin\permissions\show.in_routes') }}</h5>
                        <pre><code>// Protect routes with middleware
Route::group(['middleware' => ['permission:{{ $permission->name }}']], function () {
    // Your protected routes here
});</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
