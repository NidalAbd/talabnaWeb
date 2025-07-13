@extends('adminlte::page')
@section('title', 'Investor Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line"></i> Investor Dashboard</h1>
        <div>
            <a href="{{ route('investor.financial-report') }}" class="btn btn-primary">
                <i class="fas fa-file-invoice-dollar"></i> Financial Report
            </a>
            <a href="{{ route('investor.business-metrics') }}" class="btn btn-success">
                <i class="fas fa-chart-bar"></i> Business Metrics
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Key Performance Indicators -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalRevenue, 2) }}</h3>
                    <p>Total Revenue</p>
                    <small class="text-white">
                        <i class="fas fa-{{ $revenueGrowthRate >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($revenueGrowthRate), 1) }}% from last month
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalUsers) }}</h3>
                    <p>Total Users</p>
                    <small class="text-white">
                        <i class="fas fa-{{ $userGrowthRate >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($userGrowthRate), 1) }}% growth
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($totalPointsInSystem) }}</h3>
                    <p>Points in System</p>
                    <small class="text-white">
                        <i class="fas fa-{{ $pointsGrowthRate >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ number_format(abs($pointsGrowthRate), 1) }}% growth
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($publishedPosts) }}</h3>
                    <p>Published Posts</p>
                    <small class="text-white">
                        {{ $premiumPosts }} premium posts
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Monthly Revenue Trend
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> User Growth Trend
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Health Indicators -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat"></i> Business Health Metrics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-user-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">User Retention</span>
                                    <span class="info-box-number">{{ number_format($businessHealth['user_retention_rate'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Post Engagement</span>
                                    <span class="info-box-number">{{ number_format($businessHealth['post_engagement_rate'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-star"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Premium Adoption</span>
                                    <span class="info-box-number">{{ number_format($businessHealth['premium_adoption_rate'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Point Utilization</span>
                                    <span class="info-box-number">{{ number_format($businessHealth['point_utilization_rate'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Top Performing Categories
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Posts</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCategories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->post_count }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ ($category->post_count / $publishedPosts) * 100 }}%">
                                                {{ number_format(($category->post_count / $publishedPosts) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Financial Performance Metrics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ number_format($financialMetrics['average_revenue_per_user'], 2) }}</h4>
                                <p class="text-muted">Average Revenue per User</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($financialMetrics['revenue_per_post'], 2) }}</h4>
                                <p class="text-muted">Revenue per Post</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ number_format($monthlyRevenue, 2) }}</h4>
                                <p class="text-muted">This Month's Revenue</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ number_format($newUsersThisMonth) }}</h4>
                                <p class="text-muted">New Users This Month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .info-box {
        margin-bottom: 15px;
    }
    .progress {
        height: 20px;
    }
    .progress-bar {
        line-height: 20px;
        font-size: 12px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json(array_column($monthlyRevenueData, 'month')),
            datasets: [{
                label: 'Monthly Revenue',
                data: @json(array_column($monthlyRevenueData, 'revenue')),
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
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const userGrowthChart = new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: @json(array_column($userGrowthData, 'month')),
            datasets: [{
                label: 'New Users',
                data: @json(array_column($userGrowthData, 'users')),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgb(54, 162, 235)',
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
            }
        }
    });
});
</script>
@stop 