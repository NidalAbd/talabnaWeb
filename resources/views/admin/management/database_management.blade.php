@extends('adminlte::page')
@section('title', 'Database Management')
@section('content_header')
    <h1><i class="fas fa-database text-primary mr-2"></i> Database Management</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\management\database_management.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\management\database_management.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{('admin\management\database_management.total_tables') }}</h5>
                    <h3>{{('admin\management\database_management.42') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\management\database_management.last_backup') }}</h5>
                    <h3>{{('admin\management\database_management.2024_06_20') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{('admin\management\database_management.pending_migrations') }}</h5>
                    <h3>{{('admin\management\database_management.2') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{('admin\management\database_management.errors') }}</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\management\database_management.action_') }}</label>
                <select class="form-control mr-2" name="action">
                    <option>{{('admin\management\database_management.view_logs') }}</option>
                    <option>{{('admin\management\database_management.backup_now') }}</option>
                    <option>{{('admin\management\database_management.restore') }}</option>
                    <option>{{('admin\management\database_management.run_migration') }}</option>
                </select>
                <button class="btn btn-primary mr-2">{{('admin\management\database_management.execute') }}</button>
                <button class="btn btn-secondary">{{('admin\management\database_management.export_logs') }}</button>
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
                        <th>{{('admin\management\database_management.date') }}</th>
                        <th>{{('admin\management\database_management.action') }}</th>
                        <th>{{('admin\management\database_management.user') }}</th>
                        <th>{{('admin\management\database_management.status') }}</th>
                        <th>{{('admin\management\database_management.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\management\database_management.2024_06_20') }}</td>
                        <td>{{('admin\management\database_management.backup') }}</td>
                        <td>{{('admin\management\database_management.admin') }}</td>
                        <td><span class="badge badge-success">{{('admin\management\database_management.success') }}</span></td>
                        <td>{{('admin\management\database_management.full_backup_completed') }}</td>
                    </tr>
                    <tr>
                        <td>{{('admin\management\database_management.2024_06_18') }}</td>
                        <td>{{('admin\management\database_management.migration') }}</td>
                        <td>{{('admin\management\database_management.developer') }}</td>
                        <td><span class="badge badge-success">{{('admin\management\database_management.success') }}</span></td>
                        <td>{{('admin\management\database_management.added_new_table_analytics') }}</td>
                    </tr>
                    <tr>
                        <td>{{('admin\management\database_management.2024_06_15') }}</td>
                        <td>{{('admin\management\database_management.restore') }}</td>
                        <td>{{('admin\management\database_management.admin') }}</td>
                        <td><span class="badge badge-warning">{{('admin\management\database_management.partial') }}</span></td>
                        <td>{{('admin\management\database_management.restored_users_and_posts_tables') }}</td>
                    </tr>
                    <tr>
                        <td>{{('admin\management\database_management.2024_06_10') }}</td>
                        <td>{{('admin\management\database_management.backup') }}</td>
                        <td>{{('admin\management\database_management.admin') }}</td>
                        <td><span class="badge badge-danger">{{('admin\management\database_management.failed') }}</span></td>
                        <td>{{('admin\management\database_management.disk_full') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






