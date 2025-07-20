@extends('adminlte::page')
@section('title', 'Payment Reports')
@section('content_header')
    <h1><i class="fas fa-credit-card text-info mr-2"></i> Payment Reports</h1>
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
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Payments</h5>
                    <h3>$15,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Completed</h5>
                    <h3>$13,500</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h3>$1,200</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Failed</h5>
                    <h3>$300</h3>
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
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Payment Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-01</td>
                        <td>John Doe</td>
                        <td>$120</td>
                        <td>Credit Card</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-02</td>
                        <td>Jane Smith</td>
                        <td>$250</td>
                        <td>PayPal</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-03</td>
                        <td>Ali Hassan</td>
                        <td>$75</td>
                        <td>Bank Transfer</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-04</td>
                        <td>Maria Garcia</td>
                        <td>$300</td>
                        <td>Credit Card</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 