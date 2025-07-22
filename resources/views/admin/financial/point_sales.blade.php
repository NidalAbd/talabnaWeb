@extends('adminlte::page')
@section('title', 'Point Sales')
@section('content_header')
    <h1><i class="fas fa-coins text-warning mr-2"></i> Point Sales</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\financial\point_sales.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\financial\point_sales.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\point_sales.total_points_sold') }}</h5>
                    <h3>{{('admin\financial\point_sales.25_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\point_sales.transactions') }}</h5>
                    <h3>{{('admin\financial\point_sales.180') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\point_sales.revenue') }}</h5>
                    <h3>{{('admin\financial\point_sales._7_200') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\point_sales.avg_points_user') }}</h5>
                    <h3>{{('admin\financial\point_sales.139') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\financial\point_sales.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{('admin\financial\point_sales.filter') }}</button>
                <button class="btn btn-secondary">{{('admin\financial\point_sales.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Point Sales Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{('admin\financial\point_sales.date') }}</th>
                        <th>{{('admin\financial\point_sales.user') }}</th>
                        <th>{{('admin\financial\point_sales.points') }}</th>
                        <th>{{('admin\financial\point_sales.amount') }}</th>
                        <th>{{('admin\financial\point_sales.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\financial\point_sales.2024_06_01') }}</td>
                        <td>{{('admin\financial\point_sales.john_doe') }}</td>
                        <td>{{('admin\financial\point_sales.1_000') }}</td>
                        <td>{{('admin\financial\point_sales._40') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\point_sales.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\point_sales.2024_06_02') }}</td>
                        <td>{{('admin\financial\point_sales.jane_smith') }}</td>
                        <td>{{('admin\financial\point_sales.2_500') }}</td>
                        <td>{{('admin\financial\point_sales._100') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\point_sales.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\point_sales.2024_06_03') }}</td>
                        <td>{{('admin\financial\point_sales.ali_hassan') }}</td>
                        <td>{{('admin\financial\point_sales.500') }}</td>
                        <td>{{('admin\financial\point_sales._20') }}</td>
                        <td><span class="badge badge-warning">{{('admin\financial\point_sales.pending') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\point_sales.2024_06_04') }}</td>
                        <td>{{('admin\financial\point_sales.maria_garcia') }}</td>
                        <td>{{('admin\financial\point_sales.3_000') }}</td>
                        <td>{{('admin\financial\point_sales._120') }}</td>
                        <td><span class="badge badge-danger">{{('admin\financial\point_sales.failed') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






