@extends('adminlte::page')

@section('title', 'Point Packages')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Point Packages Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Point Packages</li>
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
                            <h3>{{ $packages->count() }}</h3>
                            <p>Total Packages</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-gift"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $packages->where('is_active', true)->count() }}</h3>
                            <p>Active Packages</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $packages->where('is_popular', true)->count() }}</h3>
                            <p>Popular Packages</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $packages->where('is_active', false)->count() }}</h3>
                            <p>Inactive Packages</p>
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
                                <i class="fas fa-gift mr-2"></i>
                                Point Packages
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.point_packages.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Add New Package
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="packages-table">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="80">Icon</th>
                                            <th>Name (AR)</th>
                                            <th>Name (EN)</th>
                                            <th>Points</th>
                                            <th>Price</th>
                                            <th>Discount</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($packages as $package)
                                            <tr>
                                                <td>{{ $package->id }}</td>
                                                <td>
                                                    @if($package->icon)
                                                        <i class="{{ $package->icon }}" style="font-size: 18px; color: {{ $package->color ?? '#007bff' }};"></i>
                                                    @else
                                                        <i class="fas fa-gift" style="font-size: 18px; color: {{ $package->color ?? '#007bff' }};"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $package->name['ar'] ?? 'N/A' }}</td>
                                                <td>{{ $package->name['en'] ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge badge-primary">{{ number_format($package->points) }} pts</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">{{ $package->price }} {{ $package->currency }}</span>
                                                </td>
                                                <td>
                                                    @if($package->discount_percentage > 0)
                                                        <span class="badge badge-warning">-{{ $package->discount_percentage }}%</span>
                                                    @else
                                                        <span class="badge badge-secondary">No Discount</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $package->duration_days }} days</span>
                                                </td>
                                                <td>
                                                    @if($package->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                    @if($package->is_popular)
                                                        <span class="badge badge-warning ml-1">Popular</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.point_packages.edit', $package->id) }}" 
                                                           class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="{{ route('admin.point_packages.show', $package->id) }}" 
                                                           class="btn btn-sm btn-secondary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form action="{{ route('admin.point_packages.destroy', $package->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this package?')"
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
                                                        <i class="fas fa-gift fa-3x mb-3"></i>
                                                        <p>No point packages found. Create your first package to get started.</p>
                                                        <a href="{{ route('admin.point_packages.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus mr-1"></i> Add First Package
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
    $('#packages-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[0, "desc"]], // Sort by ID descending
        "pageLength": 25,
        "language": {
            "search": "Search packages:",
            "lengthMenu": "Show _MENU_ packages per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ packages",
            "infoEmpty": "Showing 0 to 0 of 0 packages",
            "infoFiltered": "(filtered from _MAX_ total packages)"
        }
    });
});
</script>
@endpush
@endsection 