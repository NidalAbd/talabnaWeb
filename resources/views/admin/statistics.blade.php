@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tachometer-alt text-primary mr-2"></i> Dashboard</h1>
        <div>
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
            <button class="btn btn-outline-success ml-2" onclick="exportDashboard()">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ number_format($totalUsers ?? 0) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-user-plus text-success"></i>
                        {{ number_format($newUsersThisMonth ?? 0) }} new this month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Service Posts</span>
                    <span class="info-box-number">{{ number_format($totalPosts ?? 0) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 85%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-star text-warning"></i>
                        {{ number_format($premiumPosts ?? 0) }} premium posts
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number">${{ number_format($totalRevenue ?? 0, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 70%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-success"></i>
                        {{ number_format($growthRate ?? 0, 1) }}% growth
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Points Sold</span>
                    <span class="info-box-number">{{ number_format($totalPoints ?? 0) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 90%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-exchange-alt text-primary"></i>
                        {{ number_format($monthlyPoints ?? 0) }} this month
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Revenue Trend
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- User Activity Chart -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        User Activity
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="userActivityChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Recent Activity
                    </h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-{{ $activity['icon'] ?? 'circle' }} text-{{ $activity['color'] ?? 'primary' }} mr-2"></i>
                                                <span>{{ $activity['description'] ?? 'Activity' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $activity['user_avatar'] ?? 'img/default-avatar.png' }}" 
                                                     class="img-circle mr-2" width="25" height="25" alt="User">
                                                <span>{{ $activity['user_name'] ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-clock text-muted mr-1"></i>
                                            {{ $activity['time'] ?? 'Just now' }}
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $activity['status_color'] ?? 'success' }}">
                                                {{ $activity['status'] ?? 'Completed' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="alert alert-info m-0">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                No recent activity found
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-user-plus mb-2"></i>
                                <br>Add User
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('service_posts.create') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-plus mb-2"></i>
                                <br>New Post
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('financial.revenue') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-dollar-sign mb-2"></i>
                                <br>Revenue
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('system.health') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-heartbeat mb-2"></i>
                                <br>System Health
                            </a>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-chart-bar mr-2"></i>
                        System Status
                    </h6>
                    
                    <div class="system-status">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Database</span>
                            <span class="badge badge-success">Healthy</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Storage</span>
                            <span class="badge badge-warning">75% Used</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Cache</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Performance</span>
                            <span class="badge badge-info">Good</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Statistics Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="stat-item">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h4>{{ number_format($activeUsers ?? 0) }}</h4>
                                <p class="text-muted">Active Users</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-item">
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <h4>{{ number_format($premiumUsers ?? 0) }}</h4>
                                <p class="text-muted">Premium Users</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-item">
                                <i class="fas fa-coins fa-2x text-success mb-2"></i>
                                <h4>{{ number_format($totalPoints ?? 0) }}</h4>
                                <p class="text-muted">Points Sold</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-item">
                                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                <h4>{{ number_format($growthRate ?? 0, 1) }}%</h4>
                                <p class="text-muted">Growth Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: 0;
    position: relative;
    width: 100%;
}

.info-box .info-box-icon {
    border-radius: 0.25rem 0 0 0.25rem;
    display: flex;
    align-items: center;
    font-size: 1.875rem;
    font-weight: 300;
    justify-content: center;
    text-align: center;
    width: 70px;
    color: #fff;
}

.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box .info-box-text {
    display: block;
    font-size: 1rem;
    font-weight: 400;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box .info-box-number {
    display: block;
    font-weight: 700;
    font-size: 1.25rem;
}

.progress-description {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.table-responsive {
    overflow-x: auto;
}

.img-circle {
    border-radius: 50%;
    object-fit: cover;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.card-tools {
    float: right;
    margin-top: -0.5rem;
}

.card-tools .btn {
    margin-left: 0.25rem;
}

.btn-block {
    display: block;
    width: 100%;
    text-align: center;
    padding: 1rem 0.5rem;
}

.stat-item {
    padding: 1rem;
}

.stat-item h4 {
    margin: 0;
    font-weight: bold;
    color: #333;
}

.system-status {
    font-size: 0.9rem;
}
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 13500, 14200, 15800, 16500, {{ $totalRevenue ?? 17000 }}],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// User Activity Chart
const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
const userActivityChart = new Chart(userActivityCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active Users', 'New Users', 'Premium Users', 'Inactive'],
        datasets: [{
            data: [
                {{ $activeUsers ?? 800 }},
                {{ $newUsersThisMonth ?? 120 }},
                {{ $premiumUsers ?? 150 }},
                {{ ($totalUsers ?? 1000) - ($activeUsers ?? 800) }}
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(0, 123, 255, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(0, 123, 255, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(108, 117, 125, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

function refreshDashboard() {
    location.reload();
}

function exportDashboard() {
    // Implementation for exporting dashboard data
    alert('Exporting dashboard data...');
}
</script>
@endsection
