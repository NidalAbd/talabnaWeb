@extends('adminlte::page')
@section('title', 'Income Statement')
@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Income Statement</h1>
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
                    <h5>Total Revenue</h5>
                    <h3>$45,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Expenses</h5>
                    <h3>$32,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Net Income</h5>
                    <h3>$13,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Reporting Period</h5>
                    <h3>2024 YTD</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">Period:</label>
                <select class="form-control mr-2" name="period">
                    <option>2024 YTD</option>
                    <option>2023</option>
                    <option>2022</option>
                </select>
                <button class="btn btn-primary mr-2">Filter</button>
                <button class="btn btn-secondary">Export</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Income Statement Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Period</th>
                        <th>Revenue</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Expenses</th>
                        <th>Net Income</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024 YTD</td>
                        <td>$45,000</td>
                        <td>$12,000</td>
                        <td>$33,000</td>
                        <td>$20,000</td>
                        <td>$13,000</td>
                    </tr>
                    <tr>
                        <td>2023</td>
                        <td>$38,000</td>
                        <td>$10,000</td>
                        <td>$28,000</td>
                        <td>$17,000</td>
                        <td>$11,000</td>
                    </tr>
                    <tr>
                        <td>2022</td>
                        <td>$32,000</td>
                        <td>$8,000</td>
                        <td>$24,000</td>
                        <td>$15,000</td>
                        <td>$9,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 