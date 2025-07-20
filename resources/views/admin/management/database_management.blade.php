@extends('adminlte::page')
@section('title', 'Database Management')
@section('content_header')
    <h1><i class="fas fa-database text-primary mr-2"></i> Database Management</h1>
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
                    <h5>Total Tables</h5>
                    <h3>42</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Last Backup</h5>
                    <h3>2024-06-20</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending Migrations</h5>
                    <h3>2</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Errors</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">Action:</label>
                <select class="form-control mr-2" name="action">
                    <option>View Logs</option>
                    <option>Backup Now</option>
                    <option>Restore</option>
                    <option>Run Migration</option>
                </select>
                <button class="btn btn-primary mr-2">Execute</button>
                <button class="btn btn-secondary">Export Logs</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Database Management Logs</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-06-20</td>
                        <td>Backup</td>
                        <td>Admin</td>
                        <td><span class="badge badge-success">Success</span></td>
                        <td>Full backup completed</td>
                    </tr>
                    <tr>
                        <td>2024-06-18</td>
                        <td>Migration</td>
                        <td>Developer</td>
                        <td><span class="badge badge-success">Success</span></td>
                        <td>Added new table: analytics</td>
                    </tr>
                    <tr>
                        <td>2024-06-15</td>
                        <td>Restore</td>
                        <td>Admin</td>
                        <td><span class="badge badge-warning">Partial</span></td>
                        <td>Restored users and posts tables</td>
                    </tr>
                    <tr>
                        <td>2024-06-10</td>
                        <td>Backup</td>
                        <td>Admin</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                        <td>Disk full</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 