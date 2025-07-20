@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-shield text-primary mr-2"></i> Roles Management</h1>
        <div>
            <a href="{{ route('roles.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Role
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Roles Table -->
    <x-admin.table
        title="Roles List"
        :data="$roles"
        :columns="[
            ['label' => 'ID', 'field' => 'id', 'render' => function($role) {
                return '<span class="badge badge-secondary">' . $role->id . '</span>';
            }],
            ['label' => 'Name', 'field' => 'name', 'render' => function($role) {
                return '<strong>' . ucfirst($role->name) . '</strong><br><small class="text-muted">' . ($role->display_name ?? '') . '</small>';
            }],
            ['label' => 'Description', 'field' => 'description', 'render' => function($role) {
                return '<span class="text-muted">' . Str::limit($role->description, 50) . '</span>';
            }],
            ['label' => 'Users', 'field' => 'id', 'render' => function($role) {
                $count = $role->users->count();
                return '<span class="badge badge-primary">' . $count . '</span>';
            }],
            ['label' => 'Permissions', 'field' => 'id', 'render' => function($role) {
                $count = $role->permissions->count();
                return '<span class="badge badge-info">' . $count . '</span>';
            }],
            ['label' => 'Created', 'field' => 'created_at', 'render' => function($role) {
                return '<span class="text-muted">' . ($role->created_at ? $role->created_at->format('Y-m-d') : '-') . '</span>';
            }],
        ]"
        :filters="[
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by name...'],
        ]"
        :actions="function($role) {
            return '
                <a href="' . route('roles.show', $role->id) . '" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                <a href="' . route('roles.edit', $role->id) . '" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="' . route('roles.destroy', $role->id) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            ';
        }"
    />
</div>
@stop
