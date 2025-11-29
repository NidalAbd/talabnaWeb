@extends('adminlte::page')

@section('title', 'Permissions Management (Vue.js)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-key text-primary mr-2"></i> Permissions Management</h1>
        <div>
            <a href="{{ route('roles.index') }}" class="btn btn-info">
                <i class="fas fa-user-tag mr-1"></i> Manage Roles
            </a>
        </div>
    </div>
@stop

@section('content')
    <div id="admin-app">
        <permissions-list></permissions-list>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
