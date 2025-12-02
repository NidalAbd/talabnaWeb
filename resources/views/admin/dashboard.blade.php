@extends('adminlte::page')

@section('title', 'Admin Panel')

@section('content')
    <div id="admin-app">
        <router-view></router-view>
    </div>
@stop

@section('css')
    {{-- Add any extra CSS here --}}
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
