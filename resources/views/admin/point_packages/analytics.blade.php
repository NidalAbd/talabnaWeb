@extends('adminlte::page')

@section('title', 'Point Package Analytics')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-bar text-info mr-2"></i> Point Package Analytics</h1>
        <a href="{{ route('point_packages.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Packages
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Package Sales Overview -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-box mr-2"></i> Package Sales Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{('admin\point_packages\analytics.package_name') }}</th>
                                        <th>{{('admin\point_packages\analytics.points') }}</th>
                                        <th>{{('admin\point_packages\analytics.price') }}</th>
                                        <th>{{('admin\point_packages\analytics.sales_count') }}</th>
                                        <th>{{('admin\point_packages\analytics.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packageSales as $package)
                                        <tr>
                                            <td>
                                                @if(is_array($package->name))
                                                    {{ $package->name['en'] ?? 'N/A' }}
                                                @else
                                                    {{ $package->name }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ number_format($package->points_amount) }} pts
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    ${{ number_format($package->price, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $package->sales_count }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($package->is_active)
                                                    <span class="badge badge-success">{{('admin\point_packages\analytics.active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{('admin\point_packages\analytics.inactive') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales Chart -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i> Monthly Sales
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($monthlySales->count() > 0)
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>{{('admin\point_packages\analytics.no_sales_data_available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $packageSales->id</h3> }}
                        <p>{{('admin\point_packages\analytics.total_packages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $packageSales->id</h3> }}
                        <p>{{('admin\point_packages\analytics.active_packages') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $packageSales->id</h3> }}
                        <p>{{('admin\point_packages\analytics.total_sales') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($monthlySales->id</h3> ) }}
                        <p>{{('admin\point_packages\analytics.total_points_sold') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @if($monthlySales->count() > 0)
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlyData = @json($monthlySales);
        
        const labels = monthlyData.map(item => {
            const monthNames = [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];
            return monthNames[item.month - 1];
        });
        
        const data = monthlyData.map(item => item.total_points);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Points Sold',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        @endif
    </script>
@stop 






