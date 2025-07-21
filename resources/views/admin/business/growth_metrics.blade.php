@extends('adminlte::page')

@section('title', 'Growth Metrics')

@section('content_header')
    <h1>Growth Metrics</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Growth Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $userGrowth->sum('new_users') }}</h3>
                    <p>Total New Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($revenueGrowth->sum('revenue'), 2) }}</h3>
                    <p>Total Revenue Generated</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pointSalesGrowth->sum('sales_count') }}</h3>
                    <p>Total Point Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($pointSalesGrowth->sum('total_points')) }}</h3>
                    <p>Total Points Sold</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Growth Trend</h3>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue Growth Trend</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueGrowthChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Point Sales Growth -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Point Sales Growth</h3>
                </div>
                <div class="card-body">
                    <canvas id="pointSalesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Growth Summary</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Monthly Users</span>
                            <span class="info-box-number">
                                {{ number_format($userGrowth->avg('new_users'), 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Monthly Revenue</span>
                            <span class="info-box-number">
                                ${{ number_format($revenueGrowth->avg('revenue'), 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Monthly Sales</span>
                            <span class="info-box-number">
                                {{ number_format($pointSalesGrowth->avg('sales_count'), 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Growth Rate</span>
                            <span class="info-box-number">
                                @php
                                    $firstMonth = $userGrowth->first();
                                    $lastMonth = $userGrowth->last();
                                    $growthRate = $firstMonth && $lastMonth && $firstMonth->new_users > 0 ? 
                                        (($lastMonth->new_users - $firstMonth->new_users) / $firstMonth->new_users * 100) : 0;
                                @endphp
                                {{ number_format($growthRate, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Analysis Tables -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly User Growth</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>New Users</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userGrowth as $growth)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $growth->month)->format('M Y') }}</td>
                                    <td>{{ number_format($growth->new_users) }}</td>
                                    <td>
                                        @php
                                            $prevMonth = $userGrowth->where('month', '<', $growth->month)->last();
                                            $growthPercent = $prevMonth && $prevMonth->new_users > 0 ? 
                                                (($growth->new_users - $prevMonth->new_users) / $prevMonth->new_users * 100) : 0;
                                        @endphp
                                        <span class="badge badge-{{ $growthPercent >= 0 ? 'success' : 'danger' }}">
                                            {{ $growthPercent >= 0 ? '+' : '' }}{{ number_format($growthPercent, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No user growth data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Revenue Growth</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Revenue</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueGrowth as $growth)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $growth->month)->format('M Y') }}</td>
                                    <td>${{ number_format($growth->revenue, 2) }}</td>
                                    <td>
                                        @php
                                            $prevMonth = $revenueGrowth->where('month', '<', $growth->month)->last();
                                            $growthPercent = $prevMonth && $prevMonth->revenue > 0 ? 
                                                (($growth->revenue - $prevMonth->revenue) / $prevMonth->revenue * 100) : 0;
                                        @endphp
                                        <span class="badge badge-{{ $growthPercent >= 0 ? 'success' : 'danger' }}">
                                            {{ $growthPercent >= 0 ? '+' : '' }}{{ number_format($growthPercent, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No revenue growth data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Point Sales</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Sales</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointSalesGrowth as $growth)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $growth->month)->format('M Y') }}</td>
                                    <td>{{ number_format($growth->sales_count) }}</td>
                                    <td>{{ number_format($growth->total_points) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No point sales data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Growth Insights & Recommendations</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb text-warning"></i> Key Insights</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Total Users: {{ number_format($userGrowth->sum('new_users')) }}</li>
                                <li><i class="fas fa-check text-success"></i> Total Revenue: ${{ number_format($revenueGrowth->sum('revenue'), 2) }}</li>
                                <li><i class="fas fa-check text-success"></i> Total Sales: {{ number_format($pointSalesGrowth->sum('sales_count')) }}</li>
                                <li><i class="fas fa-check text-success"></i> Total Points: {{ number_format($pointSalesGrowth->sum('total_points')) }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-chart-line text-primary"></i> Growth Recommendations</h5>
                            <ul class="list-unstyled">
                                @php
                                    $avgUserGrowth = $userGrowth->avg('new_users');
                                    $avgRevenueGrowth = $revenueGrowth->avg('revenue');
                                @endphp
                                
                                @if($avgUserGrowth < 100)
                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Focus on user acquisition strategies</li>
                                @else
                                    <li><i class="fas fa-thumbs-up text-success"></i> Strong user growth - maintain momentum</li>
                                @endif
                                
                                @if($avgRevenueGrowth < 1000)
                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Implement revenue optimization strategies</li>
                                @else
                                    <li><i class="fas fa-thumbs-up text-success"></i> Good revenue growth - consider expansion</li>
                                @endif
                                
                                <li><i class="fas fa-chart-bar text-info"></i> Monitor growth trends monthly</li>
                                <li><i class="fas fa-users text-primary"></i> Focus on user retention and engagement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Comparison -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Growth Comparison</h3>
                </div>
                <div class="card-body">
                    <canvas id="growthComparisonChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        margin-bottom: 20px;
    }
    .card {
        margin-bottom: 20px;
    }
    .info-box {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthChart = new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: @json($userGrowth->pluck('month')->map(function($month) { return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'); })),
            datasets: [{
                label: 'New Users',
                data: @json($userGrowth->pluck('new_users')),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Revenue Growth Chart
    const revenueGrowthCtx = document.getElementById('revenueGrowthChart').getContext('2d');
    const revenueGrowthChart = new Chart(revenueGrowthCtx, {
        type: 'line',
        data: {
            labels: @json($revenueGrowth->pluck('month')->map(function($month) { return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'); })),
            datasets: [{
                label: 'Revenue',
                data: @json($revenueGrowth->pluck('revenue')),
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Point Sales Chart
    const pointSalesCtx = document.getElementById('pointSalesChart').getContext('2d');
    const pointSalesChart = new Chart(pointSalesCtx, {
        type: 'bar',
        data: {
            labels: @json($pointSalesGrowth->pluck('month')->map(function($month) { return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'); })),
            datasets: [{
                label: 'Sales Count',
                data: @json($pointSalesGrowth->pluck('sales_count')),
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 1
            }, {
                label: 'Total Points',
                data: @json($pointSalesGrowth->pluck('total_points')),
                backgroundColor: '#dc3545',
                borderColor: '#dc3545',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Growth Comparison Chart
    const comparisonCtx = document.getElementById('growthComparisonChart').getContext('2d');
    const comparisonChart = new Chart(comparisonCtx, {
        type: 'line',
        data: {
            labels: @json($userGrowth->pluck('month')->map(function($month) { return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'); })),
            datasets: [{
                label: 'User Growth',
                data: @json($userGrowth->pluck('new_users')),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Revenue Growth',
                data: @json($revenueGrowth->pluck('revenue')),
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>
@stop 