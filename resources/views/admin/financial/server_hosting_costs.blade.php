@extends('adminlte::page')
@section('title', 'Server Hosting Costs')
@section('content_header')
    <h1><i class="fas fa-server text-secondary mr-2"></i> Server Hosting Costs</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>Demo Data:</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">Remove Demo Data</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>Total Hosting Costs</h5>
                    <h3>$3,500</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Servers</h5>
                    <h3>5</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Avg. Cost/Server</h5>
                    <h3>$700</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Growth Rate</h5>
                    <h3>+2.5%</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">Date Range:</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">Filter</button>
                <button class="btn btn-secondary">Export</button>
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
                        <th>Date</th>
                        <th>Server</th>
                        <th>Provider</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-01</td>
                        <td>Web Server 1</td>
                        <td>AWS</td>
                        <td>$800</td>
                        <td><span class="badge badge-success">Paid</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-02</td>
                        <td>DB Server</td>
                        <td>Azure</td>
                        <td>$600</td>
                        <td><span class="badge badge-success">Paid</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-03</td>
                        <td>Backup Server</td>
                        <td>Google Cloud</td>
                        <td>$500</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-04</td>
                        <td>App Server</td>
                        <td>DigitalOcean</td>
                        <td>$700</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 