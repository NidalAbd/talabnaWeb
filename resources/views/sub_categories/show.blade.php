@extends('adminlte::page')
@section('title', "Subcategory Details")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-tag text-primary mr-2"></i>
            Subcategory Details: <span class="text-muted">{{ $subcategory->id }}</span>
        </h1>
        <div>
            <a href="{{ route('indexSubCategory.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Subcategories
            </a>
            <a href="{{ route('subcategories.edit', $subcategory->id) }}" class="btn btn-warning">
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
                    <div class="card-body">
                        <div class="text-center mb-4">
                            @if($subcategory->photos->count() > 0)
                                <img src="{{ asset($subcategory->photos->first()->src) }}"
                                     alt="{{ $subcategory->name[app()->getLocale()] ?? 'Subcategory' }}"
                                     class="img-fluid img-thumbnail" style="max-height: 200px;">
                            @else
                                <div class="bg-light p-4 rounded mb-3">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                    <p class="mt-2 text-muted">{{('sub_categories\show.no_image_available') }}</p>
                                </div>
                            @endif
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-hashtag mr-2"></i> ID</span>
                                <span class="badge badge-primary badge-pill">{{ $subcategory->id }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="font-weight-bold mb-2 d-block"><i class="fas fa-language mr-2"></i> Name (Arabic)</span>
                                <span class="d-block text-right">{{ $subcategory->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="font-weight-bold mb-2 d-block"><i class="fas fa-language mr-2"></i> Name (English)</span>
                                <span class="d-block text-right">{{ $subcategory->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="font-weight-bold mb-2 d-block"><i class="fas fa-th-large mr-2"></i> Parent Category</span>
                                @if($subcategory->category)
                                    <span class="d-block text-right">
                                    <a href="{{ route('categories.show', $subcategory->category->id) }}" class="text-decoration-none">
                                        {{ $subcategory->category->name[app()->getLocale()] ?? 'N/A' }}
                                    </a>
                                </span>
                                @else
                                    <span class="d-block text-right text-danger">{{('sub_categories\show.parent_category_not_found') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-toggle-on mr-2"></i> Status</span>
                                @if($subcategory->isSuspended)
                                    <span class="badge badge-danger">{{('sub_categories\show.suspended') }}</span>
                                @else
                                    <span class="badge badge-success">{{('sub_categories\show.active') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-clipboard-list mr-2"></i> Service Posts</span>
                                <span class="badge badge-primary badge-pill">{{ $subcategory->servicePosts->count() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-calendar-alt mr-2"></i> Created</span>
                                <span>{{ $subcategory->created_at->diffForHumans() }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fas fa-edit mr-2"></i> Last Updated</span>
                                <span>{{ $subcategory->updated_at->diffForHumans() }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('subcategories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                            <div>
                                <a href="{{ route('subcategories.edit', $subcategory->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('subcategories.destroy', $subcategory->id) }}"
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
                            <i class="fas fa-clipboard-list mr-2 text-primary"></i>
                            Service Posts ({{ $subcategory->servicePosts->count() }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($subcategory->servicePosts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>{{('sub_categories\show.id') }}</th>
                                        <th>{{('sub_categories\show.image') }}</th>
                                        <th>{{('sub_categories\show.title') }}</th>
                                        <th>{{('sub_categories\show.user') }}</th>
                                        <th>{{('sub_categories\show.type') }}</th>
                                        <th>{{('sub_categories\show.status') }}</th>
                                        <th>{{('sub_categories\show.actions') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($subcategory->servicePosts->take(10) as $post)
                                        <tr>
                                            <td>{{ $post->id }}</td>
                                            <td class="text-center">
                                                @if($post->photos->count() > 0)
                                                    <img src="{{ asset($post->photos->first()->src) }}"
                                                         alt="{{ $post->title }}" class="img-thumbnail"
                                                         style="max-height: 40px;">
                                                @else
                                                    <span class="badge badge-secondary">{{('sub_categories\show.no_image') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('service_posts.show', $post->id) }}" class="text-decoration-none">
                                                    {{ \Illuminate\Support\Str::limit($post->title, 30) }}
                                                </a>
                                                <div>
                                                    <small class="text-muted">
                                                        {{ $post->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($post->user)
                                                    <a href="{{ route('users.show', $post->user->id) }}" class="text-decoration-none">
                                                        {{ $post->user->user_name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{('sub_categories\show.unknown') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($post->type == 'عرض')
                                                    <span class="badge badge-info">{{('sub_categories\show.offer') }}</span>
                                                @elseif($post->type == 'طلب')
                                                    <span class="badge badge-secondary">{{('sub_categories\show.request') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($post->state == 'published')
                                                    <span class="badge badge-success">{{('sub_categories\show.published') }}</span>
                                                @elseif($post->state == 'archive')
                                                    <span class="badge badge-warning">{{('sub_categories\show.archived') }}</span>
                                                @elseif($post->state == 'not published')
                                                    <span class="badge badge-secondary">{{('sub_categories\show.draft') }}</span>
                                                @elseif($post->state == 'rejected')
                                                    <span class="badge badge-danger">{{('sub_categories\show.rejected') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('service_posts.show', $post->id) }}"
                                                       class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('service_posts.edit', $post->id) }}"
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
                            @if($subcategory->servicePosts->count() > 10)
                                <div class="card-footer bg-white text-center">
                                    <a href="{{ route('servicePostCategorySubCategory', [$subcategory->category->id, $subcategory->id]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        View All {{ $subcategory->servicePosts->count() }} Posts
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                                    <h5 class="font-weight-normal text-muted">{{('sub_categories\show.no_service_posts_found_in_this_subcatego') }}</h5>
                                    <a href="{{ route('service_posts.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus mr-1"></i> Create New Service Post
                                    </a>
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







