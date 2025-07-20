@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-shield text-primary mr-2"></i> Roles Management</h1>
        <div>
            <a href="{{ route('roles.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Role
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Advanced Roles Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-list mr-2"></i> Roles List
            </div>
            <div class="card-tools d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)">
                    <i class="fas fa-file-export mr-1"></i> Export
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
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
            <table class="table table-hover table-striped table-bordered align-middle" id="admin-table-{{ uniqid() }}">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <span class="badge badge-secondary">{{ $role->id }}</span>
                            </td>
                            <td>
                                <strong>{{ ucfirst($role->name) }}</strong><br>
                                <small class="text-muted">{{ $role->display_name ?? '' }}</small>
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit($role->description ?? 'No description', 50) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $role->users_count ?? 0 }} users</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $role->permissions_count ?? 0 }} permissions</span>
                                @if($role->permissions_count == 0)
                                    <br><small class="text-muted">({{ $role->permissions->count() }} actual)</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $role->created_at ? $role->created_at->format('M d, Y') : '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $isSystemRole = in_array($role->name, ['superadmin', 'admin']);
                                    $editDisabled = $isSystemRole ? 'disabled' : '';
                                    $deleteDisabled = $isSystemRole ? 'disabled' : '';
                                @endphp
                                <div class="btn-group" role="group">
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-xs btn-outline-primary {{ $editDisabled }}" data-toggle="tooltip" title="{{ $isSystemRole ? 'System roles cannot be edited' : 'Edit Role' }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger {{ $deleteDisabled }}" data-toggle="tooltip" title="{{ $isSystemRole ? 'System roles cannot be deleted' : 'Delete Role' }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No roles found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing <strong>{{ $roles->firstItem() }}</strong> to <strong>{{ $roles->lastItem() }}</strong> of <strong>{{ $roles->total() }}</strong> records
                    </div>
                    <div>
                        {{ $roles->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@stop

@push('css')
<style>
    .table thead th { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr:hover {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        transform: scale(1.01);
        transition: all 0.3s ease;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .btn-group .btn {
        margin-right: 2px;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }
    
    .card-title {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-top: 1px solid #f8f9fa;
    }
    

    
    /* Animation for badges */
    .badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
</style>
@endpush

@push('js')
<script>
    function printTable(btn) {
        let table = btn.closest('.card').querySelector('table');
        let w = window.open();
        w.document.write('<html><head><title>Print Table</title>');
        w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">');
        w.document.write('</head><body>');
        w.document.write(table.outerHTML);
        w.document.write('</body></html>');
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
        a.download = 'roles-export.csv';
        a.click();
    }
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Add row hover effects
        $('.table tbody tr').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
        
        // Add click effects to buttons
        $('.btn').click(function() {
            $(this).addClass('btn-clicked');
            setTimeout(() => {
                $(this).removeClass('btn-clicked');
            }, 200);
        });
    });
    
    // Custom CSS for button click effect
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .btn-clicked {
                transform: scale(0.95) !important;
                transition: transform 0.1s ease !important;
            }
        `)
        .appendTo('head');
</script>
@endpush
