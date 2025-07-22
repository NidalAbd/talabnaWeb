@extends('adminlte::page')
@section('title', 'Point Usage Analytics')
@section('content_header')
    <h1><i class="fas fa-chart-bar text-warning mr-2"></i> Point Usage Analytics</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\analytics\point_analytics.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\analytics\point_analytics.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{('admin\analytics\point_analytics.total_points_used') }}</h5>
                    <h3>{{('admin\analytics\point_analytics.18_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\analytics\point_analytics.points_purchased') }}</h5>
                    <h3>{{('admin\analytics\point_analytics.25_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{('admin\analytics\point_analytics.avg_points_user') }}</h5>
                    <h3>{{('admin\analytics\point_analytics.120') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{('admin\analytics\point_analytics.transactions') }}</h5>
                    <h3>{{('admin\analytics\point_analytics.320') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\analytics\point_analytics.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{('admin\analytics\point_analytics.filter') }}</button>
                <button class="btn btn-secondary">{{('admin\analytics\point_analytics.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Point Analytics Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{('admin\analytics\point_analytics.user') }}</th>
                        <th>{{('admin\analytics\point_analytics.points_used') }}</th>
                        <th>{{('admin\analytics\point_analytics.points_purchased') }}</th>
                        <th>{{('admin\analytics\point_analytics.last_transaction') }}</th>
                        <th>{{('admin\analytics\point_analytics.balance') }}</th>
                        <th>{{('admin\analytics\point_analytics.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\analytics\point_analytics.john_doe') }}</td>
                        <td>{{('admin\analytics\point_analytics.1_200') }}</td>
                        <td>{{('admin\analytics\point_analytics.2_000') }}</td>
                        <td>{{('admin\analytics\point_analytics.2024_06_20') }}</td>
                        <td>{{('admin\analytics\point_analytics.800') }}</td>
                        <td><span class="badge badge-success">{{('admin\analytics\point_analytics.active') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\analytics\point_analytics.jane_smith') }}</td>
                        <td>{{('admin\analytics\point_analytics.900') }}</td>
                        <td>{{('admin\analytics\point_analytics.1_500') }}</td>
                        <td>{{('admin\analytics\point_analytics.2024_06_18') }}</td>
                        <td>{{('admin\analytics\point_analytics.600') }}</td>
                        <td><span class="badge badge-success">{{('admin\analytics\point_analytics.active') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\analytics\point_analytics.ali_hassan') }}</td>
                        <td>0</td>
                        <td>{{('admin\analytics\point_analytics.500') }}</td>
                        <td>{{('admin\analytics\point_analytics.2024_05_01') }}</td>
                        <td>{{('admin\analytics\point_analytics.500') }}</td>
                        <td><span class="badge badge-danger">{{('admin\analytics\point_analytics.banned') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\analytics\point_analytics.maria_garcia') }}</td>
                        <td>{{('admin\analytics\point_analytics.800') }}</td>
                        <td>{{('admin\analytics\point_analytics.1_200') }}</td>
                        <td>{{('admin\analytics\point_analytics.2024_06_19') }}</td>
                        <td>{{('admin\analytics\point_analytics.400') }}</td>
                        <td><span class="badge badge-success">{{('admin\analytics\point_analytics.active') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






