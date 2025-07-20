@extends('adminlte::page')
@section('title', 'Monthly Profit & Loss')
@section('content_header')
    <h1><i class="fas fa-chart-pie text-success mr-2"></i> Monthly Profit & Loss</h1>
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
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Profit</h5>
                    <h3>$4,200</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Loss</h5>
                    <h3>$1,100</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Net Profit</h5>
                    <h3>$3,100</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Profit Margin</h5>
                    <h3>28.2%</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">Month:</label>
                <select class="form-control mr-2" name="month">
                    <option>June 2024</option>
                    <option>May 2024</option>
                    <option>April 2024</option>
                </select>
                <button class="btn btn-primary mr-2">Filter</button>
                <button class="btn btn-secondary">Export</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Monthly Profit & Loss Records</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>Expenses</th>
                        <th>Profit</th>
                        <th>Loss</th>
                        <th>Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>June 2024</td>
                        <td>$12,500</td>
                        <td>$9,400</td>
                        <td>$4,200</td>
                        <td>$1,100</td>
                        <td>$3,100</td>
                    </tr>
                    <tr>
                        <td>May 2024</td>
                        <td>$11,800</td>
                        <td>$8,900</td>
                        <td>$3,900</td>
                        <td>$1,000</td>
                        <td>$2,900</td>
                    </tr>
                    <tr>
                        <td>April 2024</td>
                        <td>$10,900</td>
                        <td>$8,200</td>
                        <td>$3,200</td>
                        <td>$900</td>
                        <td>$2,300</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 