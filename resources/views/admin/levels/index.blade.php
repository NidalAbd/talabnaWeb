@extends('layouts.admin')

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

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $levels->count() }}</h3>
                            <p>Total Levels</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $levels->where('is_active', true)->count() }}</h3>
                            <p>Active Levels</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $levels->where('is_premium', true)->count() }}</h3>
                            <p>Premium Levels</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $levels->where('is_active', false)->count() }}</h3>
                            <p>Inactive Levels</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Levels Management -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group mr-2"></i>
                        Service Post Levels
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Add New Level
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="levels-table">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th width="80">Order</th>
                                    <th>Level</th>
                                    <th>Points/Day</th>
                                    <th>View Boost</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-levels">
                                @foreach($levels as $level)
                                <tr data-id="{{ $level->id }}" class="level-row">
                                    <td>{{ $level->id }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $level->display_order }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="level-badge mr-3" style="background-color: {{ $level->color }}">
                                                <i class="{{ $level->icon }}"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $level->localized_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $level->localized_description }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $level->points_per_day }} pts</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">+{{ $level->view_boost_percentage }}%</span>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-active" 
                                                   id="active_{{ $level->id }}" 
                                                   data-id="{{ $level->id }}"
                                                   {{ $level->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="active_{{ $level->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($level->is_premium)
                                            <span class="badge badge-warning">Premium</span>
                                        @else
                                            <span class="badge badge-secondary">Standard</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.levels.edit', $level) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.levels.show', $level) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($level->servicePosts()->count() == 0)
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-level" 
                                                    data-id="{{ $level->id }}" 
                                                    data-name="{{ $level->localized_name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the level "<span id="level-name"></span>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.level-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

#sortable-levels tr {
    cursor: move;
}

#sortable-levels tr.ui-sortable-helper {
    background: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.level-row:hover {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$(document).ready(function() {
    // Initialize sortable
    $("#sortable-levels").sortable({
        handle: "td",
        helper: function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function(index) {
                $(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        update: function(event, ui) {
            var levels = [];
            $("#sortable-levels tr").each(function(index) {
                levels.push({
                    id: $(this).data('id'),
                    display_order: index + 1
                });
            });
            
            $.ajax({
                url: '{{ route("admin.levels.updateOrder") }}',
                method: 'POST',
                data: {
                    levels: levels,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });

    // Toggle active status
    $('.toggle-active').change(function() {
        var levelId = $(this).data('id');
        var isChecked = $(this).is(':checked');
        
        $.ajax({
            url: '/admin/levels/' + levelId + '/toggle-active',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update UI if needed
                }
            },
            error: function() {
                // Revert the toggle if failed
                $(this).prop('checked', !isChecked);
            }
        });
    });

    // Delete level
    $('.delete-level').click(function() {
        var levelId = $(this).data('id');
        var levelName = $(this).data('name');
        
        $('#level-name').text(levelName);
        $('#delete-form').attr('action', '/admin/levels/' + levelId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush 