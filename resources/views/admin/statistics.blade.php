@extends('adminlte::page')

@section('title', 'Dashboard Statistics')

@section('content_header')
    @include('partials.breadcrumbs')

    <h1><i class="fas fa-chart-bar text-primary mr-2"></i> Dashboard Overview</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Info Boxes -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Users</span>
                        <span class="info-box-number">{{ $user }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Purchase Requests</span>
                        <span class="info-box-number">{{ $purchaseRequests }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-tools text-white"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Services</span>
                        <span class="info-box-number">{{ $allService }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-gem"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Premium Services</span>
                        <span class="info-box-number">{{ $allDiamond + $allGolden }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Service Badges -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-award mr-1"></i>
                            Service Badges
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="progress-group mb-3">
                            <span class="progress-text">Diamond</span>
                            <span class="float-right"><b>{{ $allDiamond }}</b>/{{ $allService }}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: {{ ($allDiamond / $allService) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="progress-group mb-3">
                            <span class="progress-text">Golden</span>
                            <span class="float-right"><b>{{ $allGolden }}</b>/{{ $allService }}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning" style="width: {{ ($allGolden / $allService) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="progress-group">
                            <span class="progress-text">Normal</span>
                            <span class="float-right"><b>{{ $allNormal }}</b>/{{ $allService }}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-secondary" style="width: {{ ($allNormal / $allService) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Categories -->
            <div class="col-md-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            Service Categories
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $categories = [
                                    ['name' => 'Devices', 'count' => $allPhone, 'icon' => 'fas fa-mobile-alt', 'color' => 'primary'],
                                    ['name' => 'Cars', 'count' => $allCar, 'icon' => 'fas fa-car', 'color' => 'success'],
                                    ['name' => 'Jobs', 'count' => $allJobs, 'icon' => 'fas fa-briefcase', 'color' => 'warning'],
                                    ['name' => 'Real Estate', 'count' => $allRealState, 'icon' => 'fas fa-building', 'color' => 'info'],
                                    ['name' => 'Services', 'count' => $allGeneral, 'icon' => 'fas fa-tools', 'color' => 'danger']
                                ];
                            @endphp
                            @foreach($categories as $category)
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="small-box bg-{{ $category['color'] }}">
                                        <div class="inner">
                                            <h3>{{ $category['count'] }}</h3>
                                            <p>{{ $category['name'] }}</p>
                                        </div>
                                        <div class="icon">
                                            <i class="{{ $category['icon'] }}"></i>
                                        </div>
                                        <a href="#" class="small-box-footer">
                                            More info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales & Revenue Analysis -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-info card-outline">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Service Performance
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                            <p class="text-success text-xl">
                                <i class="ion ion-ios-refresh-empty"></i>
                            </p>
                            <p class="d-flex flex-column text-right">
                            <span class="font-weight-bold">
                                <i class="ion ion-android-arrow-up text-success"></i> {{ $purchaseRequests }}
                            </span>
                                <span class="text-muted">PURCHASE REQUESTS</span>
                            </p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <p class="text-danger text-xl">
                                <i class="ion ion-ios-people-outline"></i>
                            </p>
                            <p class="d-flex flex-column text-right">
                            <span class="font-weight-bold">
                                <i class="ion ion-android-arrow-up text-danger"></i> {{ $allDiamond + $allGolden }}
                            </span>
                                <span class="text-muted">PREMIUM SERVICES</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Service Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <canvas id="serviceDistribution" style="height: 180px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .icon {
            font-size: 70px;
            opacity: 0.2;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            // Service Distribution Chart
            const ctx = document.getElementById('serviceDistribution').getContext('2d');
            const serviceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Diamond', 'Golden', 'Normal'],
                    datasets: [{
                        data: [{{ $allDiamond }}, {{ $allGolden }}, {{ $allNormal }}],
                        backgroundColor: ['#007bff', '#ffc107', '#6c757d'],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
    </script>
@stop
