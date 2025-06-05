@extends('adminlte::page')
@section('title', "Categories Management")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags text-primary mr-2"></i> Categories Management</h1>
        <div>
            <a href="{{ route('categories.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Category
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-th-large text-primary mr-2"></i>
                            Categories
                        </h5>
                        <div>
                            <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i> Add New Category
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">Image</th>
                                    <th style="width: 25%">Category Name</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 15%">Subcategories</th>
                                    <th style="width: 15%">Posts</th>
                                    <th style="width: 10%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($categories) && count($categories) > 0)
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>{{ $category->id }}</td>
                                            <td class="text-center">
                                                @if($category->photos->count() > 0)
                                                    <img src="{{ asset($category->photos->first()->src) }}"
                                                         class="img-thumbnail" alt="{{ $category->name[app()->getLocale()] }}"
                                                         style="max-height: 50px;">
                                                @else
                                                    <span class="badge badge-secondary">No Image</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-weight-bold">{{ $category->name[app()->getLocale()] }}</div>
                                                <small class="text-muted">
                                                    @if(app()->getLocale() != 'en' && isset($category->name['en']))
                                                        ({{ $category->name['en'] }})
                                                    @elseif(app()->getLocale() != 'ar' && isset($category->name['ar']))
                                                        ({{ $category->name['ar'] }})
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($category->isSuspended)
                                                    <span class="badge badge-danger">Suspended</span>
                                                @else
                                                    <span class="badge badge-success">Active</span>
                                                @endif
                                            </td>
                                            <td>
                                            <span class="badge badge-info">
                                                {{ $category->sub_categories_with_service_posts_count ?? 0 }}
                                            </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $category->service_posts_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('categories.show', $category->id) }}"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('categories.edit', $category->id) }}"
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('categories.toggle-suspend', $category->id) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm {{ $category->isSuspended ? 'btn-success' : 'btn-secondary' }}"
                                                                title="{{ $category->isSuspended ? 'Activate' : 'Suspend' }}">
                                                            <i class="fas {{ $category->isSuspended ? 'fa-check' : 'fa-ban' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('categories.destroy', $category->id) }}"
                                                          method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                <h5 class="font-weight-normal text-muted">No categories found</h5>
                                                <a href="{{ route('categories.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus mr-1"></i> Create First Category
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-center">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Confirmation dialog for delete
                $('.delete-form').on('submit', function(e) {
                    e.preventDefault();

                    const form = this;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the category and all related subcategories and posts!",
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
@endsection
