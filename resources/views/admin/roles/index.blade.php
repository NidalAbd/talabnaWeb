@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-tag text-primary mr-2"></i> Roles Management</h1>
        <div>
            @can('create_role')
                <a href="{{ route('roles.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle mr-1"></i> Create New Role
                </a>
            @endcan
            @can('view_permission')
                <a href="{{ route('permissions.index') }}" class="btn btn-info ml-2">
                    <i class="fas fa-key mr-1"></i> Manage Permissions
                </a>
            @endcan
            <a href="{{ route('role-assignments.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-user-cog mr-1"></i> Assign Roles to Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Role Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $roles->total() }}</h3>
                        <p>Total Roles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $roles->sum('permissions_count') }}</h3>
                        <p>Total Permission Assignments</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $roles->where('name', '!=', 'user')->count() }}</h3>
                        <p>Administrative Roles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ App\Models\User::whereHas('roles', function($q) { $q->where('name', 'admin'); })->count() }}</h3>
                        <p>Admin Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-cog"></i>
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
                <form action="{{ route('roles.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Search by Name or Description:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search roles...">
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
                                <label>Sort By:</label>
                                <select class="form-control" name="sort_by">
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Creation Date</option>
                                    <option value="permissions_count" {{ request('sort_by') == 'permissions_count' ? 'selected' : '' }}>Permissions Count</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sort Direction:</label>
                                <select class="form-control" name="sort_dir">
                                    <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Roles Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Roles List
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
                            <th style="width: 25%">Description</th>
                            <th style="width: 10%">Permissions</th>
                            <th style="width: 25%">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($roles) > 0)
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>
                                            <span class="badge badge-{{ $role->name == 'superadmin' ? 'danger' : ($role->name == 'admin' ? 'warning' : 'info') }}">
                                                {{ $role->name }}
                                            </span>
                                    </td>
                                    <td>{{ $role->display_name ?? $role->name }}</td>
                                    <td>{{ Str::limit($role->description, 50) }}</td>
                                    <td>
                                            <span class="badge badge-primary">
                                                {{ $role->permissions_count }}
                                            </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>

                                            <a href="{{ route('role-assignments.users-with-role', $role->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-users mr-1"></i> Users
                                            </a>

                                            @can('edit_role')
                                                @if($role->name != 'superadmin' && $role->name != 'admin')
                                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit mr-1"></i> Edit
                                                    </a>
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
                                        <i class="fas fa-info-circle mr-1"></i> No roles found
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Confirm delete
            $('.btn-danger[type="submit"]').click(function(e) {
                if(!confirm('Are you sure you want to delete this role?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@stop

@can('delete_role')
    @if($role->name != 'superadmin' && $role->name != 'admin' && $role->name != 'user')
        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this role?')">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </form>
    @endif
@endcan
