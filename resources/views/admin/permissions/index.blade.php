@extends('adminlte::page')

@section('title', 'Permissions Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-key text-primary mr-2"></i> Permissions Management</h1>
        <div>
            <a href="{{ route('permissions.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Permission
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Permissions Table -->
    <x-admin.table
        title="Permissions List"
        :data="$permissions"
        :columns="[
            ['label' => 'ID', 'field' => 'id', 'render' => function($permission) {
                return '<span class="badge badge-secondary">' . $permission->id . '</span>';
            }],
            ['label' => 'Name', 'field' => 'name', 'render' => function($permission) {
                return '<strong>' . $permission->name . '</strong>';
            }],
            ['label' => 'Display Name', 'field' => 'display_name', 'render' => function($permission) {
                return '<span class="text-muted">' . ($permission->display_name ?? '') . '</span>';
            }],
            ['label' => 'Description', 'field' => 'description', 'render' => function($permission) {
                return '<span class="text-muted">' . Str::limit($permission->description, 50) . '</span>';
            }],
            ['label' => 'Roles', 'field' => 'id', 'render' => function($permission) {
                $count = $permission->roles->count();
                return '<span class="badge badge-primary">' . $count . '</span>';
            }],
            ['label' => 'Users', 'field' => 'id', 'render' => function($permission) {
                $count = $permission->users->count();
                return '<span class="badge badge-info">' . $count . '</span>';
            }],
            ['label' => 'Created', 'field' => 'created_at', 'render' => function($permission) {
                return '<span class="text-muted">' . ($permission->created_at ? $permission->created_at->format('Y-m-d') : '-') . '</span>';
            }],
        ]"
        :filters="[
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by name...'],
        ]"
        :actions="function($permission) {
            return '
                <a href="' . route('permissions.show', $permission->id) . '" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                <a href="' . route('permissions.edit', $permission->id) . '" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="' . route('permissions.destroy', $permission->id) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            ';
        }"
    />
</div>
@stop
