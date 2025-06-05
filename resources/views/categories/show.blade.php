@extends('adminlte::page')
@section('title', "Category Details")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags text-primary mr-2"></i> Category Details</h1>
        <div>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Categories
            </a>
            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-th-large text-primary mr-2"></i>
                            Category Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            @if($category->photos->count() > 0)
                                <img src="{{ asset($category->photos->first()->src) }}"
                                     alt="{{ $category->name[app()->getLocale()] }}"
                                     class="img-fluid img-thumbnail" style="max-height: 200px;">
                            @else
                                <div class="bg-light p-4 rounded mb-3">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                    <p class="mt-2 text-muted">No image available</p>
                                </div>
                            @endif
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-hashtag mr-2"></i> ID</span>
                                <span class="badge badge-primary badge-pill">{{ $category->id }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="font-weight-bold mb-2 d-block"><i class="fas fa-language mr-2"></i> Name (Arabic)</span>
                                <span class="d-block text-right">{{ $category->name['ar'] ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="font-weight-bold mb-2 d-block"><i class="fas fa-language mr-2"></i> Name (English)</span>
                                <span class="d-block text-right">{{ $category->name['en'] ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-toggle-on mr-2"></i> Status</span>
                                @if($category->isSuspended)
                                    <span class="badge badge-danger">Suspended</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-list mr-2"></i> Subcategories</span>
                                <span class="badge badge-info badge-pill">{{ $category->sub_categories->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-clipboard-list mr-2"></i> Service Posts</span>
                                <span class="badge badge-primary badge-pill">{{ $category->servicePosts->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-calendar-alt mr-2"></i> Created</span>
                                <span>{{ $category->created_at->format('M d, Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-edit mr-2"></i> Last Updated</span>
                                <span>{{ $category->updated_at->format('M d, Y') }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <div>
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('categories.destroy', $category->id) }}"
                                      method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-th mr-2 text-info"></i>
                            Subcategories ({{ $category->sub_categories->count() }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($category->sub_categories->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Posts</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($category->sub_categories as $subcategory)
                                        <tr>
                                            <td>{{ $subcategory->id }}</td>
                                            <td class="text-center">
                                                @if($subcategory->photos->count() > 0)
                                                    <img src="{{ asset($subcategory->photos->first()->src) }}"
                                                         class="img-thumbnail" alt="{{ $subcategory->name[app()->getLocale()] }}"
                                                         style="max-height: 40px;">
                                                @else
                                                    <span class="badge badge-secondary">No Image</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $subcategory->name[app()->getLocale()] ?? 'N/A' }}
                                            </td>
                                            <td>
                                                @if($subcategory->isSuspended)
                                                    <span class="badge badge-danger">Suspended</span>
                                                @else
                                                    <span class="badge badge-success">Active</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-primary badge-pill">
                                                    {{ $subcategory->servicePosts->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('subcategories.show', $subcategory->id) }}"
                                                       class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('subcategories.edit', $subcategory->id) }}"
                                                       class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <h5 class="font-weight-normal text-muted">No subcategories found</h5>
                                    <a href="{{ route('subcategories.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus mr-1"></i> Add Subcategory
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list mr-2 text-primary"></i>
                            Recent Service Posts ({{ $category->servicePosts->count() }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($category->servicePosts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Subcategory</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Posted</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($category->servicePosts()->orderBy('created_at', 'desc')->take(5)->get() as $post)
                                        <tr>
                                            <td>{{ $post->id }}</td>
                                            <td>
                                                <a href="{{ route('service_posts.show', $post->id) }}" class="text-decoration-none">
                                                    {{ \Illuminate\Support\Str::limit($post->title, 30) }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($post->subCategory)
                                                    {{ $post->subCategory->name[app()->getLocale()] ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($post->user)
                                                    {{ $post->user->user_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($post->state == 'published')
                                                    <span class="badge badge-success">Published</span>
                                                @elseif($post->state == 'archive')
                                                    <span class="badge badge-warning">Archived</span>
                                                @elseif($post->state == 'not published')
                                                    <span class="badge badge-secondary">Draft</span>
                                                @elseif($post->state == 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td>{{ $post->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                                    <h5 class="font-weight-normal text-muted">No service posts found in this category</h5>
                                </div>
                            </div>
                        @endif
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
