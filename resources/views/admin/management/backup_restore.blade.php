@extends('adminlte::page')
@section('title', 'Backup & Restore')
@section('content_header')
    <h1><i class="fas fa-save text-success mr-2"></i> Backup & Restore</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{ __('admin\management\backup_restore.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{ __('admin\management\backup_restore.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ __('admin\management\backup_restore.last_backup') }}</h5>
                    <h3>{{ __('admin\management\backup_restore.2024_06_20') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{ __('admin\management\backup_restore.total_backups') }}</h5>
                    <h3>{{ __('admin\management\backup_restore.15') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{ __('admin\management\backup_restore.pending_restores') }}</h5>
                    <h3>{{ __('admin\management\backup_restore.1') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{ __('admin\management\backup_restore.failed_backups') }}</h5>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{ __('admin\management\backup_restore.action_') }}</label>
                <select class="form-control mr-2" name="action">
                    <option>{{ __('admin\management\backup_restore.backup_now') }}</option>
                    <option>{{ __('admin\management\backup_restore.restore') }}</option>
                    <option>{{ __('admin\management\backup_restore.view_logs') }}</option>
                </select>
                <button class="btn btn-primary mr-2">{{ __('admin\management\backup_restore.execute') }}</button>
                <button class="btn btn-secondary">{{ __('admin\management\backup_restore.export_logs') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Backup & Restore Logs</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('admin\management\backup_restore.date') }}</th>
                        <th>{{ __('admin\management\backup_restore.action') }}</th>
                        <th>{{ __('admin\management\backup_restore.user') }}</th>
                        <th>{{ __('admin\management\backup_restore.status') }}</th>
                        <th>{{ __('admin\management\backup_restore.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('admin\management\backup_restore.2024_06_20') }}</td>
                        <td>{{ __('admin\management\backup_restore.backup') }}</td>
                        <td>{{ __('admin\management\backup_restore.admin') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\management\backup_restore.success') }}</span></td>
                        <td>{{ __('admin\management\backup_restore.full_backup_completed') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\management\backup_restore.2024_06_15') }}</td>
                        <td>{{ __('admin\management\backup_restore.restore') }}</td>
                        <td>{{ __('admin\management\backup_restore.admin') }}</td>
                        <td><span class="badge badge-warning">{{ __('admin\management\backup_restore.partial') }}</span></td>
                        <td>{{ __('admin\management\backup_restore.restored_users_and_posts_tables') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\management\backup_restore.2024_06_10') }}</td>
                        <td>{{ __('admin\management\backup_restore.backup') }}</td>
                        <td>{{ __('admin\management\backup_restore.admin') }}</td>
                        <td><span class="badge badge-danger">{{ __('admin\management\backup_restore.failed') }}</span></td>
                        <td>{{ __('admin\management\backup_restore.disk_full') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\management\backup_restore.2024_06_05') }}</td>
                        <td>{{ __('admin\management\backup_restore.backup') }}</td>
                        <td>{{ __('admin\management\backup_restore.admin') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\management\backup_restore.success') }}</span></td>
                        <td>{{ __('admin\management\backup_restore.incremental_backup_completed') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 