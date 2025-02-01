@php
    $user = auth()->user()
@endphp
    <!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}@yield('title')</title>
    <link rel="icon" href="{{ asset('images/maxpeak.ico') }}">
    <!-- Scripts -->
    <!-- jQuery and Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Other body tags -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- App scripts and styles -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
</head>

<body class="hold-transition sidebar-mini">

<div id="app">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#">
                        <i class="fa fa-bars fa-2x"></i>
                    </a>
                </li>
            </ul>

            <form class="form-inline ml-3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" placeholder="Search"
                           aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fa fa-search fa-lg"></i>
                        </button>
                    </div>
                </div>
            </form>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center">
                    <span class=""><strong>{{ auth()->user()->pointsBalance ?? 0 }}</strong></span>
                    <a href="{{ route('purchase_points.create') }}" class="btn btn-sm btn-primary ml-3">
                        <i class="fas fa-plus"></i>
                    </a>
                </li>
                &nbsp;&nbsp;&nbsp;


                <li class="nav-item d-flex align-items-center text-black">
                    <a href="{{ route('post.profile', auth()->user()->id) }}"
                       class="d-flex align-items-center text-black">
                <span class="text-black mr-2">
                    {{ auth()->user()->user_name ?? '' }}
                </span>
                        <!-- Main Sidebar Container -->
                        @if ($user && $user->photos && $user->photos->count() > 0)
                            <img src="{{ asset('storage/' . $user->photos->first()->src) }}"
                                 style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;"
                                 class="img-circle elevation-2 mr-2 text-black"
                                 alt="Profile Picture">
                        @else
                            <img src="{{ asset('storage/photos/avatar1.png') }}"
                                 style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;"
                                 class="img-circle elevation-2 mr-2"
                                 alt="Profile Picture">
                        @endif

                        <span class="text-muted mr-2">
                  @if (auth()->check() && auth()->user()->roles)
                                <span class="text-muted mr-2">
        {{ auth()->user()->roles->pluck('name')->implode(', ') }}
    </span>
                            @endif

                </span>
                    </a>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar  sidebar-dark-blue">
            <a href="/" class=" nav-pills nav-sidebar flex-column d-flex justify-content-center align-items-center">
                <img src="{{asset('img/logo.png')}}" class="brand img-circle elevation-2" style="opacity: .8; height: 47px;" alt=""><strong>
                </strong>
            </a>
            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @permission('view_statistics')
                        <li class="nav-item has-treeview menu-open">
                            <a href="{{route('statistics.index')}}" class="nav-link ">
                                <i class="nav-icon fas fa-tachometer-alt green"></i>
                                <p class="white text-bold"> Dashboard</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('user_index')
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Admin Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @permission('user_index')
                                <li class="nav-item">
                                    <a href="{{route('users.data')}}" class="nav-link">
                                        <i class="fas fa-users-cog nav-icon"></i>
                                        <p>Users</p>
                                    </a>
                                </li>
                                @endpermission
                                @permission('purchase_index')
                                <li class="nav-item">
                                    <a href="{{route('purchase_points.index')}}" class="nav-link">
                                        <i class="fas fa-users-cog nav-icon"></i>
                                        <p>orders</p>
                                    </a>
                                </li>
                                @endpermission
                                @permission('purchase_index')
                                <li class="nav-item">
                                    <a href="{{route('palservice_points.index')}}" class="nav-link">
                                        <i class="fas fa-users-cog nav-icon"></i>
                                        <p>Owned Point</p>
                                    </a>
                                </li>
                                @endpermission
                                @permission('purchase_index')
                                <li class="nav-item">
                                    <a href="{{route('point_transactions.index')}}" class="nav-link">
                                        <i class="fas fa-users-cog nav-icon"></i>
                                        <p>Transaction</p>
                                    </a>
                                </li>
                                @endpermission
                                @permission('user_index')
                                <li class="nav-item">
                                    <a href="{{route('service_posts.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-home blue"></i>
                                        <p class="white text-bold">All Service</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{route('job.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-user-secret blue"></i>
                                        <p class="white text-bold">Jobs</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{route('phone.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-phone-square"></i>
                                        <p class="white text-bold">Devices</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{route('realStat.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-building"></i>
                                        <p class="white text-bold">Real Estate</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{route('car.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-car"></i>
                                        <p class="white text-bold">Cars</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{route('generals.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-shopping-basket"></i>
                                        <p class="white text-bold">General</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{route('categories.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-shopping-basket"></i>
                                        <p class="white text-bold">Category</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{route('indexSubCategory.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-shopping-basket"></i>
                                        <p class="white text-bold">SubCategory</p>
                                    </a>
                                </li>
                                @endpermission
                                <li class="nav-item">
                                    <a href="{{route('reports.index')}}" class="nav-link">
                                        <i class="nav-icon fas fa-flag"></i>
                                        <p class="white text-bold">Reports</p>
                                    </a>
                                </li>

                            </ul>
                        </li>
                        @endpermission
                        @permission('show_profile')
                        @endpermission
                        @if (auth()->check())
                            <li class="nav-item">
                                <a href="{{ route('post.profile', auth()->user()->id) }}" class="nav-link">
                                    <i class="nav-icon fas fa-user-circle"></i>
                                    <p class="white text-bold">Posts Table</p>
                                </a>
                            </li>
                            {{--                            <li class="nav-item">--}}
                            {{--                                <a href="{{ route('user.profile', auth()->user()->id) }}" class="nav-link">--}}
                            {{--                                    <i class="nav-icon fas fa-user-circle"></i>--}}
                            {{--                                    <p class="white text-bold">Profile</p>--}}
                            {{--                                </a>--}}
                            {{--                            </li>--}}

                            {{--                        <li class="nav-item">--}}
                            {{--                            <a href="{{route('user_favorites.index', auth()->user()->id)}}" class="nav-link">--}}
                            {{--                                <i class="nav-icon fas fa-bookmark"></i>--}}
                            {{--                                <p class="white text-bold">Favourite</p>--}}
                            {{--                            </a>--}}
                            {{--                        </li>--}}
                            <li class="nav-item">
                                <a href="{{route('users.edit',$user->id)}}" class="nav-link">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p class="white text-bold">User setting</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="true">
                        <li class="nav-item ">
                            <a class="nav-link" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();
                             ">
                                <i class="nav-icon fas fa-power-off red"></i>
                                <p>Logout</p>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </a>
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
            <!-- /.content-header -->
            <div class="py-1">

                <!-- Content Header (Page header) -->
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <ol class="breadcrumb float-sm-left">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">@yield('title')</li>

                                </ol>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>
                <!-- /.content-header -->

                <!-- Main content -->
                <div class="content">
                    <div class="container-fluid">
                        @include('partials.alert')
                        @yield('content')
                    </div><!-- /.container-fluid -->
                </div>
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <!-- Main Footer -->
        <footer class="main-footer">
            <!-- To the right -->

            <!-- Default to the left -->
            <strong>{{ config('app.name', 'PalService') }} Copyright &copy; 2023-2024

            </strong>
        </footer>
    </div>
</div>

<!-- ./wrapper -->
@yield('script')
</body>
</html>

