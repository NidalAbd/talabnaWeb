@extends('adminlte::page')

@section('title', 'Manage Levels')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Level Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Levels</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group mr-2"></i>
                                Dynamic Levels
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.levels.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Add New Level
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="levels-table">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="80">Icon</th>
                                            <th>Name (AR)</th>
                                            <th>Name (EN)</th>
                                            <th>Color</th>
                                            <th>Points/Day</th>
                                            <th>View Boost</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($levels as $level)
                                            <tr>
                                                <td>{{ $level->id }}</td>
                                                <td>
                                                    @if($level->icon)
                                                        <i class="{{ $level->icon }}" style="color: {{ $level->color }}; font-size: 18px;"></i>
                                                    @else
                                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $level->color }};"></div>
                                                    @endif
                                                </td>
                                                <td>{{ $level->name['ar'] ?? 'N/A' }}</td>
                                                <td>{{ $level->name['en'] ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $level->color }};"></div>
                                                        <span class="text-sm">{{ $level->color }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $level->points_per_day > 0 ? 'warning' : 'secondary' }}">
                                                        {{ $level->points_per_day }} pts/day
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $level->view_boost_percentage > 0 ? 'info' : 'secondary' }}">
                                                        +{{ $level->view_boost_percentage }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $level->display_order }}</span>
                                                </td>
                                                <td>
                                                    @if($level->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.levels.edit', $level->id) }}" 
                                                           class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.levels.destroy', $level->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this level?')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-layer-group fa-3x mb-3"></i>
                                                        <p>No levels found. Create your first level to get started.</p>
                                                        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus mr-1"></i> Add First Level
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#levels-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[7, "asc"]], // Sort by display order
        "pageLength": 25,
        "language": {
            "search": "Search levels:",
            "lengthMenu": "Show _MENU_ levels per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ levels",
            "infoEmpty": "Showing 0 to 0 of 0 levels",
            "infoFiltered": "(filtered from _MAX_ total levels)"
        }
    });
});
</script>
@endpush
@endsection 