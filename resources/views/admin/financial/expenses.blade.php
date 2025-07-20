@extends('adminlte::page')
@section('title', 'Expense Tracking')
@section('content_header')
    <h1><i class="fas fa-receipt text-danger mr-2"></i> Expense Tracking</h1>
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
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Expenses</h5>
                    <h3>$8,400</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Transactions</h5>
                    <h3>95</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h3>$1,100</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h3>$7,300</h3>
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
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Expense Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-01</td>
                        <td>Marketing</td>
                        <td>Facebook Ads</td>
                        <td>$500</td>
                        <td><span class="badge badge-success">Approved</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-02</td>
                        <td>Operations</td>
                        <td>Office Supplies</td>
                        <td>$120</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-03</td>
                        <td>Infrastructure</td>
                        <td>Server Hosting</td>
                        <td>$1,200</td>
                        <td><span class="badge badge-success">Approved</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-04</td>
                        <td>HR</td>
                        <td>Staff Training</td>
                        <td>$300</td>
                        <td><span class="badge badge-danger">Rejected</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 