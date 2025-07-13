@extends('adminlte::page')

@section('title', 'Marketing Dashboard')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Marketing Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Marketing</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Marketing Metrics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
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
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($metrics['active_users']) }}</h3>
                            <p>Active Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($metrics['total_posts']) }}</h3>
                            <p>Total Posts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($metrics['premium_posts']) }}</h3>
                            <p>Premium Posts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Growth Metrics
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-gradient-success">
                                        <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Monthly Growth</span>
                                            <span class="info-box-number">{{ $metrics['monthly_growth'] }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-gradient-info">
                                        <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Engagement Rate</span>
                                            <span class="info-box-number">{{ $metrics['engagement_rate'] }}</span>
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
                                <i class="fas fa-clock mr-2"></i>
                                Recent Activities
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>New Users (Today)</td>
                                            <td>{{ $recentActivities['new_users']->where('created_at', '>=', today())->count() }}</td>
                                        </tr>
                                        <tr>
                                            <td>New Posts (Today)</td>
                                            <td>{{ $recentActivities['new_posts']->where('created_at', '>=', today())->count() }}</td>
                                        </tr>
                                        <tr>
                                            <td>Top Posts Views</td>
                                            <td>{{ $recentActivities['top_posts']->sum('view_count') }}</td>
                                        </tr>
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
@endsection 