@extends('adminlte::page')

@section('title', 'User Role Assignments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-cog text-primary mr-2"></i> User Role Assignments</h1>
        <div>
            @permission('view_role')
            <a href="{{ route('roles.index') }}" class="btn btn-info">
                <i class="fas fa-user-tag mr-1"></i> Manage Roles
            </a>
            @endpermission
            @permission('view_permission')
            <a href="{{ route('permissions.index') }}" class="btn btn-success ml-2">
                <i class="fas fa-key mr-1"></i> Manage Permissions
            </a>
            @endpermission
            <a href="{{ route('users.index') }}" class="btn btn-primary ml-2">
                <i class="fas fa-users mr-1"></i> User Management
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Modern Filter Section -->
        <div class="card card-outline card-light mb-3 shadow-sm">
            <div class="card-body py-2">
                <form action="{{ route('role-assignments.index') }}" method="GET" id="filter-form" class="row align-items-end">
                    <!-- Search Input -->
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                   placeholder="Search name or email...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-3">
                        <select class="form-control" name="role">
                            <option value="">All Roles</option>
                            @foreach(App\Models\Role::orderBy('name')->get() as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort & Direction in Combined Dropdown -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <select class="form-control" name="sort">
                                <option value="id" {{ request('sort') == 'id' || !request('sort') ? 'selected' : '' }}>Sort by ID</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Sort by Name</option>
                                <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Sort by Email</option>
                                <option value="roles_count" {{ request('sort') == 'roles_count' ? 'selected' : '' }}>Sort by Roles Count</option>
                            </select>
                            <select class="form-control" name="direction">
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                <option value="desc" {{ request('direction') == 'desc' || !request('direction') ? 'selected' : '' }}>Descending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-2 text-right">
                        <a href="{{ route('role-assignments.index') }}" class="btn btn-default">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main User Roles Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    User Role Assignments
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width: 5%">ID</th>
                            <th style="width: 20%">User</th>
                            <th style="width: 20%">Email</th>
                            <th style="width: 20%">Roles</th>
                            <th style="width: 15%">Direct Permissions</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($users) > 0)
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="user-block">
                                            @if($user->photos && $user->photos->count() > 0)
                                                @php
                                                    $photo = $user->photos->first();
                                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img class="img-circle img-bordered-sm" src="{{ $imgSrc }}" alt="{{ $user->name }}">
                                            @else
                                                <img class="img-circle img-bordered-sm" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="User Image">
                                            @endif
                                            <span class="username">
                                                    {{ $user->name }}
                                                    <small>{{ $user->user_name ?? '' }}</small>
                                                </span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles && $user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-{{ $role->name == 'superadmin' ? 'danger' : ($role->name == 'admin' ? 'warning' : 'info') }} mb-1">
                                                        {{ $role->display_name ?? $role->name }}
                                                    </span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">No Roles Assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                            <span class="badge badge-primary">
                                                {{ $user->permissions_count }} Permissions
                                            </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @permission('edit_role')
                                            <a href="{{ route('role-assignments.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-user-tag mr-1"></i> Assign Roles
                                            </a>
                                            @endpermission

                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View User
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No users found
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
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(function() {
            // Auto-submit the filter form when any select element changes
            $('form select[name="sort"], form select[name="direction"], form select[name="role"]').on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@stop
