@extends('adminlte::page')

@section('title', 'Badge Types Management - Talabna Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-award text-primary mr-2"></i>
                Badge Types Management
            </h1>
            <p class="text-muted mb-0">Manage badge types for service posts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('badge_types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add New Badge Type
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-award"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Badge Types</span>
                    <span class="info-box-number">{{ $badgeTypes->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Badges</span>
                    <span class="info-box-number">{{ $badgeTypes->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Posts with Badges</span>
                    <span class="info-box-number">{{ $statistics['total_badged_posts'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info shadow-sm">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Points Used</span>
                    <span class="info-box-number">{{ number_format($statistics['total_points_used'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('badge_types.create') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-plus mr-2"></i> Create New Badge Type
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <form action="{{ route('badge_types.migrate') }}" method="POST" class="d-inline w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-block" onclick="return confirm('This will migrate old badge data to the new system. Continue?')">
                                    <i class="fas fa-sync mr-2"></i> Migrate Old Badges
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('statistics.index') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-chart-bar mr-2"></i> View Statistics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Badge Types Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-award mr-2"></i> Badge Types
                <span class="badge badge-primary ml-2">{{ $badgeTypes->count() }}</span>
            </h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle" id="badge-types-table">
                <thead class="thead-light">
                    <tr>
                        <th width="50">#</th>
                        <th width="80">Icon</th>
                        <th>Name (AR)</th>
                        <th>Name (EN)</th>
                        <th>Slug</th>
                        <th>Color</th>
                        <th>Points/Day</th>
                        <th>View Boost</th>
                        <th>Priority</th>
                        <th>Posts</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($badgeTypes as $badge)
                        <tr data-badge-id="{{ $badge->id }}">
                            <td>
                                <span class="badge badge-secondary">{{ $badge->id }}</span>
                            </td>
                            <td class="text-center">
                                <i class="{{ $badge->icon }}" style="color: {{ $badge->color }}; font-size: 24px;"></i>
                            </td>
                            <td>
                                <strong>{{ $badge->name_ar }}</strong>
                                @if($badge->is_default)
                                    <span class="badge badge-info ml-1">Default</span>
                                @endif
                            </td>
                            <td>{{ $badge->name_en }}</td>
                            <td><code>{{ $badge->slug }}</code></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded mr-2" style="background-color: {{ $badge->color }}; width: 20px; height: 20px;"></div>
                                    <span class="text-sm">{{ $badge->color }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $badge->points_per_day > 0 ? 'warning' : 'secondary' }}">
                                    {{ $badge->points_per_day }} pts/day
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $badge->view_boost_percent > 0 ? 'info' : 'secondary' }}">
                                    +{{ $badge->view_boost_percent }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $badge->priority }}</span>
                            </td>
                            <td>
                                <span class="badge badge-dark">{{ $badge->service_posts_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $badge->is_active ? 'success' : 'danger' }}" id="status-{{ $badge->id }}">
                                    {{ $badge->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('badge_types.edit', $badge->id) }}"
                                       class="btn btn-outline-primary" title="Edit Badge">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-{{ $badge->is_active ? 'warning' : 'success' }} toggle-status-btn"
                                            data-badge-id="{{ $badge->id }}"
                                            data-current-status="{{ $badge->is_active ? '1' : '0' }}"
                                            title="{{ $badge->is_active ? 'Deactivate' : 'Activate' }}"
                                            {{ $badge->is_default && $badge->is_active ? 'disabled' : '' }}>
                                        <i class="fas fa-{{ $badge->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    @if(!$badge->is_default)
                                        <form action="{{ route('badge_types.set_default', $badge->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="Set as Default">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('badge_types.show', $badge->id) }}"
                                       class="btn btn-outline-secondary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$badge->is_default && ($badge->service_posts_count ?? 0) == 0)
                                        <form action="{{ route('badge_types.destroy', $badge->id) }}"
                                              method="POST" class="d-inline delete-badge-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Badge">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-award fa-4x mb-4 text-muted"></i>
                                    <h4 class="text-muted">No Badge Types Found</h4>
                                    <p class="text-muted mb-4">Create your first badge type to get started.</p>
                                    <a href="{{ route('badge_types.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus mr-2"></i> Create First Badge Type
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Badge Usage by Type -->
    @if($badgeTypes->count() > 0)
    <div class="card card-outline card-info shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie mr-2"></i> Badge Usage Overview
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($badgeTypes as $badge)
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card h-100" style="border-left: 4px solid {{ $badge->color }};">
                        <div class="card-body text-center">
                            <i class="{{ $badge->icon }}" style="color: {{ $badge->color }}; font-size: 32px;"></i>
                            <h5 class="mt-2">{{ $badge->name_en }}</h5>
                            <h3 class="text-bold">{{ $badge->service_posts_count ?? 0 }}</h3>
                            <small class="text-muted">Active Posts</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#badge-types-table').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[8, "asc"]], // Sort by priority
        "pageLength": 25,
        "language": {
            "search": "Search badges:",
            "lengthMenu": "Show _MENU_ badges per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ badges",
            "infoEmpty": "Showing 0 to 0 of 0 badges",
            "infoFiltered": "(filtered from _MAX_ total badges)"
        }
    });

    // Toggle status functionality
    $('.toggle-status-btn').on('click', function() {
        const badgeId = $(this).data('badge-id');
        const currentStatus = $(this).data('current-status');
        const button = $(this);
        const statusBadge = $(`#status-${badgeId}`);

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${currentStatus == '1' ? 'deactivate' : 'activate'} this badge type?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/badge_types/${badgeId}/toggle-active`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            const newStatus = response.is_active;
                            const newStatusText = newStatus ? 'Active' : 'Inactive';
                            const newBadgeClass = newStatus ? 'badge-success' : 'badge-danger';

                            statusBadge.removeClass('badge-success badge-danger').addClass(newBadgeClass).text(newStatusText);

                            button.data('current-status', newStatus ? '1' : '0');
                            button.removeClass('btn-outline-success btn-outline-warning').addClass(newStatus ? 'btn-outline-warning' : 'btn-outline-success');
                            button.find('i').removeClass('fa-play fa-pause').addClass(newStatus ? 'fa-pause' : 'fa-play');
                            button.attr('title', newStatus ? 'Deactivate' : 'Activate');

                            Swal.fire('Success!', response.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    });

    // Delete badge functionality
    $('.delete-badge-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
});
</script>
@endpush
