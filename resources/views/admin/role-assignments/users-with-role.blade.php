@extends('adminlte::page')

@section('title', 'Users with Role')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users text-primary mr-2"></i> Users with Role: {{ $role->field</h1>
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
        <!-- Role Info Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('admin\role-assignments\users-with-role.role_information') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('admin\role-assignments\users-with-role.name_') }}</strong> {{ $role->field</p>
                                <p class="mb-1"><strong>{{ __('admin\role-assignments\users-with-role.display_name_') }}</strong> {{ $role->field</p>
                            </div>
                            <div class="col-md-8">
                                <p class="mb-1"><strong>{{ __('admin\role-assignments\users-with-role.description_') }}</strong> {{ $role->description ?? 'No description available' }}</p>
                                <p class="mb-1">
                                    <strong>{{ __('admin\role-assignments\users-with-role.permissions_') }}</strong>
                                    <span class="badge badge-primary">{{ $role->field</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users with Role Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i>
                    Users with Role: {{ $role->display_name ?? $role->name }}
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
                            <th style="width: 5%">{{ __('admin\role-assignments\users-with-role.id') }}</th>
                            <th style="width: 20%">{{ __('admin\role-assignments\users-with-role.user') }}</th>
                            <th style="width: 20%">{{ __('admin\role-assignments\users-with-role.email') }}</th>
                            <th style="width: 15%">{{ __('admin\role-assignments\users-with-role.status') }}</th>
                            <th style="width: 15%">{{ __('admin\role-assignments\users-with-role.all_roles') }}</th>
                            <th style="width: 25%">{{ __('admin\role-assignments\users-with-role.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($users) > 0)
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->field</td>
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
                                                    <small>{{ $user->field</small>
                                                </span>
                                        </div>
                                    </td>
                                    <td>{{ $user->field</td>
                                    <td>
                                            <span class="badge badge-{{ $user->is_active == 'active' ? 'success' : ($user->is_active == 'banned' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($user->is_active) }}
                                            </span>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $userRole)
                                            <span class="badge badge-{{ $userRole->name == 'superadmin' ? 'danger' : ($userRole->name == 'admin' ? 'warning' : 'info') }} mb-1">
                                                    {{ $userRole->display_name ?? $userRole->name }}
                                                </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View User
                                            </a>

                                            @can('edit_role')
                                                <a href="{{ route('role-assignments.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-user-tag mr-1"></i> Edit Roles
                                                </a>
                                            @endcan

                                            @can('edit_user')
                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit mr-1"></i> Edit User
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No users found with this role
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
