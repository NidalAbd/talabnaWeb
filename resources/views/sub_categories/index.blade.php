@extends('adminlte::page')
@section('title', 'Subcategories Management')
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tag text-primary mr-2"></i> Subcategories Management</h1>
        <div>
            <a href="{{ route('subcategories.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Subcategory
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
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
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">Image</th>
                                    <th style="width: 20%">Subcategory Name</th>
                                    <th style="width: 20%">Parent Category</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 15%">Posts</th>
                                    <th style="width: 15%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($subCategories as $subCategory)
                                    <tr>
                                        <td>{{ $subCategory->id }}</td>
                                        <td class="text-center">
                                            @if($subCategory->photos->count() > 0)
                                                <img src="{{ asset($subCategory->photos->first()->src) }}"
                                                     class="img-thumbnail" alt="{{ $subCategory->name[app()->getLocale()] ?? 'Subcategory' }}"
                                                     style="max-height: 50px;">
                                            @else
                                                <span class="badge badge-secondary">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $subCategory->name[app()->getLocale()] ?? 'N/A' }}</div>
                                            <small class="text-muted">
                                                @if(app()->getLocale() != 'en' && isset($subCategory->name['en']))
                                                    ({{ $subCategory->name['en'] }})
                                                @elseif(app()->getLocale() != 'ar' && isset($subCategory->name['ar']))
                                                    ({{ $subCategory->name['ar'] }})
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if($subCategory->category)
                                                <div class="font-weight-bold">{{ $subCategory->category->name[app()->getLocale()] ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-danger">Category Not Found</span>
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
                                            <span class="badge badge-primary badge-pill">
                                                {{ $subCategory->service_posts_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('subcategories.show', $subCategory->id) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('subcategories.edit', $subCategory->id) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('subcategories.toggle-suspend', $subCategory->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm {{ $subCategory->isSuspended ? 'btn-success' : 'btn-secondary' }}"
                                                            title="{{ $subCategory->isSuspended ? 'Activate' : 'Suspend' }}">
                                                        <i class="fas {{ $subCategory->isSuspended ? 'fa-check' : 'fa-ban' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('subcategories.destroy', $subCategory->id) }}"
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                                <h5 class="font-weight-normal text-muted">No subcategories found</h5>
                                                <a href="{{ route('subcategories.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus mr-1"></i> Create First Subcategory
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-center">
                            {{ $subCategories->links() }}
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
                        text: "This will delete the subcategory and all related service posts!",
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
