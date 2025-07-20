@extends('adminlte::page')

@section('title', 'User Analytics')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users text-primary mr-2"></i> User Analytics</h1>
        <div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Export Report
            </button>
            <button class="btn btn-outline-success ml-2" onclick="refreshData()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ number_format($totalUsers) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-user-plus text-success"></i>
                        All registered users
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Users</span>
                    <span class="info-box-number">{{ number_format($activeUsers) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0 }}%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-check-circle text-success"></i>
                        {{ $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">New This Month</span>
                    <span class="info-box-number">{{ number_format($newUsersThisMonth) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $newUsersThisMonth > 0 ? min($newUsersThisMonth * 10, 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-{{ $userGrowthRate >= 0 ? 'arrow-up text-success' : 'arrow-down text-danger' }}"></i>
                        {{ number_format($userGrowthRate, 1) }}% growth
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Banned Users</span>
                    <span class="info-box-number">{{ number_format($bannedUsers) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $totalUsers > 0 ? ($bannedUsers / $totalUsers) * 100 : 0 }}%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        {{ $totalUsers > 0 ? number_format(($bannedUsers / $totalUsers) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Engagement Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        User Engagement Overview
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
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="userEngagementChart" style="height: 300px;"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-legend">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="legend-color bg-primary" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="ml-2">Users with Posts ({{ $userDemographics['with_posts'] }})</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="legend-color bg-success" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="ml-2">Users with Points ({{ $userDemographics['with_points'] }})</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="legend-color bg-warning" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="ml-2">Premium Users ({{ $userDemographics['premium'] }})</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="legend-color bg-info" style="width: 20px; height: 20px; border-radius: 50%;"></div>
                                    <span class="ml-2">Monthly Active ({{ $userDemographics['monthly_active'] }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users and Recent Registrations -->
    <div class="row">
        <!-- Top Users by Posts -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top Users by Posts
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
                                    <th>User</th>
                                    <th>Posts</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUsersByPosts as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->photos->first()?->src ?? 'img/default-avatar.png' }}" 
                                                     class="img-circle mr-2" width="30" height="30" alt="User">
                                                <div>
                                                    <div class="font-weight-bold">{{ $user->user_name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $user->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary font-weight-bold">
                                                {{ $user->service_posts_count }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->is_active === 'active')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-ban mr-1"></i>Banned
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" title="View Profile">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" title="View Posts">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="alert alert-info m-0">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                No users with posts found
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

        <!-- Recent User Registrations -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>
                        Recent Registrations
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
                                    <th>User</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->photos->first()?->src ?? 'img/default-avatar.png' }}" 
                                                     class="img-circle mr-2" width="30" height="30" alt="User">
                                                <div>
                                                    <div class="font-weight-bold">{{ $user->user_name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $user->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                            {{ $user->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @if($user->is_active === 'active')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-ban mr-1"></i>Banned
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" title="View Profile">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="alert alert-info m-0">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                No recent registrations found
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

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.card-tools {
    float: right;
    margin-top: -0.5rem;
}

.card-tools .btn {
    margin-left: 0.25rem;
}

.chart-legend {
    padding: 1rem;
}

.legend-color {
    display: inline-block;
}
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Engagement Chart
const ctx = document.getElementById('userEngagementChart').getContext('2d');
const userEngagementChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Users with Posts', 'Users with Points', 'Premium Users', 'Monthly Active'],
        datasets: [{
            data: [
                {{ $userDemographics['with_posts'] }},
                {{ $userDemographics['with_points'] }},
                {{ $userDemographics['premium'] }},
                {{ $userDemographics['monthly_active'] }}
            ],
            backgroundColor: [
                'rgba(0, 123, 255, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(23, 162, 184, 0.8)'
            ],
            borderColor: [
                'rgba(0, 123, 255, 1)',
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(23, 162, 184, 1)'
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

function refreshData() {
    location.reload();
}
</script>
@endsection 