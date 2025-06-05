@extends('adminlte::page')
@section('title', "Service Posts Management")
@section('content_header')
    @include('partials.breadcrumbs')
    @include('partials.alert')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-list text-primary mr-2"></i> Service Posts Management</h1>
        <div>
            <a href="{{ route('service_posts.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Service Post
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list text-primary mr-2"></i>
                            Service Posts
                        </h5>
                        <div class="d-flex">
                            <!-- Bulk Action Buttons -->
                            <div class="btn-group mr-2">
                                <button id="selectAllBtn" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-check-square mr-1"></i> Select All
                                </button>
                                <button id="deleteSelectedBtn" class="btn btn-sm btn-danger" disabled>
                                    <i class="fas fa-trash mr-1"></i> Delete Selected
                                </button>
                            </div>
                            <a href="{{ route('service_posts.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i> Add New Post
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

                        <div class="mb-3 mx-3">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <select id="category-filter" class="form-control form-control-sm">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ request('category') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name[app()->getLocale()] ?? $cat->name['en'] ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select id="subcategory-filter" class="form-control form-control-sm"
                                        {{ $subcategories->isEmpty() ? 'disabled' : '' }}>
                                        <option value="">All Subcategories</option>
                                        @foreach($subcategories as $subcat)
                                            <option value="{{ $subcat->id }}"
                                                {{ request('subcategory') == $subcat->id ? 'selected' : '' }}>
                                                {{ $subcat->name[app()->getLocale()] ?? $subcat->name['en'] ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select id="status-filter" class="form-control form-control-sm">
                                        <option value="">All Statuses</option>
                                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="archive" {{ request('status') == 'archive' ? 'selected' : '' }}>Archived</option>
                                        <option value="not published" {{ request('status') == 'not published' ? 'selected' : '' }}>Draft</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select id="type-filter" class="form-control form-control-sm">
                                        <option value="">All Types</option>
                                        <option value="عرض" {{ request('type') == 'عرض' ? 'selected' : '' }}>Offer</option>
                                        <option value="طلب" {{ request('type') == 'طلب' ? 'selected' : '' }}>Request</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="search-filter" class="form-control"
                                               placeholder="Search..."
                                               value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width: 40px;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="checkAll">
                                            <label class="custom-control-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Category / Subcategory</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Badge</th>
                                    <th>Stats</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($servicePosts as $post)
                                    <tr>
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input post-checkbox" id="post-{{ $post->id }}" value="{{ $post->id }}">
                                                <label class="custom-control-label" for="post-{{ $post->id }}"></label>
                                            </div>
                                        </td>
                                        <td>{{ $post->id }}</td>
                                        <td class="text-center">
                                            @if($post->photos->count() > 0)
                                                <img src="{{ asset($post->photos->first()->src) }}"
                                                     class="img-thumbnail" alt="{{ $post->title }}"
                                                     style="max-height: 50px;">
                                            @else
                                                <span class="badge badge-secondary">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold text-truncate" style="max-width: 200px;">
                                                {{ $post->title }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $post->created_at->format('M d, Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge badge-info mb-1">
                                                    {{ $post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? 'Unknown' }}
                                                </span>
                                                @if($post->subCategory)
                                                    <span class="badge badge-light">
                                                        {{ $post->subCategory->name[app()->getLocale()] ?? $post->subCategory->name['en'] ?? 'Unknown' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($post->user)
                                                <a href="{{ route('users.show', $post->user_id) }}" class="text-decoration-none">
                                                    {{ $post->user->user_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($post->type == 'عرض')
                                                <span class="badge badge-primary">Offer</span>
                                            @elseif($post->type == 'طلب')
                                                <span class="badge badge-secondary">Request</span>
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
                                        <td>
                                            @if($post->have_badge == 'ماسي')
                                                <span class="badge badge-primary">Diamond</span>
                                            @elseif($post->have_badge == 'ذهبي')
                                                <span class="badge badge-warning">Gold</span>
                                            @else
                                                <span class="badge badge-light">Standard</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">
                                                    <i class="fas fa-eye mr-1"></i> {{ $post->view_count }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-heart mr-1"></i> {{ $post->favorites_count ?? 0 }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @include('components.report-button', [
                                                 'reportableType' => 'service_post',
                                                 'reportableId' => $post->id,
                                                 'buttonText' => 'Report Post'
                                                   ])
                                                <a href="{{ route('service_posts.show', $post->id) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('service_posts.edit', $post->id) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('service_posts.destroy', $post->id) }}"
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
                                        <td colspan="11" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                                                <h5 class="font-weight-normal text-muted">No service posts found</h5>
                                                <a href="{{ route('service_posts.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus mr-1"></i> Create First Service Post
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
                            {{ $servicePosts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form for bulk delete -->
    <form id="bulkDeleteForm" action="{{ route('service_posts.bulk-destroy') }}" method="POST">
        @csrf
        @method('DELETE')
        <input type="hidden" name="post_ids" id="postIdsInput">
    </form>
@endsection

@section('css')
    <style>
        /* Additional CSS for the service posts index */
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }

        .img-thumbnail {
            object-fit: cover;
        }

        .text-truncate {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function() {
            console.log('Service Posts Index script loaded');

            // Select All logic
            $('#checkAll').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.post-checkbox').prop('checked', isChecked);
                updateDeleteButton();
            });

            // Individual checkbox change
            $('.post-checkbox').on('change', function() {
                updateDeleteButton();

                // Update "Select All" checkbox state
                const totalCheckboxes = $('.post-checkbox').length;
                const checkedCheckboxes = $('.post-checkbox:checked').length;
                $('#checkAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
            });

            // Select All button click
            $('#selectAllBtn').on('click', function() {
                $('#checkAll').prop('checked', true).trigger('change');
            });

            // Delete Selected button click
            $('#deleteSelectedBtn').on('click', function() {
                const selectedIds = getSelectedPostIds();
                if (selectedIds.length === 0) return;

                Swal.fire({
                    title: 'Delete Selected Posts?',
                    text: `You are about to delete ${selectedIds.length} service posts. This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#postIdsInput').val(JSON.stringify(selectedIds));
                        $('#bulkDeleteForm').submit();
                    }
                });
            });

            // Helper function to update Delete button state
            function updateDeleteButton() {
                const selectedCount = $('.post-checkbox:checked').length;
                $('#deleteSelectedBtn').prop('disabled', selectedCount === 0);

                // Update button text to show count
                if (selectedCount > 0) {
                    $('#deleteSelectedBtn').html(`<i class="fas fa-trash mr-1"></i> Delete Selected (${selectedCount})`);
                } else {
                    $('#deleteSelectedBtn').html(`<i class="fas fa-trash mr-1"></i> Delete Selected`);
                }
            }

            // Helper function to get all selected post IDs
            function getSelectedPostIds() {
                return $('.post-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            // Category change handler to load subcategories
            $('#category-filter').on('change', function() {
                const categoryId = $(this).val();
                const $subcategoryFilter = $('#subcategory-filter');

                // Reset subcategory filter
                $subcategoryFilter.html('<option value="">All Subcategories</option>');

                if (categoryId) {
                    // AJAX call to fetch subcategories
                    $.ajax({
                        url: "{{ route('fetchSubcategories') }}",
                        method: 'GET',
                        data: {
                            category_id: categoryId
                        },
                        success: function(subcategories) {
                            if (subcategories.length > 0) {
                                // Populate subcategories
                                subcategories.forEach(function(subcategory) {
                                    const name = subcategory.name['{{ app()->getLocale() }}'] || subcategory.name['en'] || 'Unknown';
                                    $subcategoryFilter.append(
                                        `<option value="${subcategory.id}">${name}</option>`
                                    );
                                });

                                // Enable subcategory dropdown
                                $subcategoryFilter.prop('disabled', false);
                            } else {
                                $subcategoryFilter.prop('disabled', true);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            $subcategoryFilter.prop('disabled', true);
                        }
                    });
                } else {
                    $subcategoryFilter.prop('disabled', true);
                }

                // Trigger filtering
                applyFilters();
            });

            // Filtering function
            function applyFilters() {
                const categoryFilter = $('#category-filter').val();
                const subcategoryFilter = $('#subcategory-filter').val();
                const statusFilter = $('#status-filter').val();
                const typeFilter = $('#type-filter').val();
                const searchFilter = $('#search-filter').val();

                // Construct query string
                const params = new URLSearchParams();

                if (categoryFilter) params.append('category', categoryFilter);
                if (subcategoryFilter) params.append('subcategory', subcategoryFilter);
                if (statusFilter) params.append('status', statusFilter);
                if (typeFilter) params.append('type', typeFilter);
                if (searchFilter) params.append('search', searchFilter);

                // Navigate to filtered URL
                const baseUrl = "{{ route('service_posts.index') }}";
                const fullUrl = params.toString()
                    ? `${baseUrl}?${params.toString()}`
                    : baseUrl;

                window.location.href = fullUrl;
            }

            // Event listeners for filters
            $('#category-filter, #subcategory-filter, #status-filter, #type-filter').on('change', function() {
                applyFilters();
            });

            // Debounced search
            let searchTimeout;
            $('#search-filter').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 500);
            });

            // Delete confirmation
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete this service post!",
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
@stop
