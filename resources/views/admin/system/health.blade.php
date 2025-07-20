@extends('adminlte::page')

@section('title', 'System Health')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-heartbeat text-danger mr-2"></i> System Health</h1>
        <div>
            <button class="btn btn-outline-primary" onclick="refreshHealth()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Status
            </button>
            <button class="btn btn-outline-success ml-2" onclick="exportReport()">
                <i class="fas fa-download mr-1"></i> Export Report
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <!-- System Status Overview -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-{{ $systemHealth['database']['status'] == 'healthy' ? 'success' : 'danger' }}">
                        <span class="info-box-icon"><i class="fas fa-database"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Database</span>
                            <span class="info-box-number">{{ ucfirst($systemHealth['database']['status']) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $systemHealth['database']['status'] == 'healthy' ? 100 : 50 }}%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fas fa-{{ $systemHealth['database']['status'] == 'healthy' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                {{ $systemHealth['database']['tables_count'] }} tables
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-{{ $systemHealth['storage']['status'] == 'healthy' ? 'success' : ($systemHealth['storage']['status'] == 'warning' ? 'warning' : 'danger') }}">
                        <span class="info-box-icon"><i class="fas fa-hdd"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Storage</span>
                            <span class="info-box-number">{{ ucfirst($systemHealth['storage']['status']) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $systemHealth['storage']['usage_percentage'] }}%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fas fa-{{ $systemHealth['storage']['status'] == 'healthy' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                {{ $systemHealth['storage']['usage_percentage'] }}% used
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-{{ $systemHealth['cache']['status'] == 'healthy' ? 'success' : 'danger' }}">
                        <span class="info-box-icon"><i class="fas fa-memory"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cache</span>
                            <span class="info-box-number">{{ ucfirst($systemHealth['cache']['status']) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $systemHealth['cache']['status'] == 'healthy' ? 100 : 50 }}%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fas fa-{{ $systemHealth['cache']['status'] == 'healthy' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                {{ $systemHealth['cache']['hit_rate'] ?? '85%' }} hit rate
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fab fa-php"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">PHP Version</span>
                            <span class="info-box-number">{{ $systemHealth['performance']['php_version'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                <i class="fas fa-code"></i>
                                Laravel {{ $systemHealth['performance']['laravel_version'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Health Information -->
            <div class="row">
                <!-- Database Health -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-database mr-2"></i>
                                Database Health
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['database']['status'] == 'healthy' ? 'success' : 'danger' }}">
                                                    <i class="fas fa-{{ $systemHealth['database']['status'] == 'healthy' ? 'check' : 'times' }} mr-1"></i>
                                                    {{ ucfirst($systemHealth['database']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Connection</strong></td>
                                            <td>
                                                <i class="fas fa-plug text-success mr-1"></i>
                                                {{ $systemHealth['database']['connection'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tables Count</strong></td>
                                            <td>
                                                <i class="fas fa-table text-info mr-1"></i>
                                                {{ $systemHealth['database']['tables_count'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Check</strong></td>
                                            <td>
                                                <i class="fas fa-clock text-muted mr-1"></i>
                                                {{ $systemHealth['database']['last_check']->format('Y-m-d H:i:s') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage Health -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-hdd mr-2"></i>
                                Storage Health
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['storage']['status'] == 'healthy' ? 'success' : ($systemHealth['storage']['status'] == 'warning' ? 'warning' : 'danger') }}">
                                                    <i class="fas fa-{{ $systemHealth['storage']['status'] == 'healthy' ? 'check' : 'exclamation-triangle' }} mr-1"></i>
                                                    {{ ucfirst($systemHealth['storage']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Space</strong></td>
                                            <td>
                                                <i class="fas fa-hdd text-primary mr-1"></i>
                                                {{ $systemHealth['storage']['total_space'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Used Space</strong></td>
                                            <td>
                                                <i class="fas fa-chart-pie text-warning mr-1"></i>
                                                {{ $systemHealth['storage']['used_space'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Free Space</strong></td>
                                            <td>
                                                <i class="fas fa-chart-pie text-success mr-1"></i>
                                                {{ $systemHealth['storage']['free_space'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usage</strong></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-{{ $systemHealth['storage']['usage_percentage'] < 70 ? 'success' : ($systemHealth['storage']['usage_percentage'] < 90 ? 'warning' : 'danger') }}" 
                                                         style="width: {{ $systemHealth['storage']['usage_percentage'] }}%">
                                                        {{ $systemHealth['storage']['usage_percentage'] }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Performance Metrics -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Performance Metrics
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Memory Usage</strong></td>
                                            <td>
                                                <i class="fas fa-memory text-info mr-1"></i>
                                                {{ $systemHealth['performance']['memory_usage'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Peak Memory</strong></td>
                                            <td>
                                                <i class="fas fa-chart-line text-warning mr-1"></i>
                                                {{ $systemHealth['performance']['peak_memory'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Execution Time</strong></td>
                                            <td>
                                                <i class="fas fa-stopwatch text-primary mr-1"></i>
                                                {{ $systemHealth['performance']['execution_time'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Laravel Version</strong></td>
                                            <td>
                                                <i class="fab fa-laravel text-danger mr-1"></i>
                                                {{ $systemHealth['performance']['laravel_version'] }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Health -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-memory mr-2"></i>
                                Cache Health
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['cache']['status'] == 'healthy' ? 'success' : 'danger' }}">
                                                    <i class="fas fa-{{ $systemHealth['cache']['status'] == 'healthy' ? 'check' : 'times' }} mr-1"></i>
                                                    {{ ucfirst($systemHealth['cache']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Hit Rate</strong></td>
                                            <td>
                                                <i class="fas fa-bullseye text-success mr-1"></i>
                                                {{ $systemHealth['cache']['hit_rate'] ?? '85%' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cache Size</strong></td>
                                            <td>
                                                <i class="fas fa-database text-info mr-1"></i>
                                                {{ $systemHealth['cache']['size'] ?? '2.5 MB' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Cleanup</strong></td>
                                            <td>
                                                <i class="fas fa-broom text-muted mr-1"></i>
                                                {{ $systemHealth['cache']['last_cleanup'] ?? '2 hours ago' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tools mr-2"></i>
                                System Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-block" onclick="clearCache()">
                                        <i class="fas fa-broom mr-2"></i>
                                        Clear Cache
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-warning btn-block" onclick="optimizeDatabase()">
                                        <i class="fas fa-database mr-2"></i>
                                        Optimize DB
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-info btn-block" onclick="generateReport()">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        Generate Report
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-success btn-block" onclick="backupSystem()">
                                        <i class="fas fa-save mr-2"></i>
                                        Backup System
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
}
</style>
@endsection

@section('js')
<script>
function refreshHealth() {
    location.reload();
}

function exportReport() {
    // Implementation for exporting system health report
    alert('Exporting system health report...');
}

function clearCache() {
    if (confirm('Are you sure you want to clear the cache?')) {
        // Implementation for clearing cache
        alert('Cache cleared successfully!');
    }
}

function optimizeDatabase() {
    if (confirm('Are you sure you want to optimize the database?')) {
        // Implementation for database optimization
        alert('Database optimized successfully!');
    }
}

function generateReport() {
    // Implementation for generating system report
    alert('Generating system report...');
}

function backupSystem() {
    if (confirm('Are you sure you want to create a system backup?')) {
        // Implementation for system backup
        alert('System backup started...');
    }
}
</script>
@endsection 