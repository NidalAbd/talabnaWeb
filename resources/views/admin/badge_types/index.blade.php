@extends('adminlte::page')

@section('title', 'Badge Types Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-award text-primary mr-2"></i> Badge Types Management</h1>
        <div>
            <a href="{{ route('badge_types.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Badge Type
            </a>
            <form action="{{ route('badge_types.migrate') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning ml-2" onclick="return confirm('Migrate old badge data?')">
                    <i class="fas fa-sync mr-1"></i> Migrate
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Main Card with Table -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Badge Types
                    <span class="badge badge-primary ml-2">{{ $badgeTypes->count() }}</span>
                    <span class="badge badge-success ml-1">{{ $badgeTypes->where('is_active', true)->count() }} Active</span>
                </h3>
                <div class="card-tools">
                    <form action="{{ route('badge_types.index') }}" method="GET" class="form-inline">
                        <select class="form-control form-control-sm mr-2" name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @if(request('search') || request('status'))
                            <a href="{{ route('badge_types.index') }}" class="btn btn-sm btn-default ml-2">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th style="width: 50px">Icon</th>
                            <th>Name (EN)</th>
                            <th>Name (AR)</th>
                            <th>Slug</th>
                            <th style="width: 80px">Color</th>
                            <th style="width: 80px">Pts/Day</th>
                            <th style="width: 80px">Boost</th>
                            <th style="width: 60px">Priority</th>
                            <th style="width: 60px">Posts</th>
                            <th style="width: 80px">Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($badgeTypes as $badge)
                            <tr>
                                <td>{{ $badge->id }}</td>
                                <td class="text-center">
                                    <i class="{{ $badge->icon }}" style="color: {{ $badge->color }}; font-size: 20px;"></i>
                                </td>
                                <td>
                                    <strong>{{ $badge->name_en }}</strong>
                                    @if($badge->is_default)
                                        <span class="badge badge-info ml-1">Default</span>
                                    @endif
                                </td>
                                <td>{{ $badge->name_ar }}</td>
                                <td><code>{{ $badge->slug }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="rounded mr-1" style="background-color: {{ $badge->color }}; width: 16px; height: 16px; display: inline-block;"></span>
                                        <small>{{ $badge->color }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $badge->points_per_day > 0 ? 'warning' : 'secondary' }}">
                                        {{ $badge->points_per_day }}
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
                                    <span class="badge badge-{{ $badge->is_active ? 'success' : 'danger' }}">
                                        {{ $badge->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('badge_types.show', $badge->id) }}"
                                           class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('badge_types.edit', $badge->id) }}"
                                           class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn {{ $badge->is_active ? 'btn-secondary' : 'btn-success' }} toggle-status-btn"
                                                data-badge-id="{{ $badge->id }}"
                                                data-current-status="{{ $badge->is_active ? '1' : '0' }}"
                                                title="{{ $badge->is_active ? 'Deactivate' : 'Activate' }}"
                                                {{ $badge->is_default && $badge->is_active ? 'disabled' : '' }}>
                                            <i class="fas fa-{{ $badge->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        @if(!$badge->is_default)
                                            <form action="{{ route('badge_types.set_default', $badge->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary" title="Set as Default">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$badge->is_default && ($badge->service_posts_count ?? 0) == 0)
                                            <form action="{{ route('badge_types.destroy', $badge->id) }}"
                                                  method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <i class="fas fa-award fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No badge types found</p>
                                    <a href="{{ route('badge_types.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Create First Badge Type
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Toggle status functionality
            $('.toggle-status-btn').on('click', function() {
                const badgeId = $(this).data('badge-id');
                const currentStatus = $(this).data('current-status');
                const button = $(this);

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
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                if (response.success) {
                                    location.reload();
                                }
                            },
                            error: function(xhr) {
                                let message = 'Something went wrong.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', message, 'error');
                            }
                        });
                    }
                });
            });

            // Delete confirmation
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
