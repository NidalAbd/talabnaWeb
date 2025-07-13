@extends('adminlte::page')

@section('title', 'Premium Features')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Premium Features Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Premium Features</li>
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

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $features->count() }}</h3>
                            <p>Total Features</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $features->where('is_active', true)->count() }}</h3>
                            <p>Active Features</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $features->where('is_popular', true)->count() }}</h3>
                            <p>Popular Features</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $features->where('is_active', false)->count() }}</h3>
                            <p>Inactive Features</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-crown mr-2"></i>
                                Premium Features
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.premium-features.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Add New Feature
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="features-table">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="80">Icon</th>
                                            <th>Name (AR)</th>
                                            <th>Name (EN)</th>
                                            <th>Category</th>
                                            <th>Points Cost</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($features as $feature)
                                            <tr>
                                                <td>{{ $feature->id }}</td>
                                                <td>
                                                    @if($feature->icon)
                                                        <i class="{{ $feature->icon }}" style="font-size: 18px; color: {{ $feature->color ?? '#ffc107' }};"></i>
                                                    @else
                                                        <i class="fas fa-crown" style="font-size: 18px; color: {{ $feature->color ?? '#ffc107' }};"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $feature->name['ar'] ?? 'N/A' }}</td>
                                                <td>{{ $feature->name['en'] ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge badge-info">{{ $feature->category }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ number_format($feature->points_cost) }} pts</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">{{ $feature->duration_days }} days</span>
                                                </td>
                                                <td>
                                                    @if($feature->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                    @if($feature->is_popular)
                                                        <span class="badge badge-warning ml-1">Popular</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.premium-features.edit', $feature->id) }}" 
                                                           class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="{{ route('admin.premium-features.show', $feature->id) }}" 
                                                           class="btn btn-sm btn-secondary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form action="{{ route('admin.premium-features.destroy', $feature->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this feature?')"
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-crown fa-3x mb-3"></i>
                                                        <p>No premium features found. Create your first feature to get started.</p>
                                                        <a href="{{ route('admin.premium-features.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus mr-1"></i> Add First Feature
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
    $('#features-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[0, "desc"]], // Sort by ID descending
        "pageLength": 25,
        "language": {
            "search": "Search features:",
            "lengthMenu": "Show _MENU_ features per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ features",
            "infoEmpty": "Showing 0 to 0 of 0 features",
            "infoFiltered": "(filtered from _MAX_ total features)"
        }
    });
});
</script>
@endpush
@endsection 