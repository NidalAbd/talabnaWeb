@extends('adminlte::page')

@section('title', 'Level Management - Talabna Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-layer-group text-primary mr-2"></i> 
                Level Management
            </h1>
            <p class="text-muted mb-0">{{ __('admin\levels\index.manage_dynamic_service_post_levels_and_t') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="exportLevels()">
                <i class="fas fa-file-export mr-1"></i> Export
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="printLevels()">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('levels.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add New Level
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">{{ __('admin\levels\index._') }}</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">{{ __('admin\levels\index._') }}</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- Level Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('admin\levels\index.total_levels') }}</span>
                    <span class="info-box-number">{{ $levels->field</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All available levels
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('admin\levels\index.active_levels') }}</span>
                    <span class="info-box-number">{{ $levels->field</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $levels->field</div></div>
                    <span class="progress-description">
                        <i class="fas fa-check-circle text-light"></i> {{ $levels->count() > 0 ? number_format(($levels->where('is_active', true)->count() / $levels->count()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('admin\levels\index.premium_levels') }}</span>
                    <span class="info-box-number">{{ $levels->field</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $levels->field</div></div>
                    <span class="progress-description">
                        <i class="fas fa-star text-light"></i> {{ $levels->count() > 0 ? number_format(($levels->where('is_premium', true)->count() / $levels->count()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info shadow-sm">
                <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('admin\levels\index.avg_view_boost') }}</span>
                    <span class="info-box-number">{{ number_format($levels->field</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> Average boost across all levels
                    </span>
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
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-block" onclick="bulkActivate()">
                                <i class="fas fa-play mr-2"></i> Activate All
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-warning btn-block" onclick="bulkDeactivate()">
                                <i class="fas fa-pause mr-2"></i> Deactivate All
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-info btn-block" onclick="reorderLevels()">
                                <i class="fas fa-sort mr-2"></i> Reorder Levels
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <button type="button" class="btn btn-outline-success btn-block" onclick="duplicateLevel()">
                                <i class="fas fa-copy mr-2"></i> Duplicate Level
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Levels Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-layer-group mr-2"></i> Dynamic Levels
                <span class="badge badge-primary ml-2">{{ $levels->field</span>
            </div>
            <div class="card-tools d-flex align-items-center">
                <div class="input-group input-group-sm mr-3" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Search levels..." id="levelSearch">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                <div class="btn-group btn-group-sm mr-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary active" onclick="toggleView('table')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <a href="{{ route('levels.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add New Level
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle" id="levels-table">
                <thead class="thead-light">
                    <tr>
                        <th width="50">{{ __('admin\levels\index._') }}</th>
                        <th width="80">{{ __('admin\levels\index.icon') }}</th>
                        <th>{{ __('admin\levels\index.name_ar_') }}</th>
                        <th>{{ __('admin\levels\index.name_en_') }}</th>
                        <th>{{ __('admin\levels\index.color') }}</th>
                        <th>{{ __('admin\levels\index.points_day') }}</th>
                        <th>{{ __('admin\levels\index.view_boost') }}</th>
                        <th>{{ __('admin\levels\index.order') }}</th>
                        <th>{{ __('admin\levels\index.status') }}</th>
                        <th width="180">{{ __('admin\levels\index.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($levels as $level)
                        <tr data-level-id="{{ $level->id }}">
                            <td>
                                <span class="badge badge-secondary">{{ $level->field</span>
                            </td>
                            <td>
                                @if($level->icon)
                                    <i class="{{ $level->icon }}" style="color: {{ $level->color }}; font-size: 18px;"></i>
                                @else
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $level->field</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $level->field</strong>
                                @if($level->is_premium)
                                    <span class="badge badge-warning ml-1">{{ __('admin\levels\index.premium') }}</span>
                                @endif
                            </td>
                            <td>{{ $level->field</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $level->field</div>
                                    <span class="text-sm">{{ $level->field</span>
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
                                <span class="badge badge-primary">{{ $level->field</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $level->is_active ? 'success' : 'danger' }}" id="status-{{ $level->id }}">
                                    {{ $level->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('levels.edit', $level->id) }}" 
                                       class="btn btn-outline-primary" title="Edit Level">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-{{ $level->is_active ? 'warning' : 'success' }} toggle-status-btn"
                                            data-level-id="{{ $level->id }}"
                                            data-current-status="{{ $level->is_active ? '1' : '0' }}"
                                            title="{{ $level->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $level->field</i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-info" 
                                            onclick="viewLevelDetails({{ $level->id }})"
                                            title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-secondary" 
                                            onclick="duplicateLevel({{ $level->id }})"
                                            title="Duplicate Level">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <form action="{{ route('levels.destroy', $level->id) }}" 
                                          method="POST" class="d-inline delete-level-form"
                                          data-level-id="{{ $level->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                title="Delete Level">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-layer-group fa-4x mb-4 text-muted"></i>
                                    <h4 class="text-muted">{{ __('admin\levels\index.no_levels_found') }}</h4>
                                    <p class="text-muted mb-4">{{ __('admin\levels\index.create_your_first_level_to_get_started_w') }}</p>
                                    <a href="{{ route('levels.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus mr-2"></i> Create First Level
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

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
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

    // Search functionality
    $('#levelSearch').on('keyup', function() {
        $('#levels-table').DataTable().search(this.value).draw();
    });

    // Toggle status functionality
    $('.toggle-status-btn').on('click', function() {
        const levelId = $(this).data('level-id');
        const currentStatus = $(this).data('current-status');
        const button = $(this);
        const statusBadge = $(`#status-${levelId}`);
        
        // Show confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${currentStatus == '1' ? 'deactivate' : 'activate'} this level?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: `{{ route('admin.levels.toggleActive', '') }}/${levelId}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update UI
                            const newStatus = response.is_active;
                            const newStatusText = newStatus ? 'Active' : 'Inactive';
                            const newBadgeClass = newStatus ? 'badge-success' : 'badge-danger';
                            
                            statusBadge.removeClass('badge-success badge-danger').addClass(newBadgeClass).text(newStatusText);
                            
                            // Update button
                            button.data('current-status', newStatus ? '1' : '0');
                            button.removeClass('btn-outline-success btn-outline-warning').addClass(newStatus ? 'btn-outline-warning' : 'btn-outline-success');
                            button.find('i').removeClass('fa-play fa-pause').addClass(newStatus ? 'fa-pause' : 'fa-play');
                            button.attr('title', newStatus ? 'Deactivate' : 'Activate');
                            
                            // Show success message
                            Swal.fire(
                                'Success!',
                                response.message,
                                'success'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Something went wrong. Please try again.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Delete level functionality
    $('.delete-level-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const levelId = form.data('level-id');
        
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
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Remove the row with animation
                            form.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                // Reload DataTable
                                $('#levels-table').DataTable().ajax.reload();
                            });
                            
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                        }
                    },
                    error: function(xhr) {
                        let message = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        Swal.fire(
                            'Error!',
                            message,
                            'error'
                        );
                    }
                });
            }
        });
    });
});

// Additional functions
function exportLevels() {
    Swal.fire({
        title: 'Export Levels',
        text: 'Choose export format:',
        icon: 'info',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Export to Excel
            window.open('/admin/levels/export?format=excel', '_blank');
        } else if (result.isDenied) {
            // Export to PDF
            window.open('/admin/levels/export?format=pdf', '_blank');
        }
    });
}

function printLevels() {
    window.print();
}

function bulkActivate() {
    Swal.fire({
        title: 'Activate All Levels',
        text: 'This will activate all inactive levels. Continue?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, activate all!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.levels.bulk-activate") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to activate levels.', 'error');
                }
            });
        }
    });
}

function bulkDeactivate() {
    Swal.fire({
        title: 'Deactivate All Levels',
        text: 'This will deactivate all active levels. Continue?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, deactivate all!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.levels.bulk-deactivate") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to deactivate levels.', 'error');
                }
            });
        }
    });
}

function reorderLevels() {
    Swal.fire({
        title: 'Reorder Levels',
        text: 'Drag and drop to reorder levels:',
        icon: 'info',
        html: '<div id="reorder-container">{{ __('admin\levels\index.reorder_functionality_will_be_implemente') }}</div>',
        showCancelButton: true,
        confirmButtonText: 'Save Order'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement save order functionality
            $.ajax({
                url: '{{ route("admin.levels.update-order") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order: [] // Will be populated with actual order data
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to update level order.', 'error');
                }
            });
        }
    });
}

function duplicateLevel(levelId = null) {
    if (levelId) {
        // Duplicate specific level
        Swal.fire({
            title: 'Duplicate Level',
            text: 'Create a copy of this level?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, duplicate!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.levels.duplicate", "") }}/' + levelId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to duplicate level.', 'error');
                    }
                });
            }
        });
    } else {
        // Duplicate from template
        Swal.fire({
            title: 'Duplicate Level',
            text: 'Select a level to duplicate:',
            icon: 'info',
            html: '<select class="form-control" id="levelSelect">{{ __('admin\levels\index._') }}<option value="">{{ __('admin\levels\index.select_a_level_') }}</option>{{ __('admin\levels\index._foreach_levels') }}<option value="{{ $level->id }}">{{ $level->name["en"] ?? "N/A" }}</option>{{ __('admin\levels\index._endforeach_') }}</select>',
            showCancelButton: true,
            confirmButtonText: 'Duplicate'
        }).then((result) => {
            if (result.isConfirmed) {
                const selectedLevel = $('#levelSelect').val();
                if (selectedLevel) {
                    duplicateLevel(selectedLevel);
                }
            }
        });
    }
}

function viewLevelDetails(levelId) {
    // Get level data from the table row
    const row = $(`tr[data-level-id="${levelId}"]`);
    const nameAr = row.find('td:eq(2) strong').text();
    const nameEn = row.find('td:eq(3)').text();
    const color = row.find('td:eq(4) span').text();
    const pointsPerDay = row.find('td:eq(5) .badge').text();
    const viewBoost = row.find('td:eq(6) .badge').text();
    const displayOrder = row.find('td:eq(7) .badge').text();
    const status = row.find('td:eq(8) .badge').text();
    
    Swal.fire({
        title: 'Level Details',
        html: `
            <div class="text-left">
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.arabic_name_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._namear_') }}</div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.english_name_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._nameen_') }}</div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.color_') }}</strong></div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="w-3 h-3 rounded mr-2" style="background-color: ${color};"></div>
                            <span>{{ __('admin\levels\index._color_') }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.points_per_day_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._pointsperday_') }}</div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.view_boost_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._viewboost_') }}</div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.display_order_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._displayorder_') }}</div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>{{ __('admin\levels\index.status_') }}</strong></div>
                    <div class="col-6">{{ __('admin\levels\index._status_') }}</div>
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
    });
}

function toggleView(view) {
    if (view === 'grid') {
        // Implement grid view
        Swal.fire('Grid View', 'Grid view functionality will be implemented.', 'info');
    } else {
        // Table view is already active
    }
}
</script>
@endpush
@endsection 