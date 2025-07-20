@extends('adminlte::page')
@section('title', 'Advertisement Costs')
@section('content_header')
    <h1><i class="fas fa-cogs text-primary mr-2"></i> Advertisement Costs</h1>
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
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Ad Costs</h5>
                    <h3>$2,200</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Campaigns</h5>
                    <h3>12</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Avg. Cost/Campaign</h5>
                    <h3>$183</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Growth Rate</h5>
                    <h3>+3.2%</h3>
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
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Advertisement Cost Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Campaign</th>
                        <th>Platform</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-01</td>
                        <td>Summer Sale</td>
                        <td>Facebook</td>
                        <td>$400</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-02</td>
                        <td>App Launch</td>
                        <td>Google Ads</td>
                        <td>$600</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-03</td>
                        <td>Brand Awareness</td>
                        <td>Instagram</td>
                        <td>$300</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-04</td>
                        <td>Referral Program</td>
                        <td>Twitter</td>
                        <td>$200</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 