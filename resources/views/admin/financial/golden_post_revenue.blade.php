@extends('adminlte::page')
@section('title', 'Golden Post Revenue')
@section('content_header')
    <h1><i class="fas fa-star text-warning mr-2"></i> Golden Post Revenue</h1>
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
                    <h5>Total Golden Revenue</h5>
                    <h3>$2,800</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Golden Posts</h5>
                    <h3>70</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Avg. Revenue/Post</h5>
                    <h3>$40</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Growth Rate</h5>
                    <h3>+5.1%</h3>
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
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Golden Post Revenue Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Post Title</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-01</td>
                        <td>John Doe</td>
                        <td>Golden Car Sale</td>
                        <td>$40</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-02</td>
                        <td>Jane Smith</td>
                        <td>Golden House Listing</td>
                        <td>$60</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-03</td>
                        <td>Ali Hassan</td>
                        <td>Golden Job Offer</td>
                        <td>$30</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                    </tr>
                    <tr>
                        <td>2024-06-04</td>
                        <td>Maria Garcia</td>
                        <td>Golden Device Sale</td>
                        <td>$50</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 