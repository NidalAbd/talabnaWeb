@extends('adminlte::page')

@section('title', 'Service Posts Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fas fa-briefcase text-primary mr-2"></i> Service Posts Management</h1>
            <p class="text-muted mb-0">Manage and monitor all service posts across the platform</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('service_posts.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Create Post
            </a>
            <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#bulkActionsModal">
                <i class="fas fa-tasks mr-1"></i> Bulk Actions
            </button>
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#exportModal">
                <i class="fas fa-download mr-1"></i> Export
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Posts</span>
                    <span class="info-box-number">{{ number_format($servicePosts->total()) }}</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All service posts
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Published</span>
                    <span class="info-box-number">{{ number_format($publishedCount ?? 0) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $servicePosts->total() > 0 ? (($publishedCount ?? 0) / $servicePosts->total()) * 100 : 0 }}%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-check text-light"></i> {{ $servicePosts->total() > 0 ? number_format((($publishedCount ?? 0) / $servicePosts->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ number_format($pendingCount ?? 0) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $servicePosts->total() > 0 ? (($pendingCount ?? 0) / $servicePosts->total()) * 100 : 0 }}%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-clock text-light"></i> {{ $servicePosts->total() > 0 ? number_format((($pendingCount ?? 0) / $servicePosts->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-danger shadow-sm">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Premium</span>
                    <span class="info-box-number">{{ number_format($premiumCount ?? 0) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $servicePosts->total() > 0 ? (($premiumCount ?? 0) / $servicePosts->total()) * 100 : 0 }}%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-star text-light"></i> {{ $servicePosts->total() > 0 ? number_format((($premiumCount ?? 0) / $servicePosts->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter mr-2"></i> Advanced Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="category-filter" class="form-label">Category</label>
                        <select name="category" id="category-filter" class="form-control select2">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name[app()->getLocale()] ?? $cat->name['en'] ?? 'Unknown' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subcategory-filter" class="form-label">Subcategory</label>
                        <select name="subcategory" id="subcategory-filter" class="form-control select2" {{ $subcategories->isEmpty() ? 'disabled' : '' }}>
                            <option value="">All Subcategories</option>
                            @foreach($subcategories as $subcat)
                                <option value="{{ $subcat->id }}" {{ request('subcategory') == $subcat->id ? 'selected' : '' }}>
                                    {{ $subcat->name[app()->getLocale()] ?? $subcat->name['en'] ?? 'Unknown' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status-filter" class="form-label">Status</label>
                        <select name="status" id="status-filter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="not published" {{ request('status') == 'not published' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="archive" {{ request('status') == 'archive' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="premium-filter" class="form-label">Type</label>
                        <select name="premium" id="premium-filter" class="form-control">
                            <option value="">All Types</option>
                            <option value="0" {{ request('premium') == '0' ? 'selected' : '' }}>Regular</option>
                            <option value="1" {{ request('premium') == '1' ? 'selected' : '' }}>Premium</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="level-filter" class="form-label">Level</label>
                        <select name="level" id="level-filter" class="form-control">
                            <option value="">All Levels</option>
                            @foreach($levels ?? [] as $level)
                                <option value="{{ $level->id }}" {{ request('level') == $level->id ? 'selected' : '' }}>
                                    {{ $level->localized_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date-range" class="form-label">Date Range</label>
                        <input type="text" name="date_range" id="date-range" class="form-control" placeholder="Select date range" value="{{ request('date_range') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="search-filter" class="form-label">Search</label>
                        <input type="text" name="search" id="search-filter" class="form-control" placeholder="Search posts..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('service_posts.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt mr-1"></i> Reset
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm" data-toggle="collapse" data-target="#advancedFilters">
                                <i class="fas fa-cog mr-1"></i> Advanced
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="saveFilterBtn">
                                <i class="fas fa-save mr-1"></i> Save Filter
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters Collapse -->
                <div class="collapse mt-3" id="advancedFilters">
                    <div class="card card-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="user-filter" class="form-label">User</label>
                                <select name="user" id="user-filter" class="form-control select2">
                                    <option value="">All Users</option>
                                    @foreach($users ?? [] as $user)
                                        <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name ?? $user->user_name ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="city-filter" class="form-label">City</label>
                                <select name="city" id="city-filter" class="form-control select2">
                                    <option value="">All Cities</option>
                                    @foreach($cities ?? [] as $city)
                                        <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>
                                            {{ $city->name[app()->getLocale()] ?? $city->name['en'] ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="country-filter" class="form-label">Country</label>
                                <select name="country" id="country-filter" class="form-control select2">
                                    <option value="">All Countries</option>
                                    @foreach($countries ?? [] as $country)
                                        <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name[app()->getLocale()] ?? $country->name['en'] ?? 'Unknown' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="views-filter" class="form-label">Min Views</label>
                                <input type="number" name="min_views" id="views-filter" class="form-control" placeholder="0" value="{{ request('min_views') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Service Posts Table -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">
                <i class="fas fa-list mr-2"></i> Service Posts List
                <span class="badge badge-primary ml-2">{{ $servicePosts->total() }}</span>
            </div>
            <div class="card-tools">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleViewMode()">
                        <i class="fas fa-th-large" id="viewModeIcon"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="servicePostsTable">
                    <thead class="thead-dark sticky-top" style="z-index: 10;">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="60">ID</th>
                            <th width="300">Post Details</th>
                            <th width="150">User</th>
                            <th width="120">Category</th>
                            <th width="120">Subcategory</th>
                            <th width="100">Status</th>
                            <th width="80">Type</th>
                            <th width="100">Level</th>
                            <th width="80">Views</th>
                            <th width="250">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($servicePosts as $post)
                        <tr class="align-middle" id="service-post-row-{{ $post->id }}" data-post-id="{{ $post->id }}">
                            <td>
                                <input type="checkbox" class="form-check-input post-checkbox" value="{{ $post->id }}">
                            </td>
                            <td>
                                <span class="badge badge-secondary">#{{ $post->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    @if($post->photos && $post->photos->count() > 0)
                                        <img src="{{ $post->photos->first()->src }}" alt="Post Image" 
                                             class="img-thumbnail mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded mr-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold" data-toggle="tooltip" title="{{ is_array($post->title) ? ($post->title[app()->getLocale()] ?? $post->title['ar'] ?? $post->title['en'] ?? 'No Title') : $post->title }}">
                                            {{ Str::limit(is_array($post->title) ? ($post->title[app()->getLocale()] ?? $post->title['ar'] ?? $post->title['en'] ?? 'No Title') : $post->title, 40) }}
                                        </h6>
                                        <p class="mb-1 text-muted small" data-toggle="tooltip" title="{{ is_array($post->description) ? ($post->description[app()->getLocale()] ?? $post->description['ar'] ?? $post->description['en'] ?? '') : $post->description }}">
                                            {{ Str::limit(is_array($post->description) ? ($post->description[app()->getLocale()] ?? $post->description['ar'] ?? $post->description['en'] ?? '') : $post->description, 60) }}
                                        </p>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-info">{{ $post->type ?? 'N/A' }}</span>
                                            @if($post->price)
                                                <span class="badge badge-success">{{ $post->price }} {{ $post->price_currency_code ?? 'USD' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($post->user)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-2" style="width:32px;height:32px;font-size:0.8rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $post->user->name ?? $post->user->user_name ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ $post->user->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $post->category ? ($post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? 'Unknown') : 'Unknown' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $post->subCategory ? 'primary' : 'secondary' }}">
                                    {{ $post->subCategory ? $post->subCategory->display_name : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $post->state == 'published' ? 'success' : ($post->state == 'archive' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($post->state) }}
                                </span>
                            </td>
                            <td>
                                <span class="premium-badge">
                                    @if($post->is_premium)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-star"></i> Premium
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Regular</span>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($post->level)
                                    <span class="badge" style="background-color: {{ $post->level->color }}; color: white;">
                                        <i class="fas {{ $post->level->icon }}"></i> {{ $post->level->localized_name }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Default</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light text-dark">
                                    <i class="fas fa-eye mr-1"></i>{{ $post->view_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('service_posts.show', $post->id) }}" class="btn btn-outline-info" data-toggle="tooltip" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('service_posts.edit', $post->id) }}" class="btn btn-outline-primary" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success" data-toggle="tooltip" title="Upgrade Level" onclick="showLevelUpgrade({{ $post->id }})">
                                        <i class="fas fa-level-up-alt"></i>
                                    </button>
                                    @if($post->state == 'not published')
                                        <button type="button" class="btn btn-outline-warning" data-toggle="tooltip" title="Approve" onclick="approveServicePost({{ $post->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    @if($post->state == 'published')
                                        <button type="button" class="btn btn-outline-warning" data-toggle="tooltip" title="Reject" onclick="rejectServicePost({{ $post->id }})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger" data-toggle="tooltip" title="Delete" onclick="deleteServicePost({{ $post->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No service posts found matching your criteria.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Showing <b>{{ $servicePosts->firstItem() ?? 0 }}</b> to <b>{{ $servicePosts->lastItem() ?? 0 }}</b> of <b>{{ $servicePosts->total() }}</b> results</span>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 small">Per page:</label>
                    <select class="form-control form-control-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <div>
                {{ $servicePosts->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Selected <span id="selectedCount">0</span> posts</p>
                <div class="form-group">
                    <label>Action:</label>
                    <select class="form-control" id="bulkAction">
                        <option value="">Choose action...</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                        <option value="archive">Archive Selected</option>
                        <option value="make_premium">Make Premium</option>
                        <option value="remove_premium">Remove Premium</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Service Posts</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Format:</label>
                    <select class="form-control" id="exportFormat">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Include:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includePhotos" checked>
                        <label class="form-check-label" for="includePhotos">Photos</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeUserInfo" checked>
                        <label class="form-check-label" for="includeUserInfo">User Information</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeStats" checked>
                        <label class="form-check-label" for="includeStats">Statistics</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="exportData()">Export</button>
            </div>
        </div>
    </div>
</div>

<!-- Level Upgrade Modal -->
<div class="modal fade" id="levelUpgradeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upgrade Service Post Level</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Select Level:</label>
                            <select class="form-control" id="levelSelect">
                                <option value="">Choose a level...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duration (Days):</label>
                            <input type="number" class="form-control" id="levelDuration" min="1" max="365" value="30">
                        </div>
                        <div class="alert alert-info" id="levelInfo" style="display: none;">
                            <div id="levelDescription"></div>
                            <div id="levelFeatures"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Your Points</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span>Available:</span>
                                    <span id="userPoints" class="font-weight-bold">0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Required:</span>
                                    <span id="requiredPoints" class="font-weight-bold text-danger">0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Remaining:</span>
                                    <span id="remainingPoints" class="font-weight-bold text-success">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showPointPackages()">
                                <i class="fas fa-shopping-cart mr-1"></i> Buy More Points
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="upgradeLevelBtn" onclick="upgradeServicePostLevel()" disabled>
                    Upgrade Level
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Point Packages Modal -->
<div class="modal fade" id="pointPackagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buy Point Packages</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row" id="packagesContainer">
                    <!-- Packages will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(function () {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Initialize Date Range Picker
        $('#date-range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Dynamic subcategory loading
        $('#category-filter').on('change', function() {
            const categoryId = $(this).val();
            const $subcategoryFilter = $('#subcategory-filter');
            const $subcategoryContainer = $subcategoryFilter.closest('.col-md-2');
            
            // Show loading state
            $subcategoryFilter.prop('disabled', true);
            $subcategoryFilter.html('<option value="">Loading...</option>');
            
            if (categoryId) {
                $.ajax({
                    url: "{{ route('fetchSubcategories') }}",
                    method: 'GET',
                    data: { category_id: categoryId },
                    success: function(subcategories) {
                        $subcategoryFilter.html('<option value="">All Subcategories</option>');
                        
                        if (subcategories.length > 0) {
                            subcategories.forEach(function(subcat) {
                                const name = subcat.name['{{ app()->getLocale() }}'] || subcat.name['en'] || 'Unknown';
                                $subcategoryFilter.append(`<option value="${subcat.id}">${name}</option>`);
                            });
                            $subcategoryFilter.prop('disabled', false);
                            $subcategoryContainer.show();
                        } else {
                            $subcategoryFilter.html('<option value="">No subcategories found</option>');
                            $subcategoryFilter.prop('disabled', true);
                        }
                        
                        $subcategoryFilter.select2('destroy').select2({
                            theme: 'bootstrap4',
                            width: '100%'
                        });
                    },
                    error: function() {
                        $subcategoryFilter.html('<option value="">Error loading subcategories</option>');
                        $subcategoryFilter.prop('disabled', true);
                        $subcategoryFilter.select2('destroy').select2({
                            theme: 'bootstrap4',
                            width: '100%'
                        });
                    }
                });
            } else {
                $subcategoryFilter.html('<option value="">All Subcategories</option>');
                $subcategoryFilter.prop('disabled', true);
                $subcategoryFilter.select2('destroy').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });

        // Auto-submit form on filter changes
        $('#category-filter, #subcategory-filter, #status-filter, #premium-filter, #user-filter, #city-filter, #country-filter').on('change', function() {
            setTimeout(function() {
                $('#filterForm').submit();
            }, 100);
        });

        // Debounced search
        let searchTimeout;
        $('#search-filter').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                $('#filterForm').submit();
            }, 500);
        });

        // Views filter
        $('#views-filter').on('change', function() {
            setTimeout(function() {
                $('#filterForm').submit();
            }, 100);
        });

        // Select All functionality
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.post-checkbox').prop('checked', isChecked);
            updateSelectedCount();
        });

        // Individual checkbox change
        $('.post-checkbox').on('change', function() {
            updateSelectedCount();
            updateSelectAllState();
        });

        // Save filter functionality
        $('#saveFilterBtn').on('click', function() {
            const filterName = prompt('Enter a name for this filter:');
            if (filterName) {
                const filterData = $('#filterForm').serialize();
                localStorage.setItem('savedFilter_' + filterName, filterData);
                Swal.fire({
                    title: 'Success!',
                    text: 'Filter saved successfully!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        // Load saved filters
        loadSavedFilters();
    });

    // Update selected count
    function updateSelectedCount() {
        const selectedCount = $('.post-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        
        // Enable/disable bulk action button
        if (selectedCount > 0) {
            $('#bulkActionsModal .btn-primary').prop('disabled', false);
        } else {
            $('#bulkActionsModal .btn-primary').prop('disabled', true);
        }
    }

    // Update select all state
    function updateSelectAllState() {
        const totalCheckboxes = $('.post-checkbox').length;
        const checkedCheckboxes = $('.post-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    }

    // Toggle view mode
    function toggleViewMode() {
        const table = $('#servicePostsTable');
        const icon = $('#viewModeIcon');
        
        if (table.hasClass('compact-view')) {
            table.removeClass('compact-view');
            icon.removeClass('fa-list').addClass('fa-th-large');
        } else {
            table.addClass('compact-view');
            icon.removeClass('fa-th-large').addClass('fa-list');
        }
    }

    // Refresh table
    function refreshTable() {
        location.reload();
    }

    // Change per page
    function changePerPage(value) {
        const url = new URL(window.location);
        url.searchParams.set('per_page', value);
        window.location = url;
    }

    // Execute bulk action
    function executeBulkAction() {
        const action = $('#bulkAction').val();
        const selectedIds = $('.post-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!action) {
            Swal.fire('Error', 'Please select an action', 'error');
            return;
        }

        if (selectedIds.length === 0) {
            Swal.fire('Error', 'Please select at least one post', 'error');
            return;
        }

        const actionText = $('#bulkAction option:selected').text();
        
        Swal.fire({
            title: 'Confirm Action',
            text: `Are you sure you want to ${actionText.toLowerCase()} ${selectedIds.length} selected posts?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('service_posts.bulk-action') }}",
                    method: 'POST',
                    data: {
                        action: action,
                        post_ids: selectedIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while processing the request', 'error');
                    }
                });
            }
        });
    }

    // Export data
    function exportData() {
        const format = $('#exportFormat').val();
        const includePhotos = $('#includePhotos').is(':checked');
        const includeUserInfo = $('#includeUserInfo').is(':checked');
        const includeStats = $('#includeStats').is(':checked');

        const params = new URLSearchParams(window.location.search);
        params.append('format', format);
        params.append('include_photos', includePhotos);
        params.append('include_user_info', includeUserInfo);
        params.append('include_stats', includeStats);

        window.open(`{{ route('service_posts.export') }}?${params.toString()}`, '_blank');
        $('#exportModal').modal('hide');
    }

    // Duplicate post
    function duplicatePost(postId) {
        Swal.fire({
            title: 'Duplicate Post',
            text: 'Are you sure you want to duplicate this post?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, duplicate!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}/duplicate`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Post duplicated successfully!',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while duplicating the post', 'error');
                    }
                });
            }
        });
    }

    // Archive post
    function archivePost(postId) {
        Swal.fire({
            title: 'Archive Post',
            text: 'Are you sure you want to archive this post?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, archive!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}/archive`,
                    method: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Post archived successfully!',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while archiving the post', 'error');
                    }
                });
            }
        });
    }

    // Feature post
    function featurePost(postId) {
        Swal.fire({
            title: 'Feature Post',
            text: 'Are you sure you want to feature this post?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, feature!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}/feature`,
                    method: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Post featured successfully!',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An error occurred while featuring the post', 'error');
                    }
                });
            }
        });
    }

    // Load saved filters
    function loadSavedFilters() {
        const savedFilters = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('savedFilter_')) {
                const filterName = key.replace('savedFilter_', '');
                savedFilters.push(filterName);
            }
        }

        if (savedFilters.length > 0) {
            const filterSelect = $('<select class="form-control form-control-sm ml-2" style="width: auto;">');
            filterSelect.append('<option value="">Load saved filter...</option>');
            
            savedFilters.forEach(filter => {
                filterSelect.append(`<option value="${filter}">${filter}</option>`);
            });

            filterSelect.on('change', function() {
                const filterName = $(this).val();
                if (filterName) {
                    const filterData = localStorage.getItem('savedFilter_' + filterName);
                    if (filterData) {
                        const params = new URLSearchParams(filterData);
                        window.location.search = params.toString();
                    }
                }
            });

            $('#saveFilterBtn').after(filterSelect);
        }
    }

    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 65) {
            e.preventDefault();
            $('#selectAll').click();
        }
        
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
            e.preventDefault();
            $('#search-filter').focus();
        }
        
        // Escape to clear selection
        if (e.keyCode === 27) {
            $('.post-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false).prop('indeterminate', false);
            updateSelectedCount();
        }
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        // Only refresh if no modal is open and user is active
        if (!$('.modal').hasClass('show') && !$('.dropdown-menu').is(':visible')) {
            // Refresh statistics only
            $.get('{{ route("service_posts.statistics") }}', function(data) {
                // Update statistics cards
                $('.info-box-number').each(function(index) {
                    const values = [data.total, data.published, data.pending, data.premium];
                    if (values[index] !== undefined) {
                        $(this).text(values[index].toLocaleString());
                    }
                });
            });
        }
    }, 300000); // 5 minutes

    // Level Management Variables
    let currentServicePostId = null;
    let availableLevels = [];
    let userPoints = 0;

    // Show level upgrade modal
    function showLevelUpgrade(postId) {
        currentServicePostId = postId;
        $('#levelUpgradeModal').modal('show');
        loadAvailableLevels(postId);
    }

    // Load available levels for service post
    function loadAvailableLevels(postId) {
        $.ajax({
            url: `/service_posts/${postId}/available-levels`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    availableLevels = response.data.levels;
                    userPoints = response.data.user_points;
                    
                    // Populate level select
                    const $levelSelect = $('#levelSelect');
                    $levelSelect.html('<option value="">Choose a level...</option>');
                    
                    availableLevels.forEach(function(level) {
                        const disabled = !level.can_afford ? 'disabled' : '';
                        const option = `<option value="${level.id}" ${disabled} data-level='${JSON.stringify(level)}'>${level.name}</option>`;
                        $levelSelect.append(option);
                    });
                    
                    // Update user points display
                    $('#userPoints').text(userPoints.toLocaleString());
                    
                    // Show current level info if exists
                    if (response.data.current_level) {
                        const current = response.data.current_level;
                        $('#levelInfo').show().html(`
                            <strong>Current Level:</strong> ${current.name}<br>
                            <strong>Expires:</strong> ${current.expires_at}<br>
                            <strong>Remaining Days:</strong> ${current.remaining_days || 0}
                        `);
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load available levels', 'error');
            }
        });
    }

    // Handle level selection change
    $('#levelSelect').on('change', function() {
        const selectedLevelId = $(this).val();
        const selectedLevel = availableLevels.find(l => l.id == selectedLevelId);
        
        if (selectedLevel) {
            updateLevelInfo(selectedLevel);
            updatePointsCalculation(selectedLevel);
        } else {
            $('#levelInfo').hide();
            $('#upgradeLevelBtn').prop('disabled', true);
        }
    });

    // Handle duration change
    $('#levelDuration').on('input', function() {
        const selectedLevelId = $('#levelSelect').val();
        const selectedLevel = availableLevels.find(l => l.id == selectedLevelId);
        
        if (selectedLevel) {
            updatePointsCalculation(selectedLevel);
        }
    });

    // Update level information display
    function updateLevelInfo(level) {
        const features = level.features.map(f => `<li>${f}</li>`).join('');
        $('#levelInfo').show().html(`
            <div id="levelDescription">
                <strong>${level.name}</strong><br>
                ${level.description}
            </div>
            <div id="levelFeatures" class="mt-2">
                <strong>Features:</strong>
                <ul class="mb-0">${features}</ul>
            </div>
        `);
    }

    // Update points calculation
    function updatePointsCalculation(level) {
        const duration = parseInt($('#levelDuration').val()) || 0;
        const requiredPoints = level.points_per_day * duration;
        const remainingPoints = userPoints - requiredPoints;
        
        $('#requiredPoints').text(requiredPoints.toLocaleString());
        $('#remainingPoints').text(remainingPoints.toLocaleString());
        
        // Enable/disable upgrade button
        const canUpgrade = requiredPoints > 0 && remainingPoints >= 0;
        $('#upgradeLevelBtn').prop('disabled', !canUpgrade);
        
        // Update colors
        $('#requiredPoints').removeClass('text-danger text-success').addClass(requiredPoints > 0 ? 'text-danger' : 'text-success');
        $('#remainingPoints').removeClass('text-danger text-success').addClass(remainingPoints >= 0 ? 'text-success' : 'text-danger');
    }

    // Upgrade service post level
    function upgradeServicePostLevel() {
        const levelId = $('#levelSelect').val();
        const duration = $('#levelDuration').val();
        
        if (!levelId || !duration) {
            Swal.fire('Error', 'Please select a level and duration', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Confirm Upgrade',
            text: `Are you sure you want to upgrade this service post for ${duration} days?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, upgrade!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${currentServicePostId}/update-level`,
                    method: 'PATCH',
                    data: {
                        level_id: levelId,
                        duration: duration,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#levelUpgradeModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    }

    // Show point packages modal
    function showPointPackages() {
        $('#levelUpgradeModal').modal('hide');
        $('#pointPackagesModal').modal('show');
        loadPointPackages();
    }

    // Load point packages
    function loadPointPackages() {
        $.ajax({
            url: '{{ route("service_posts.point-packages") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const packages = response.data.packages;
                    const $container = $('#packagesContainer');
                    $container.empty();
                    
                    packages.forEach(function(package) {
                        const features = package.features ? package.features.map(f => `<li>${f}</li>`).join('') : '';
                        const packageHtml = `
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">${package.name}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <h4 class="text-primary">${package.formatted_price}</h4>
                                            <p class="text-muted mb-0">${package.formatted_points}</p>
                                        </div>
                                        <p class="small">${package.description}</p>
                                        ${features ? `<ul class="small mb-0">${features}</ul>` : ''}
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="purchasePackage(${package.id})">
                                            Purchase
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        $container.append(packageHtml);
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load point packages', 'error');
            }
        });
    }

    // Purchase point package
    function purchasePackage(packageId) {
        Swal.fire({
            title: 'Purchase Package',
            text: 'This will redirect you to the payment gateway. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, continue!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payment gateway or handle purchase
                window.open(`/point-packages/${packageId}/purchase`, '_blank');
            }
        });
    }

    // Service Post Actions
    function approveServicePost(postId) {
        Swal.fire({
            title: 'Approve Service Post',
            text: 'Are you sure you want to approve this service post?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}/approve`,
                    method: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    }

    function rejectServicePost(postId) {
        Swal.fire({
            title: 'Reject Service Post',
            text: 'Are you sure you want to reject this service post?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}/reject`,
                    method: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    }

    function deleteServicePost(postId) {
        Swal.fire({
            title: 'Delete Service Post',
            text: 'Are you sure you want to delete this service post? This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/service_posts/${postId}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush

@push('css')
<style>
    /* Enhanced table styles */
    .table thead th { 
        background: #f8f9fa; 
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .table td, .table th { 
        vertical-align: middle !important; 
        padding: 0.75rem;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
        transform: scale(1.001);
        transition: all 0.2s ease;
    }
    
    /* Card enhancements */
    .card { 
        transition: box-shadow 0.3s ease, transform 0.2s ease; 
        border: none;
        border-radius: 0.5rem;
    }
    
    .card:hover { 
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    /* Button enhancements */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        border-radius: 0.2rem;
    }
    
    .btn-group-sm .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    /* Badge enhancements */
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
        border-radius: 0.375rem;
    }
    
    /* Avatar styles */
    .avatar {
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Compact view */
    .compact-view td {
        padding: 0.5rem;
        font-size: 0.9rem;
    }
    
    .compact-view .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
    
    /* Loading states */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    /* Animation for status changes */
    .status-badge, .premium-badge {
        transition: all 0.3s ease;
    }
    
    .status-badge.updated, .premium-badge.updated {
        animation: pulse 0.6s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 0.25em 0.5em;
        }
    }
    
    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Info box enhancements */
    .info-box {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .info-box-icon {
        border-radius: 0;
    }
    
    .info-box-number {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    /* Filter form enhancements */
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .select2-container--bootstrap4 .select2-selection--single {
        border-radius: 0.375rem;
    }
    
    /* Modal enhancements */
    .modal-content {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    /* Action button improvements - matching users index */
    .btn-group .btn {
        margin-right: 1px;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.2;
        border-radius: 0.2rem;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    /* Ensure action buttons are always visible */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    /* Responsive improvements for action buttons */
    @media (max-width: 768px) {
        .btn-group .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
    }
    
    /* Pagination enhancements */
    .pagination .page-link {
        border-radius: 0.25rem;
        margin: 0 0.125rem;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
@endpush
