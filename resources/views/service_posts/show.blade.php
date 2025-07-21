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
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list text-primary mr-2"></i>
                            Service Post Details
                        </h5>
                        <div>
                            @if($servicePost->level && $servicePost->level->name['ar'] == 'ماسي')
                                <span class="badge badge-primary py-1 px-2">{{ __('service_posts\show.diamond') }}</span>
                            @elseif($servicePost->level && $servicePost->level->name['ar'] == 'ذهبي')
                                <span class="badge badge-warning py-1 px-2">{{ __('service_posts\show.gold') }}</span>
                            @endif

                            @if($servicePost->type == 'عرض')
                                <span class="badge badge-info py-1 px-2">{{ __('service_posts\show.offer') }}</span>
                            @elseif($servicePost->type == 'طلب')
                                <span class="badge badge-secondary py-1 px-2">{{ __('service_posts\show.request') }}</span>
                            @endif

                            @if($servicePost->state == 'published')
                                <span class="badge badge-success py-1 px-2">{{ __('service_posts\show.published') }}</span>
                            @elseif($servicePost->state == 'archive')
                                <span class="badge badge-warning py-1 px-2">{{ __('service_posts\show.archived') }}</span>
                            @elseif($servicePost->state == 'not published')
                                <span class="badge badge-secondary py-1 px-2">{{ __('service_posts\show.draft') }}</span>
                            @elseif($servicePost->state == 'rejected')
                                <span class="badge badge-danger py-1 px-2">{{ __('service_posts\show.rejected') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <h4 class="font-weight-bold mb-3">{{ $servicePost->field</h4>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-tag text-muted mr-2"></i>
                                <strong class="mr-2">{{ __('service_posts\show.category_') }}</strong>
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
                                <strong class="mr-2">{{ __('service_posts\show.posted_by_') }}</strong>
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
                                <strong class="mr-2">{{ __('service_posts\show.posted_') }}</strong>
                                <span>{{ $servicePost->field</span>
                            </div>

                            @if($servicePost->price > 0)
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-money-bill-wave text-muted mr-2"></i>
                                    <strong class="mr-2">{{ __('service_posts\show.price_') }}</strong>
                                    <span>{{ number_format($servicePost->price, 2) }} {{ $servicePost->price_currency_code }}</span>
                                </div>
                            @endif

                            @if($servicePost->country || $servicePost->city)
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-muted mr-2"></i>
                                    <strong class="mr-2">{{ __('service_posts\show.location_') }}</strong>
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
                                <h6 class="mb-0">{{ __('service_posts\show.description') }}</h6>
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
                                                            <p class="mb-2">{{ __('service_posts\show.audio_file') }}</p>
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
                                                            <p class="mb-0">{{ __('service_posts\show.file') }}</p>
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
                                <h6 class="mb-0">{{ __('service_posts\show.location') }}</h6>
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
                                <span class="badge badge-primary badge-pill">{{ $servicePost->field</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-heart text-muted mr-2"></i> Favorites
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $servicePost->field</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-comments text-muted mr-2"></i> Comments
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $servicePost->field</span>
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

                            <h5 class="mb-1">{{ $servicePost->field</h5>
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
                                        <h5 class="mb-0">{{ $servicePost->field</h5>
                                        <small class="text-muted">{{ __('service_posts\show.posts') }}</small>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="mb-0">{{ $servicePost->field</h5>
                                        <small class="text-muted">{{ __('service_posts\show.points') }}</small>
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
                                            <small class="text-muted">{{ $comment->field</small>
                                        </div>
                                        <p class="mb-1">{{ Str::limit($comment->field</p>
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
                                <p class="text-muted">{{ __('service_posts\show.no_comments_yet') }}</p>
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
                                                <h6 class="mb-1">{{ Str::limit($relatedPost->field</h6>
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
                                <p class="text-muted">{{ __('service_posts\show.no_similar_posts_found') }}</p>
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
                        mapElement.innerHTML = '<div class="text-center py-5"><i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i><p class="text-muted">{{ __('service_posts\show.location_coordinates_not_available') }}</p></div>{{ __('service_posts\show._') }}<div class="text-center py-5"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><p class="text-muted">{{ __('service_posts\show.could_not_load_map') }}</p></div>';
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
            });
        </script>
    @endpush
@endsection
