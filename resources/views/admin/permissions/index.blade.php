@extends('adminlte::page')

@section('title', 'Permissions Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-key text-primary mr-2"></i> Permissions Management</h1>
        <div>
            <a href="{{ route('permissions.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Permission
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Permission Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-key"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\permissions\index.total_permissions') }}</span>
                    <span class="info-box-number">{{ number_format($permissions->total()) }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All system permissions
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\permissions\index.total_roles') }}</span>
                    <span class="info-box-number">{{ number_format($rolesCount ?? 0) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-users text-light"></i> Roles with permissions
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info shadow-sm">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\permissions\index.total_users') }}</span>
                    <span class="info-box-number">{{ number_format($usersCount ?? 0) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-user text-light"></i> Users with permissions
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-cog"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\permissions\index.system_permissions') }}</span>
                    <span class="info-box-number">{{ $systemPermissionsCount ?? 0 }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-exclamation-triangle text-light"></i> Protected permissions
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-list mr-2"></i> Permissions List
            </div>
            <div class="card-tools d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)"><i class="fas fa-file-export mr-1"></i> Export</button>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)"><i class="fas fa-print mr-1"></i> Print</button>
                <form method="GET" class="d-flex align-items-center mb-2 mb-md-0" style="gap: 0.5rem;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="?" class="btn btn-sm btn-secondary ml-1">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>{{('admin\permissions\index.id') }}</th>
                        <th>{{('admin\permissions\index.name') }}</th>
                        <th>{{('admin\permissions\index.display_name') }}</th>
                        <th>{{('admin\permissions\index.description') }}</th>
                        <th>{{('admin\permissions\index.roles') }}</th>
                        <th>{{('admin\permissions\index.users') }}</th>
                        <th>{{('admin\permissions\index.created') }}</th>
                        <th>{{('admin\permissions\index.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $permission->id }}</span></td>
                            <td><strong>{{ $permission->id }}</strong></td>
                            <td><span class="text-muted">{{ Str::limit($permission->id) }}</span></td>
                            <td><span class="text-muted">{{ Str::limit($permission->id) }}</span></td>
                            <td><span class="badge badge-primary">{{ $permission->id }}</span></td>
                            <td><span class="badge badge-info">{{ $permission->id }}</span></td>
                            <td><span class="text-muted">{{ $permission->id }}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('permissions.show', $permission->id) }}"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('permissions.edit', $permission->id) }}"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('permissions.destroy', $permission->count()) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No permissions found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($permissions->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing <strong>{{ $permissions->firstItem() }}</strong> to <strong>{{ $permissions->lastItem() }}</strong> of <strong>{{ $permissions->total() }}</strong> records
                    </div>
                    <div>
                        {{ $permissions->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@push('css')
<style>
    .table thead th { background: #f8f9fa; }
    .table td, .table th { vertical-align: middle !important; }
    .card { transition: box-shadow 0.2s; }
    .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
    .badge-pink { background: #e83e8c; color: #fff; }
    .img-circle { border-radius: 50%; }
</style>
@endpush

@push('js')
<script>
    function printTable(btn) {
        let table = btn.closest('.card').querySelector('table');
        let w = window.open();
        w.document.write('<html><head><title>{{('admin\permissions\index.print_table') }}</title>{{('admin\permissions\index._w_document_write_') }}<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">{{('admin\permissions\index._w_document_write_') }}</head><body>{{('admin\permissions\index._w_document_write_table_outer') }}</body></html>');
        w.print();
        w.close();
    }
    
    function exportTable(btn) {
        // Simple CSV export
        let table = btn.closest('.card').querySelector('table');
        let rows = Array.from(table.rows);
        let csv = rows.map(row => Array.from(row.cells).map(cell => '"' + cell.innerText.replace(/"/g, '""') + '"').join(',')).join('\n');
        let blob = new Blob([csv], { type: 'text/csv' });
        let a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'permissions-export.csv';
        a.click();
    }
</script>
@endpush







