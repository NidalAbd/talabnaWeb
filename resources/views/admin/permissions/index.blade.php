@extends('adminlte::page')

@section('title', 'Permissions Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-key text-primary mr-2"></i> Permissions Management</h1>
        <div>
            @can('create_permission')
                <a href="{{ route('permissions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle mr-1"></i> Create Permission
                </a>
            @endcan
            <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#generatePermissionsModal">
                <i class="fas fa-magic mr-1"></i> Generate CRUD Permissions
            </button>
            @can('view_role')
                <a href="{{ route('roles.index') }}" class="btn btn-info ml-2">
                    <i class="fas fa-user-tag mr-1"></i> Manage Roles
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Permission Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $permissions->total() }}</h3>
                        <p>Total Permissions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $groupedPermissions->count() }}</h3>
                        <p>Permission Categories</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ DB::table(Config::get('laratrust.tables.permission_role'))->count() }}</h3>
                        <p>Role Assignments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-link"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ DB::table(Config::get('laratrust.tables.permission_user'))->count() }}</h3>
                        <p>Direct User Assignments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Card -->
        <div class="card card-outline card-primary collapsed-card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i>
                    Search & Filters
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('permissions.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Search by Name or Description:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search permissions...">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Filter by Category:</label>
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($groupedPermissions as $group => $permissions)
                                        <option value="{{ $group }}" {{ request('category') == $group ? 'selected' : '' }}>
                                            {{ ucfirst($group) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Results Per Page:</label>
                                <select class="form-control" name="per_page">
                                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Permissions Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Permissions List
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
                            <th style="width: 5%">ID</th>
                            <th style="width: 15%">Name</th>
                            <th style="width: 20%">Display Name</th>
                            <th style="width: 30%">Description</th>
                            <th style="width: 10%">Roles</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($permissions) > 0)
                            @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>
                                            <span class="badge badge-info">
                                                {{ $permission->name }}
                                            </span>
                                    </td>
                                    <td>{{ $permission->display_name ?? $permission->name }}</td>
                                    <td>{{ Str::limit($permission->description, 50) }}</td>
                                    <td>
                                        @php
                                            $roleCount = DB::table(Config::get('laratrust.tables.permission_role'))
                                                ->where('permission_id', $permission->id)
                                                ->count();
                                        @endphp
                                        <span class="badge badge-primary">
                                                {{ $roleCount }}
                                            </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('permissions.show', $permission->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>

                                            @can('edit_permission')
                                                <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                            @endcan

                                            @can('delete_permission')
                                                @php
                                                    $isSystemPermission = in_array($permission->name, [
                                                        'view_role', 'create_role', 'edit_role', 'delete_role',
                                                        'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
                                                    ]);
                                                @endphp

                                                @if(!$isSystemPermission && $roleCount == 0)
                                                    <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this permission?')">
                                                            <i class="fas fa-trash mr-1"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No permissions found
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Change the card-footer section -->
            <div class="card-footer clearfix">
                <div class="float-right">
                    <!-- Debug info -->
                    @if(method_exists($permissions, 'links'))
                        {{ $permissions->links() }}
                    @else
                        <div class="text-danger">Error: $permissions is not paginated ({{ get_class($permissions) }})</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Permissions Modal -->
    <div class="modal fade" id="generatePermissionsModal" tabindex="-1" role="dialog" aria-labelledby="generatePermissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('permissions.generate') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="generatePermissionsModalLabel">Generate CRUD Permissions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>This will generate standard CRUD permissions for a module (view, create, edit, delete).</p>

                        <div class="form-group">
                            <label for="module_name">Module Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="module_name" name="module_name"
                                   placeholder="Enter module name (e.g. product, user, report)" required>
                            <small class="form-text text-muted">
                                Use lowercase letters, underscores instead of spaces (e.g. product, blog_post)
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="icon fas fa-info-circle"></i>
                            <strong>The following permissions will be generated:</strong>
                            <ul class="mb-0 mt-2" id="permissions-preview">
                                <li>view_[module]</li>
                                <li>create_[module]</li>
                                <li>edit_[module]</li>
                                <li>delete_[module]</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Generate Permissions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Update permissions preview
            $('#module_name').on('input', function() {
                const moduleName = $(this).val();
                if (moduleName) {
                    $('#permissions-preview').html(`
                        <li>view_${moduleName}</li>
                        <li>create_${moduleName}</li>
                        <li>edit_${moduleName}</li>
                        <li>delete_${moduleName}</li>
                    `);
                } else {
                    $('#permissions-preview').html(`
                        <li>view_[module]</li>
                        <li>create_[module]</li>
                        <li>edit_[module]</li>
                        <li>delete_[module]</li>
                    `);
                }
            });
        });
    </script>
@stop
