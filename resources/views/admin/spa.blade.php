@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('content_header')
    <!-- This will be handled by Vue Router -->
@stop

@section('content')
    <div id="admin-app">
        <!-- Vue Router will render components here -->
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
    <script>
        // Set app name for Vue Router
        window.appName = '{{ config('app.name', 'Admin Dashboard') }}'
    </script>
@stop

@section('css')
    <style>
        /* Ensure smooth transitions */
        #admin-app {
            min-height: 500px;
        }

        /* Loading state */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
@stop
