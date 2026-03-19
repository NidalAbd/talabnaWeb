<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Admin Dashboard'))</title>
    <link rel="icon" href="{{ asset('img/logo.ico') }}">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">{{('layouts\admin.dashboard') }}</a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    {{ Auth::user()->name ?? 'Admin' }}
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <img src="{{ asset('img/logo.ico') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">{{ config('app.name', 'Admin') }}</span>
        </a>
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    {{-- Main --}}
                    <li class="nav-header">MAIN</li>
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a>
                    </li>

                    {{-- Content --}}
                    <li class="nav-header">CONTENT</li>
                    <li class="nav-item">
                        <a href="/categories" class="nav-link"><i class="nav-icon fas fa-th-large"></i><p>Categories</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/subcategories" class="nav-link"><i class="nav-icon fas fa-sitemap"></i><p>Subcategories</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/service_posts" class="nav-link"><i class="nav-icon fas fa-briefcase"></i><p>Service Posts</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/badge-types" class="nav-link"><i class="nav-icon fas fa-certificate"></i><p>Badge Types</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/ai-content" class="nav-link"><i class="nav-icon fas fa-robot"></i><p>AI Content</p></a>
                    </li>

                    {{-- Users --}}
                    <li class="nav-header">USERS</li>
                    <li class="nav-item">
                        <a href="/users" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Users</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/banned-users" class="nav-link"><i class="nav-icon fas fa-user-slash"></i><p>Banned Users</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/banned-devices" class="nav-link"><i class="nav-icon fas fa-mobile-alt"></i><p>Banned Devices</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/reports" class="nav-link"><i class="nav-icon fas fa-flag"></i><p>Reports</p></a>
                    </li>

                    {{-- Points & Payments --}}
                    <li class="nav-header">POINTS & PAYMENTS</li>
                    <li class="nav-item">
                        <a href="/palservice-points" class="nav-link"><i class="nav-icon fas fa-star"></i><p>Points Overview</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/point-packages" class="nav-link"><i class="nav-icon fas fa-gem"></i><p>Point Packages</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/country-pricing" class="nav-link"><i class="nav-icon fas fa-dollar-sign"></i><p>Country Pricing</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/point-purchase-requests" class="nav-link"><i class="nav-icon fas fa-shopping-cart"></i><p>Purchase Requests</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/point-transactions" class="nav-link"><i class="nav-icon fas fa-exchange-alt"></i><p>Transactions</p></a>
                    </li>

                    {{-- Location --}}
                    <li class="nav-header">LOCATION</li>
                    <li class="nav-item">
                        <a href="/countries" class="nav-link"><i class="nav-icon fas fa-globe"></i><p>Countries</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/cities" class="nav-link"><i class="nav-icon fas fa-city"></i><p>Cities</p></a>
                    </li>

                    {{-- Marketing & Analytics --}}
                    <li class="nav-header">MARKETING & ANALYTICS</li>
                    <li class="nav-item">
                        <a href="/marketing-notifications" class="nav-link"><i class="nav-icon fas fa-bullhorn"></i><p>Marketing</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/analytics" class="nav-link"><i class="nav-icon fas fa-chart-bar"></i><p>Analytics</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/statistics" class="nav-link"><i class="nav-icon fas fa-chart-pie"></i><p>Statistics</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/seo" class="nav-link"><i class="nav-icon fas fa-search"></i><p>SEO Analytics</p></a>
                    </li>

                    {{-- Settings --}}
                    <li class="nav-header">SETTINGS</li>
                    <li class="nav-item">
                        <a href="/roles" class="nav-link"><i class="nav-icon fas fa-shield-alt"></i><p>Roles</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/permissions" class="nav-link"><i class="nav-icon fas fa-key"></i><p>Permissions</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/role-assignments" class="nav-link"><i class="nav-icon fas fa-user-tag"></i><p>Role Assignments</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/languages" class="nav-link"><i class="nav-icon fas fa-language"></i><p>Languages</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/translations" class="nav-link"><i class="nav-icon fas fa-file-alt"></i><p>Translations</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/auto-translate" class="nav-link"><i class="nav-icon fas fa-robot"></i><p>Auto-Translate (AI)</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/subscription-plans" class="nav-link"><i class="nav-icon fas fa-crown"></i><p>Subscription Plans</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="/command-monitor" class="nav-link"><i class="nav-icon fas fa-terminal"></i><p>Command Monitor</p></a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{('layouts\admin.dashboard') }}</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @include('partials.alert')
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer text-center">
        <strong>&copy; {{ date('Y') }} {{ config('app.name', 'Admin') }}.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- AdminLTE Scripts -->
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html> 






