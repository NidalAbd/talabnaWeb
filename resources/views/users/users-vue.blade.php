@extends('adminlte::page')

@section('title', 'Users Management (Vue.js)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users text-primary mr-2"></i> Users Management</h1>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add User
            </a>
        </div>
    </div>
@stop

@section('content')
    <div id="admin-app">
        <users-list></users-list>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
