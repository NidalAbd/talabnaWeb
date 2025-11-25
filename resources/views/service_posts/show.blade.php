@extends('adminlte::page')
@section('title', "Service Post Details")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-eye text-primary mr-2"></i> View Service Post #{{ $servicePost->id }}</h1>
        <div>
            <a href="{{ route('service_posts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
            <a href="{{ route('service_posts.edit', $servicePost->id) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Validation Error!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list text-primary mr-2"></i>
                            Service Post Details
                        </h5>
                        <div>
                            @if($servicePost->have_badge == 'ماسي')
                                <span class="badge badge-primary py-1 px-2">Diamond</span>
                            @elseif($servicePost->have_badge == 'ذهبي')
                                <span class="badge badge-warning py-1 px-2">Gold</span>
                            @endif

                            @if($servicePost->type == 'عرض')
                                <span class="badge badge-info py-1 px-2">Offer</span>
                            @elseif($servicePost->type == 'طلب')
                                <span class="badge badge-secondary py-1 px-2">Request</span>
                            @endif

                            @if($servicePost->state == 'published')
                                <span class="badge badge-success py-1 px-2">Published</span>
                            @elseif($servicePost->state == 'archive')
                                <span class="badge badge-warning py-1 px-2">Archived</span>
                            @elseif($servicePost->state == 'not published')
                                <span class="badge badge-secondary py-1 px-2">Draft</span>
                            @elseif($servicePost->state == 'rejected')
                                <span class="badge badge-danger py-1 px-2">Rejected</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <h4 class="font-weight-bold mb-3">{{ $servicePost->title }}</h4>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-tag text-muted mr-2"></i>
                                <strong class="mr-2">Category:</strong>
                                <span>

        @if($servicePost->subCategory)
                                        <i class="fas fa-chevron-right mx-2 small"></i>
                                        <a href="{{ route('servicePostCategorySubCategory', [$servicePost->categories_id, $servicePost->sub_categories_id]) }}" class="text-decoration-none">
                {{ getTranslatedName($servicePost->subCategory->name) }}
            </a>
                                    @endif
    </span>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user text-muted mr-2"></i>
                                <strong class="mr-2">Posted by:</strong>
                                <span>
                                @if($servicePost->user)
                                        <a href="{{ route('users.show', $servicePost->user_id) }}" class="text-decoration-none">
                                        {{ $servicePost->user->user_name }}
                                    </a>
                                    @else
                                        Unknown User
                                    @endif
                            </span>
                            </div>

                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-calendar-alt text-muted mr-2"></i>
                                <strong class="mr-2">Posted:</strong>
                                <span>{{ $servicePost->created_at->format('M d, Y H:i') }}</span>
                            </div>

                            @if($servicePost->price > 0)
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-money-bill-wave text-muted mr-2"></i>
                                    <strong class="mr-2">Price:</strong>
                                    <span>{{ number_format($servicePost->price, 2) }} {{ $servicePost->price_currency_code }}</span>
                                </div>
                            @endif

                            @if($servicePost->country || $servicePost->city)
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-muted mr-2"></i>
                                    <strong class="mr-2">Location:</strong>
                                    <span>
                                        @if($servicePost->city)
                                            {{ $servicePost->city->getTranslatedName() }},
                                        @endif
                                        @if($servicePost->country)
                                            {{ getTranslatedName($servicePost->country->getTranslatedName()) }}
                                        @endif
        </span>
                                </div>
                            @endif
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Description</h6>
                            </div>
                            <div class="card-body">
                                <div class="description-content">
                                    {!! nl2br(e($servicePost->description)) !!}
                                </div>
                            </div>
                        </div>

                        @if($servicePost->photos->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Media ({{ $servicePost->photos->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($servicePost->photos as $photo)
                                            <div class="col-md-4 mb-3">
                                                @if(Str::contains($photo->src, ['.jpg', '.jpeg', '.png', '.gif']))
                                                    <a href="{{ asset($photo->src) }}" target="_blank" class="d-block">
                                                        <img src="{{ asset($photo->src) }}" class="img-fluid rounded" alt="Post image">
                                                    </a>
                                                @elseif(Str::contains($photo->src, ['.mp3', '.wav']))
                                                    <div class="card bg-light">
                                                        <div class="card-body text-center py-3">
                                                            <i class="fas fa-music fa-3x text-info mb-2"></i>
                                                            <p class="mb-2">Audio File</p>
                                                            <audio controls class="w-100">
                                                                <source src="{{ asset($photo->src) }}" type="audio/mpeg">
                                                                Your browser does not support the audio element.
                                                            </audio>
                                                        </div>
                                                    </div>
                                                @elseif(Str::contains($photo->src, ['.mp4', '.avi', '.mov']))
                                                    <div class="card bg-light">
                                                        <div class="card-body text-center py-3">
                                                            <video controls class="img-fluid rounded">
                                                                <source src="{{ asset($photo->src) }}" type="video/mp4">
                                                                Your browser does not support the video element.
                                                            </video>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="card bg-light">
                                                        <div class="card-body text-center py-3">
                                                            <i class="fas fa-file fa-3x text-secondary mb-2"></i>
                                                            <p class="mb-0">File</p>
                                                            <a href="{{ asset($photo->src) }}" class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                                                                <i class="fas fa-download mr-1"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Location</h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="map" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('service_posts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Service Posts
                            </a>
                            <div>
                                <a href="{{ route('service_posts.edit', $servicePost->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('service_posts.destroy', $servicePost->id) }}" method="POST" class="d-inline delete-form">
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

            <div class="col-md-4">
                <!-- Badge Management Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-award text-warning mr-2"></i>
                            Badge Management
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $currentBadge = $servicePost->badgeType;
                            $badgeTypes = \App\Models\BadgeType::active()->ordered()->get();
                        @endphp

                        <!-- Current Badge -->
                        <div class="text-center mb-3">
                            @if($currentBadge)
                                <i class="{{ $currentBadge->icon }}" style="font-size: 48px; color: {{ $currentBadge->color }};"></i>
                                <h5 class="mt-2">{{ $currentBadge->name_en }}</h5>
                                <p class="text-muted mb-0">{{ $currentBadge->name_ar }}</p>
                                @if($servicePost->badge_expires_at)
                                    <small class="text-{{ $servicePost->badge_expires_at > now() ? 'success' : 'danger' }}">
                                        @if($servicePost->badge_expires_at > now())
                                            Expires: {{ $servicePost->badge_expires_at->format('M d, Y') }}
                                            ({{ $servicePost->badge_expires_at->diffInDays(now()) }} days left)
                                        @else
                                            Expired
                                        @endif
                                    </small>
                                @endif
                            @else
                                <i class="fas fa-tag" style="font-size: 48px; color: #ccc;"></i>
                                <h5 class="mt-2 text-muted">No Badge</h5>
                            @endif
                        </div>

                        <hr>

                        <!-- Apply/Change Badge Form -->
                        <form action="{{ route('service_posts.apply_badge', $servicePost->id) }}" method="POST" id="badge-form">
                            @csrf
                            <div class="form-group">
                                <label for="badge_type_id">Select Badge</label>
                                <select class="form-control" id="badge_type_id" name="badge_type_id" required>
                                    <option value="">-- Select Badge --</option>
                                    @foreach($badgeTypes as $badge)
                                        <option value="{{ $badge->id }}"
                                                data-points="{{ $badge->points_per_day }}"
                                                data-color="{{ $badge->color }}"
                                                {{ $currentBadge && $currentBadge->id == $badge->id ? 'selected' : '' }}>
                                            {{ $badge->name_en }} ({{ $badge->name_ar }}) - {{ $badge->points_per_day }} pts/day
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Total badge types: {{ count($badgeTypes) }}</small>
                            </div>

                            <div class="form-group">
                                <label for="days">Duration (Days)</label>
                                <input type="number" class="form-control" id="days" name="days" value="7" min="1" max="365" required>
                            </div>

                            <div class="alert alert-info py-2" id="cost-preview">
                                <small>
                                    <strong>Total Cost:</strong> <span id="total-cost">0</span> points
                                    <br>
                                    <strong>User Balance:</strong> <span id="user-balance">{{ $servicePost->user->pointsBalance ?? 0 }}</span> points
                                    <div id="refund-info" style="display:none;" class="mt-2 pt-2 border-top">
                                        <span class="text-success">
                                            <i class="fas fa-undo"></i> Refund: <span id="refund-amount">0</span> points
                                            (<span id="refund-days">0</span> unused days)
                                        </span>
                                        <br>
                                        <strong class="text-primary">Net Charge: <span id="net-amount">0</span> points</strong>
                                    </div>
                                </small>
                            </div>

                            <button type="button" class="btn btn-warning btn-block" id="apply-badge-btn">
                                <i class="fas fa-award mr-1"></i> <span id="badge-btn-text">Apply Badge</span>
                            </button>
                        </form>

                        @if($currentBadge && !$currentBadge->is_default)
                            <form action="{{ route('service_posts.remove_badge', $servicePost->id) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('Are you sure you want to remove the badge?')">
                                    <i class="fas fa-times mr-1"></i> Remove Badge
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-info mr-2"></i>
                            Post Performance
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-eye text-muted mr-2"></i> Views
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $servicePost->view_count }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-heart text-muted mr-2"></i> Favorites
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $servicePost->favorites()->count() }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-comments text-muted mr-2"></i> Comments
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $servicePost->comments()->count() }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-flag text-muted mr-2"></i> Reports
                                </div>
                                <span class="badge badge-{{ $servicePost->reports()->count() > 0 ? 'danger' : 'primary' }} badge-pill">
                                {{ $servicePost->reports()->count() }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                @if($servicePost->user)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user text-success mr-2"></i>
                                Posted By
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @if($servicePost->user->photos->count() > 0)
                                <img src="{{ asset($servicePost->user->photos->first()->src) }}"
                                     class="rounded-circle mb-3"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     alt="{{ $servicePost->user->user_name }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto mb-3"
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                            @endif

                            <h5 class="mb-1">{{ $servicePost->user->user_name }}</h5>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    @if($servicePost->user->city_id && $servicePost->user->city)
                                        {{ $servicePost->user->city->getTranslatedName() }},
                                    @endif
                                    @if($servicePost->user->country_id && $servicePost->user->country)
                                        {{ $servicePost->user->country->getTranslatedName() }}
                                    @else
                                        Location not specified
                                    @endif
                                </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-center">
                                    <div class="mr-3 text-center">
                                        <h5 class="mb-0">{{ $servicePost->user->servicePosts()->count() }}</h5>
                                        <small class="text-muted">Posts</small>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="mb-0">{{ $servicePost->user->pointsBalance ?? 0 }}</h5>
                                        <small class="text-muted">Points</small>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('users.show', $servicePost->user_id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user mr-1"></i> View Profile
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Recent Comments -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-comments text-primary mr-2"></i>
                            Recent Comments
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($servicePost->comments()->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($servicePost->comments()->latest()->take(5)->get() as $comment)
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between mb-1">
                                            <h6 class="mb-0">
                                                {{ $comment->user->user_name ?? 'Unknown User' }}
                                            </h6>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ Str::limit($comment->content, 100) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            @if($servicePost->comments()->count() > 5)
                                <div class="card-footer text-center bg-white">
                                    <a href="#" class="text-decoration-none">
                                        View All {{ $servicePost->comments()->count() }} Comments
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No comments yet</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Posts -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list text-primary mr-2"></i>
                            Similar Posts
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $relatedPosts = \App\Models\ServicePost::where('id', '!=', $servicePost->id)
                                ->where(function($query) use ($servicePost) {
                                    $query->where('categories_id', $servicePost->categories_id)
                                        ->orWhere('sub_categories_id', $servicePost->sub_categories_id);
                                })
                                ->where('state', 'published')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp

                        @if($relatedPosts->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($relatedPosts as $relatedPost)
                                    <a href="{{ route('service_posts.show', $relatedPost->id) }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <div class="mr-3">
                                                @if($relatedPost->photos->count() > 0)
                                                    <img src="{{ asset($relatedPost->photos->first()->src) }}"
                                                         style="width: 60px; height: 60px; object-fit: cover;"
                                                         class="rounded" alt="{{ $relatedPost->title }}">
                                                @else
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ Str::limit($relatedPost->title, 50) }}</h6>
                                                <div class="d-flex">
                                                    <small class="text-muted mr-2">
                                                        <i class="fas fa-calendar-alt mr-1"></i> {{ $relatedPost->created_at->format('M d, Y') }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-eye mr-1"></i> {{ $relatedPost->view_count }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No similar posts found</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', '') }}&callback=initMap" async defer></script>
        <script>
            function initMap() {
                try {
                    const lat = {{ $servicePost->location_latitudes ?? 0 }};
                    const lng = {{ $servicePost->location_longitudes ?? 0 }};
                    const mapElement = document.getElementById('map');

                    if (mapElement && lat && lng) {
                        const map = new google.maps.Map(mapElement, {
                            center: { lat, lng },
                            zoom: 12
                        });

                        new google.maps.Marker({
                            position: { lat, lng },
                            map: map,
                            title: "{{ $servicePost->title }}"
                        });
                    } else if (mapElement) {
                        mapElement.innerHTML = '<div class="text-center py-5"><i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i><p class="text-muted">Location coordinates not available</p></div>';
                    }
                } catch (error) {
                    console.error("Error initializing map:", error);
                    const mapElement = document.getElementById('map');
                    if (mapElement) {
                        mapElement.innerHTML = '<div class="text-center py-5"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p class="text-muted">Could not load map</p></div>';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Confirmation dialog for delete
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

                // Badge cost calculator with refund preview
                async function updateBadgeCost() {
                    console.log('updateBadgeCost called');
                    const select = document.getElementById('badge_type_id');
                    const days = document.getElementById('days');
                    const totalCost = document.getElementById('total-cost');
                    const refundInfo = document.getElementById('refund-info');
                    const refundAmount = document.getElementById('refund-amount');
                    const refundDays = document.getElementById('refund-days');
                    const netAmount = document.getElementById('net-amount');
                    const badgeBtnText = document.getElementById('badge-btn-text');

                    if (select && days && totalCost) {
                        const selectedOption = select.options[select.selectedIndex];
                        const badgeId = select.value;
                        const daysValue = parseInt(days.value) || 0;

                        if (!badgeId) {
                            totalCost.textContent = '0';
                            refundInfo.style.display = 'none';
                            badgeBtnText.textContent = 'Apply Badge';
                            return;
                        }

                        const pointsPerDay = selectedOption ? parseInt(selectedOption.dataset.points) || 0 : 0;
                        const total = pointsPerDay * daysValue;
                        totalCost.textContent = total.toLocaleString();

                        // Fetch refund preview from server
                        try {
                            const response = await fetch('{{ route("service_posts.calculate_refund_preview", $servicePost->id) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    badge_type_id: badgeId,
                                    days: daysValue
                                })
                            });

                            if (response.ok) {
                                const data = await response.json();
                                console.log('Refund preview:', data);

                                if (data.has_current_badge && data.refund_amount > 0) {
                                    refundAmount.textContent = data.refund_amount.toLocaleString();
                                    refundDays.textContent = data.refund_info.remaining_days;
                                    netAmount.textContent = data.net_amount.toLocaleString();
                                    refundInfo.style.display = 'block';
                                    badgeBtnText.textContent = 'Switch Badge';
                                } else {
                                    refundInfo.style.display = 'none';
                                    badgeBtnText.textContent = 'Apply Badge';
                                }

                                // Update balance warning if insufficient
                                if (!data.can_afford) {
                                    totalCost.parentElement.classList.remove('alert-info');
                                    totalCost.parentElement.classList.add('alert-danger');
                                } else {
                                    totalCost.parentElement.classList.remove('alert-danger');
                                    totalCost.parentElement.classList.add('alert-info');
                                }
                            }
                        } catch (error) {
                            console.error('Error fetching refund preview:', error);
                            refundInfo.style.display = 'none';
                        }
                    }
                }

                const badgeSelect = document.getElementById('badge_type_id');
                const daysInput = document.getElementById('days');

                console.log('Badge elements:', {
                    badgeSelect: badgeSelect ? 'found' : 'not found',
                    daysInput: daysInput ? 'found' : 'not found'
                });

                if (badgeSelect) {
                    console.log('Badge select options count:', badgeSelect.options.length);
                    badgeSelect.addEventListener('change', function() {
                        console.log('Badge select changed');
                        updateBadgeCost();
                    });
                }

                if (daysInput) {
                    daysInput.addEventListener('input', function() {
                        console.log('Days input changed');
                        updateBadgeCost();
                    });
                }

                // Initial calculation
                console.log('Running initial calculation');
                updateBadgeCost();

                // Form submission with confirmation
                const badgeForm = document.getElementById('badge-form');
                const applyBadgeBtn = document.getElementById('apply-badge-btn');

                if (applyBadgeBtn) {
                    applyBadgeBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const badgeSelect = document.getElementById('badge_type_id');
                        const daysInput = document.getElementById('days');
                        const refundInfo = document.getElementById('refund-info');

                        if (!badgeSelect.value) {
                            alert('Please select a badge type');
                            return;
                        }

                        const selectedBadge = badgeSelect.options[badgeSelect.selectedIndex].text;
                        const days = daysInput.value;
                        const isSwitch = refundInfo && refundInfo.style.display !== 'none';

                        let confirmMessage = `Are you sure you want to apply ${selectedBadge} for ${days} days?`;

                        if (isSwitch) {
                            const refundAmount = document.getElementById('refund-amount').textContent;
                            const refundDays = document.getElementById('refund-days').textContent;
                            const netAmount = document.getElementById('net-amount').textContent;

                            confirmMessage = `⚠️ Badge Switch Confirmation\n\n`;
                            confirmMessage += `New Badge: ${selectedBadge}\n`;
                            confirmMessage += `Duration: ${days} days\n\n`;
                            confirmMessage += `💰 Refund: ${refundAmount} points (${refundDays} unused days)\n`;
                            confirmMessage += `💳 Net Charge: ${netAmount} points\n\n`;
                            confirmMessage += `Do you want to proceed?`;
                        }

                        if (confirm(confirmMessage)) {
                            console.log('Form confirmed, submitting...');
                            badgeForm.submit();
                        } else {
                            console.log('Form submission cancelled by user');
                        }
                    });
                } else {
                    console.error('Apply badge button not found!');
                }
            });
        </script>
    @endpush
@endsection
