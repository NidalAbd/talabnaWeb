@extends('adminlte::page')

@section('title', 'Marketing Dashboard - Talabna Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-chart-line text-primary mr-2"></i> 
                Marketing Dashboard
            </h1>
            <p class="text-muted mb-0">Monitor business metrics and marketing performance</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportReport()">
                <i class="fas fa-file-export mr-1"></i> Export Report
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="printDashboard()">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <button type="button" class="btn btn-primary" onclick="sendCampaign()">
                <i class="fas fa-paper-plane mr-1"></i> Send Campaign
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Marketing Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-info shadow-sm">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ number_format($metrics['total_users']) }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All registered users
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Users</span>
                    <span class="info-box-number">{{ number_format($metrics['active_users']) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $metrics['total_users'] > 0 ? ($metrics['active_users'] / $metrics['total_users']) * 100 : 0 }}%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-check-circle text-light"></i> {{ $metrics['total_users'] > 0 ? number_format(($metrics['active_users'] / $metrics['total_users']) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Posts</span>
                    <span class="info-box-number">{{ number_format($metrics['total_posts']) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All service posts
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-danger shadow-sm">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Premium Posts</span>
                    <span class="info-box-number">{{ number_format($metrics['premium_posts']) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $metrics['total_posts'] > 0 ? ($metrics['premium_posts'] / $metrics['total_posts']) * 100 : 0 }}%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-star text-light"></i> {{ $metrics['total_posts'] > 0 ? number_format(($metrics['premium_posts'] / $metrics['total_posts']) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-block" onclick="sendNotification()">
                                <i class="fas fa-bell mr-2"></i> Send Notification
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-success btn-block" onclick="createCampaign()">
                                <i class="fas fa-bullhorn mr-2"></i> Create Campaign
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-info btn-block" onclick="viewAnalytics()">
                                <i class="fas fa-chart-bar mr-2"></i> View Analytics
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-warning btn-block" onclick="exportData()">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Growth Metrics
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="refreshMetrics()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monthly Growth</span>
                                    <span class="info-box-number">{{ $metrics['monthly_growth'] }}%</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ min($metrics['monthly_growth'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Engagement Rate</span>
                                    <span class="info-box-number">{{ $metrics['engagement_rate'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ min($metrics['engagement_rate'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Metrics -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><strong>Conversion Rate:</strong></td>
                                            <td><span class="badge badge-success">{{ number_format($metrics['conversion_rate'] ?? 0, 2) }}%</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Average Session:</strong></td>
                                            <td><span class="badge badge-info">{{ $metrics['avg_session'] ?? '0' }} min</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bounce Rate:</strong></td>
                                            <td><span class="badge badge-warning">{{ number_format($metrics['bounce_rate'] ?? 0, 1) }}%</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Recent Activities
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="refreshActivities()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Activity</th>
                                    <th>Count</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <i class="fas fa-user-plus text-success mr-2"></i>
                                        New Users (Today)
                                    </td>
                                    <td><span class="badge badge-success">{{ $recentActivities['new_users']->where('created_at', '>=', today())->count() }}</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-file-alt text-info mr-2"></i>
                                        New Posts (Today)
                                    </td>
                                    <td><span class="badge badge-info">{{ $recentActivities['new_posts']->where('created_at', '>=', today())->count() }}</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-eye text-warning mr-2"></i>
                                        Top Posts Views
                                    </td>
                                    <td><span class="badge badge-warning">{{ $recentActivities['top_posts']->sum('view_count') }}</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-heart text-danger mr-2"></i>
                                        Total Favorites
                                    </td>
                                    <td><span class="badge badge-danger">{{ $recentActivities['favorites'] ?? 0 }}</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Section -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-2"></i>
                        User Growth Trend
                    </h3>
                    <div class="card-tools">
                        <select class="form-control form-control-sm" id="timeRange">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-pie-chart mr-2"></i>
                        User Demographics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="demographicsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initializeCharts();
    
    // Time range change handler
    $('#timeRange').on('change', function() {
        updateCharts();
    });
});

function initializeCharts() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    window.userGrowthChart = new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
            datasets: [{
                label: 'New Users',
                data: [12, 19, 3, 5, 2, 3, 7],
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

    // Demographics Chart
    const demographicsCtx = document.getElementById('demographicsChart').getContext('2d');
    window.demographicsChart = new Chart(demographicsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: [65, 30, 5],
                backgroundColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateCharts() {
    // Update charts based on selected time range
    const timeRange = $('#timeRange').val();
    // Implement chart update logic here
    console.log('Updating charts for time range:', timeRange);
}

// Additional functions
function exportReport() {
    Swal.fire({
        title: 'Export Marketing Report',
        text: 'Choose export format:',
        icon: 'info',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export to Excel
            window.open('/admin/marketing/export?format=excel', '_blank');
        } else if (result.isDenied) {
            // Export to PDF
            window.open('/admin/marketing/export?format=pdf', '_blank');
        }
    });
}

function printDashboard() {
    window.print();
}

function sendCampaign() {
    Swal.fire({
        title: 'Send Marketing Campaign',
        text: 'Create and send a new marketing campaign to users.',
        icon: 'info',
        confirmButtonText: 'Create Campaign'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/admin/marketing/campaigns/create';
        }
    });
}

function sendNotification() {
    Swal.fire({
        title: 'Send Notification',
        text: 'Send a push notification to all users.',
        icon: 'info',
        input: 'text',
        inputLabel: 'Notification Message',
        inputPlaceholder: 'Enter your message...',
        showCancelButton: true,
        confirmButtonText: 'Send'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.marketing.send-notification") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    message: result.value
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to send notification.', 'error');
                }
            });
        }
    });
}

function createCampaign() {
    Swal.fire({
        title: 'Create Marketing Campaign',
        text: 'Set up a new marketing campaign.',
        icon: 'info',
        confirmButtonText: 'Create'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/admin/marketing/campaigns/create';
        }
    });
}

function viewAnalytics() {
    Swal.fire({
        title: 'View Detailed Analytics',
        text: 'Open detailed analytics dashboard.',
        icon: 'info',
        confirmButtonText: 'Open Analytics'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/admin/marketing/analytics';
        }
    });
}

function exportData() {
    Swal.fire({
        title: 'Export Data',
        text: 'Export marketing data for analysis.',
        icon: 'info',
        confirmButtonText: 'Export'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.marketing.export-data") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        window.open(response.download_url, '_blank');
                        Swal.fire('Success!', 'Data exported successfully!', 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to export data.', 'error');
                }
            });
        }
    });
}

function refreshMetrics() {
    Swal.fire({
        title: 'Refreshing Metrics',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route("admin.marketing.refresh-metrics") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Update metrics on the page
                updateMetricsDisplay(response.metrics);
                Swal.fire('Success!', 'Metrics refreshed successfully!', 'success');
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to refresh metrics.', 'error');
        }
    });
}

function refreshActivities() {
    Swal.fire({
        title: 'Refreshing Activities',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route("admin.marketing.refresh-activities") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Update activities on the page
                updateActivitiesDisplay(response.activities);
                Swal.fire('Success!', 'Activities refreshed successfully!', 'success');
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Failed to refresh activities.', 'error');
        }
    });
}

function updateMetricsDisplay(metrics) {
    // Update metrics display with new data
    if (metrics.total_users !== undefined) {
        $('.info-box-number').each(function() {
            const text = $(this).text();
            if (text.includes('Total Users')) {
                $(this).text(metrics.total_users);
            } else if (text.includes('Active Users')) {
                $(this).text(metrics.active_users);
            } else if (text.includes('Total Posts')) {
                $(this).text(metrics.total_posts);
            } else if (text.includes('Premium Posts')) {
                $(this).text(metrics.premium_posts);
            }
        });
    }
}

function updateActivitiesDisplay(activities) {
    // Update activities display with new data
    if (activities && activities.length > 0) {
        const activitiesContainer = $('#recent-activities');
        activitiesContainer.empty();
        
        activities.forEach(activity => {
            activitiesContainer.append(`
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas ${activity.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">${activity.title}</div>
                        <div class="activity-time">${activity.time}</div>
                    </div>
                </div>
            `);
        });
    }
}
</script>
@endpush
@endsection 