@extends('adminlte::page')

@section('title', 'Roles Management (Vue.js)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-tag text-primary mr-2"></i> Roles Management</h1>
        <div>
            <a href="{{ route('permissions.index') }}" class="btn btn-success">
                <i class="fas fa-key mr-1"></i> Manage Permissions
            </a>
            <a href="{{ route('roles.create') }}" class="btn btn-primary ml-2">
                <i class="fas fa-plus-circle mr-1"></i> Create New Role
            </a>
        </div>
    </div>
@stop

@section('content')
    <div id="admin-app">
        <roles-list></roles-list>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
