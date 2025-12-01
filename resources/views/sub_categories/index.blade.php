@extends('adminlte::page')

@section('title', 'Subcategories Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-sitemap text-primary mr-2"></i> Subcategories Management</h1>
        <div>
            <a href="{{ route('subcategories.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Subcategory
            </a>
            <a href="{{ route('categories.index') }}" class="btn btn-info ml-2">
                <i class="fas fa-list mr-1"></i> Categories
            </a>
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
                    <i class="fas fa-list mr-2"></i>Subcategories
                    <span class="badge badge-primary ml-2">{{ $subCategories->total() }}</span>
                </h3>
                <div class="card-tools">
                    <!-- Filters inline -->
                    <form action="{{ route('subcategories.index') }}" method="GET" class="form-inline" id="filter-form">
                        <select class="form-control form-control-sm mr-2" name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <select class="form-control form-control-sm mr-2" name="category_id" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name[app()->getLocale()] ?? $category->name['en'] ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @if(request('search') || request('status') || request('category_id'))
                            <a href="{{ route('subcategories.index') }}" class="btn btn-sm btn-default ml-2">
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
                            <th style="width: 60px">Image</th>
                            <th>Name (EN)</th>
                            <th>Name (AR)</th>
                            <th>Parent Category</th>
                            <th style="width: 80px">Status</th>
                            <th style="width: 80px">Posts</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subCategories as $subCategory)
                            <tr>
                                <td>{{ $subCategory->id }}</td>
                                <td>
                                    @if($subCategory->photos->count() > 0)
                                        <img src="{{ asset($subCategory->photos->first()->src) }}"
                                             class="img-thumbnail"
                                             alt="{{ $subCategory->name['en'] ?? 'Subcategory' }}"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-image"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $subCategory->name['en'] ?? 'N/A' }}</strong>
                                    @if($subCategory->is_featured ?? false)
                                        <i class="fas fa-star text-warning ml-1" title="Featured"></i>
                                    @endif
                                    @if($subCategory->is_popular ?? false)
                                        <i class="fas fa-fire text-danger ml-1" title="Popular"></i>
                                    @endif
                                </td>
                                <td>{{ $subCategory->name['ar'] ?? 'N/A' }}</td>
                                <td>
                                    @if($subCategory->category)
                                        {{ $subCategory->category->name[app()->getLocale()] ?? $subCategory->category->name['en'] ?? 'N/A' }}
                                    @else
                                        <span class="text-danger">No Category</span>
                                    @endif
                                </td>
                                <td>
                                    @if($subCategory->isSuspended)
                                        <span class="badge badge-danger">Suspended</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $subCategory->service_posts_count }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subcategories.show', $subCategory->id) }}"
                                           class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('subcategories.edit', $subCategory->id) }}"
                                           class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('subcategories.toggle-suspend', $subCategory->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                    class="btn {{ $subCategory->isSuspended ? 'btn-success' : 'btn-secondary' }}"
                                                    title="{{ $subCategory->isSuspended ? 'Activate' : 'Suspend' }}">
                                                <i class="fas {{ $subCategory->isSuspended ? 'fa-check' : 'fa-ban' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('subcategories.destroy', $subCategory->id) }}"
                                              method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No subcategories found</p>
                                    <a href="{{ route('subcategories.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Create First Subcategory
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($subCategories->hasPages())
                <div class="card-footer clearfix">
                    {{ $subCategories->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Confirmation dialog for delete
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete the subcategory!",
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
