@extends('adminlte::page')
@section('title', 'Point Usage Analytics')
@section('content_header')
    <h1><i class="fas fa-chart-bar text-warning mr-2"></i> Point Usage Analytics</h1>
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
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Total Points Used</h5>
                    <h3>18,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Points Purchased</h5>
                    <h3>25,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Avg. Points/User</h5>
                    <h3>120</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Transactions</h5>
                    <h3>320</h3>
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
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Point Analytics Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>User</th>
                        <th>Points Used</th>
                        <th>Points Purchased</th>
                        <th>Last Transaction</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>1,200</td>
                        <td>2,000</td>
                        <td>2024-06-20</td>
                        <td>800</td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>900</td>
                        <td>1,500</td>
                        <td>2024-06-18</td>
                        <td>600</td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>Ali Hassan</td>
                        <td>0</td>
                        <td>500</td>
                        <td>2024-05-01</td>
                        <td>500</td>
                        <td><span class="badge badge-danger">Banned</span></td>
                    </tr>
                    <tr>
                        <td>Maria Garcia</td>
                        <td>800</td>
                        <td>1,200</td>
                        <td>2024-06-19</td>
                        <td>400</td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 