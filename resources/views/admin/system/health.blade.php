@extends('adminlte::page')

@section('title', 'System Health')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">System Health</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Health</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- System Status Overview -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-{{ $systemHealth['database']['status'] == 'healthy' ? 'success' : 'danger' }}">
                        <div class="inner">
                            <h3>{{ ucfirst($systemHealth['database']['status']) }}</h3>
                            <p>Database</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-{{ $systemHealth['storage']['status'] == 'healthy' ? 'success' : ($systemHealth['storage']['status'] == 'warning' ? 'warning' : 'danger') }}">
                        <div class="inner">
                            <h3>{{ ucfirst($systemHealth['storage']['status']) }}</h3>
                            <p>Storage</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hdd"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-{{ $systemHealth['cache']['status'] == 'healthy' ? 'success' : 'danger' }}">
                        <div class="inner">
                            <h3>{{ ucfirst($systemHealth['cache']['status']) }}</h3>
                            <p>Cache</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-memory"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $systemHealth['performance']['php_version'] }}</h3>
                            <p>PHP Version</p>
                        </div>
                        <div class="icon">
                            <i class="fab fa-php"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Database Health -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-database mr-2"></i>
                                Database Health
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['database']['status'] == 'healthy' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($systemHealth['database']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Connection</strong></td>
                                            <td>{{ $systemHealth['database']['connection'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tables Count</strong></td>
                                            <td>{{ $systemHealth['database']['tables_count'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Check</strong></td>
                                            <td>{{ $systemHealth['database']['last_check']->format('Y-m-d H:i:s') }}</td>
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
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['storage']['status'] == 'healthy' ? 'success' : ($systemHealth['storage']['status'] == 'warning' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($systemHealth['storage']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Space</strong></td>
                                            <td>{{ $systemHealth['storage']['total_space'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Used Space</strong></td>
                                            <td>{{ $systemHealth['storage']['used_space'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Free Space</strong></td>
                                            <td>{{ $systemHealth['storage']['free_space'] }}</td>
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
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Memory Usage</strong></td>
                                            <td>{{ $systemHealth['performance']['memory_usage'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Peak Memory</strong></td>
                                            <td>{{ $systemHealth['performance']['peak_memory'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Execution Time</strong></td>
                                            <td>{{ $systemHealth['performance']['execution_time'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Laravel Version</strong></td>
                                            <td>{{ $systemHealth['performance']['laravel_version'] }}</td>
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
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Status</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $systemHealth['cache']['status'] == 'healthy' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($systemHealth['cache']['status']) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Driver</strong></td>
                                            <td>{{ $systemHealth['cache']['driver'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Check</strong></td>
                                            <td>{{ $systemHealth['cache']['last_check']->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Errors -->
            @if(!empty($systemHealth['errors']))
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Recent Errors
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($systemHealth['errors'] as $error)
                                        <tr>
                                            <td class="text-danger">{{ $error }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection 