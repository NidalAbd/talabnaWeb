@extends('adminlte::page')
@section('title', 'Server Hosting Costs')
@section('content_header')
    <h1><i class="fas fa-server text-secondary mr-2"></i> Server Hosting Costs</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\financial\server_hosting_costs.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\financial\server_hosting_costs.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\server_hosting_costs.total_hosting_costs') }}</h5>
                    <h3>{{('admin\financial\server_hosting_costs._3_500') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\server_hosting_costs.servers') }}</h5>
                    <h3>{{('admin\financial\server_hosting_costs.5') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\server_hosting_costs.avg_cost_server') }}</h5>
                    <h3>{{('admin\financial\server_hosting_costs._700') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\server_hosting_costs.growth_rate') }}</h5>
                    <h3>{{('admin\financial\server_hosting_costs._2_5_') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\financial\server_hosting_costs.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{('admin\financial\server_hosting_costs.filter') }}</button>
                <button class="btn btn-secondary">{{('admin\financial\server_hosting_costs.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Server Hosting Cost Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{('admin\financial\server_hosting_costs.date') }}</th>
                        <th>{{('admin\financial\server_hosting_costs.server') }}</th>
                        <th>{{('admin\financial\server_hosting_costs.provider') }}</th>
                        <th>{{('admin\financial\server_hosting_costs.amount') }}</th>
                        <th>{{('admin\financial\server_hosting_costs.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\financial\server_hosting_costs.2024_06_01') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.web_server_1') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.aws') }}</td>
                        <td>{{('admin\financial\server_hosting_costs._800') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\server_hosting_costs.paid') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\server_hosting_costs.2024_06_02') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.db_server') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.azure') }}</td>
                        <td>{{('admin\financial\server_hosting_costs._600') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\server_hosting_costs.paid') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\server_hosting_costs.2024_06_03') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.backup_server') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.google_cloud') }}</td>
                        <td>{{('admin\financial\server_hosting_costs._500') }}</td>
                        <td><span class="badge badge-warning">{{('admin\financial\server_hosting_costs.pending') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\server_hosting_costs.2024_06_04') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.app_server') }}</td>
                        <td>{{('admin\financial\server_hosting_costs.digitalocean') }}</td>
                        <td>{{('admin\financial\server_hosting_costs._700') }}</td>
                        <td><span class="badge badge-danger">{{('admin\financial\server_hosting_costs.failed') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






