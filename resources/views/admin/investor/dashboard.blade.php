@extends('adminlte::page')

@section('title', 'Investor Dashboard')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Investor Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Investor</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Financial Metrics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($metrics['total_revenue']) }}</h3>
                            <p>Total Revenue (SAR)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($metrics['monthly_revenue']) }}</h3>
                            <p>Monthly Revenue (SAR)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($metrics['total_users']) }}</h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($metrics['active_posts']) }}</h3>
                            <p>Active Posts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Metrics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">User Growth</span>
                            <span class="info-box-number">{{ $metrics['user_growth'] }}%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ min($metrics['user_growth'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Revenue Growth</span>
                            <span class="info-box-number">{{ $metrics['revenue_growth'] }}%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ min($metrics['revenue_growth'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Engagement Rate</span>
                            <span class="info-box-number">{{ $metrics['engagement_rate'] }}%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ min($metrics['engagement_rate'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box bg-gradient-danger">
                        <span class="info-box-icon"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Premium Users</span>
                            <span class="info-box-number">{{ $metrics['premium_users'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $metrics['premium_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Revenue Chart -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Revenue Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Top Categories
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive">
                                <canvas id="categoriesChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- User Activity -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>
                                User Activity
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Value</th>
                                            <th>Change</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>New Users (This Month)</td>
                                            <td>{{ number_format($metrics['new_users_month']) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['new_users_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['new_users_growth'] >= 0 ? '+' : '' }}{{ $metrics['new_users_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Active Users (This Week)</td>
                                            <td>{{ number_format($metrics['active_users_week']) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['active_users_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['active_users_growth'] >= 0 ? '+' : '' }}{{ $metrics['active_users_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Premium Subscriptions</td>
                                            <td>{{ number_format($metrics['premium_subscriptions']) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['premium_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['premium_growth'] >= 0 ? '+' : '' }}{{ $metrics['premium_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Average Session Duration</td>
                                            <td>{{ $metrics['avg_session_duration'] }} min</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['session_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['session_growth'] >= 0 ? '+' : '' }}{{ $metrics['session_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator mr-2"></i>
                                Financial Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Revenue</th>
                                            <th>Growth</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>This Month</td>
                                            <td>{{ number_format($metrics['monthly_revenue']) }} SAR</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['monthly_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['monthly_growth'] >= 0 ? '+' : '' }}{{ $metrics['monthly_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Last Month</td>
                                            <td>{{ number_format($metrics['last_month_revenue']) }} SAR</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['last_month_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['last_month_growth'] >= 0 ? '+' : '' }}{{ $metrics['last_month_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>This Quarter</td>
                                            <td>{{ number_format($metrics['quarterly_revenue']) }} SAR</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['quarterly_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['quarterly_growth'] >= 0 ? '+' : '' }}{{ $metrics['quarterly_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>This Year</td>
                                            <td>{{ number_format($metrics['yearly_revenue']) }} SAR</td>
                                            <td>
                                                <span class="badge badge-{{ $metrics['yearly_growth'] >= 0 ? 'success' : 'danger' }}">
                                                    {{ $metrics['yearly_growth'] >= 0 ? '+' : '' }}{{ $metrics['yearly_growth'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Recent Transactions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Package</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recent_transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                                <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                                                <td>{{ $transaction->package->name['en'] ?? 'N/A' }}</td>
                                                <td>{{ $transaction->amount }} {{ $transaction->currency }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No recent transactions found.</td>
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
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Revenue Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chart_data['labels']) !!},
            datasets: [{
                label: 'Revenue (SAR)',
                data: {!! json_encode($chart_data['revenue']) !!},
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

    // Categories Chart
    var categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    var categoriesChart = new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chart_data['category_labels']) !!},
            datasets: [{
                data: {!! json_encode($chart_data['category_data']) !!},
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
@endsection 