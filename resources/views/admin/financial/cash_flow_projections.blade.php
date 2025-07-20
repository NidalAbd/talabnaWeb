@extends('adminlte::page')
@section('title', 'Cash Flow Projections')
@section('content_header')
    <h1><i class="fas fa-chart-area text-info mr-2"></i> Cash Flow Projections</h1>
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
                    <h5>Projected Inflow</h5>
                    <h3>$15,000</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Projected Outflow</h5>
                    <h3>$10,200</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Net Cash Flow</h5>
                    <h3>$4,800</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Projection Period</h5>
                    <h3>Q3 2024</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">Projection Period:</label>
                <select class="form-control mr-2" name="period">
                    <option>Q3 2024</option>
                    <option>Q2 2024</option>
                    <option>Q1 2024</option>
                </select>
                <button class="btn btn-primary mr-2">Filter</button>
                <button class="btn btn-secondary">Export</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Cash Flow Projection Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Period</th>
                        <th>Inflow</th>
                        <th>Outflow</th>
                        <th>Net Flow</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Q3 2024</td>
                        <td>$15,000</td>
                        <td>$10,200</td>
                        <td>$4,800</td>
                        <td>Expected growth in premium sales</td>
                    </tr>
                    <tr>
                        <td>Q2 2024</td>
                        <td>$13,500</td>
                        <td>$9,800</td>
                        <td>$3,700</td>
                        <td>Stable ad revenue</td>
                    </tr>
                    <tr>
                        <td>Q1 2024</td>
                        <td>$12,000</td>
                        <td>$8,900</td>
                        <td>$3,100</td>
                        <td>Initial marketing push</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 