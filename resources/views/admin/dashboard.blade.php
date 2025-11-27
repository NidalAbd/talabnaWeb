@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div id="admin-app">
        <admin-dashboard></admin-dashboard>
    </div>
@stop

@section('css')
    {{-- Add any extra CSS here --}}
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
