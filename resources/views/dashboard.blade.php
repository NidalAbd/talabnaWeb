@extends('adminlte::page')
@section('title', 'Dashboard')

@section('content_header')
    @include('partials.breadcrumbs')
    @include('partials.alert')
    <!-- Welcome Card -->
    <div class="row mb-4">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ __('dashboard.welcome') }}</h4>
                        <p class="mb-0">{{ __('dashboard.change_language_prompt') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content_top_nav_right')
    <form method="GET" action="{{ url()->current() }}" class="d-inline my-auto">
        <select name="lang" onchange="this.form.submit()" class="form-control form-control-sm" style="width:auto;display:inline;">
            <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>{{ __('dashboard.english') }}</option>
            <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>{{ __('dashboard.arabic') }}</option>
        </select>
        @foreach(request()->except('lang') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>
@endsection

@section('content')
    @include('partials.alert')
    <div class="container-fluid">
        <!-- User Stats Row -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalUsers }}</h3>
                        <p>{{ __('dashboard.registered_users') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $activeUsers }}</h3>
                        <p>{{ __('dashboard.active_users') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="{{ route('users.index') }}?status=active" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $bannedUsers }}</h3>
                        <p>{{ __('dashboard.banned_users') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <a href="{{ route('users.index') }}?status=banned" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $totalReports }}</h3>
                        <p>{{ __('dashboard.total_reports') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Service Posts Stats Row -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $totalPosts }}</h3>
                        <p>{{ __('dashboard.total_service_posts') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <a href="{{ route('service_posts.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $publishedPosts }}</h3>
                        <p>{{ __('dashboard.published_posts') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('service_posts.index') }}?status=published" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $notPublishedPosts }}</h3>
                        <p>{{ __('dashboard.not_published_posts') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('service_posts.index') }}?status=not%20published" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $rejectedPosts }}</h3>
                        <p>{{ __('dashboard.rejected_posts') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                    <a href="{{ route('service_posts.index') }}?status=rejected" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
        <!-- Points System Stats Row -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($totalPoints) }}</h3>
                        <p>{{ __('dashboard.total_points_in_system') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <a href="{{ route('palservice_points.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($pointsUsedLifetime) }}</h3>
                        <p>{{ __('dashboard.points_used') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <a href="{{ route('point_transactions.index') }}?type=used" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $pendingPurchaseRequests }}</h3>
                        <p>{{ __('dashboard.pending_purchase_requests') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <a href="{{ route('palservice_points.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Points Used Detailed Statistics -->
        <div class="row">
            <div class="col-12">
                @include('components.points-used-stats')
            </div>
        </div>

        <!-- Badge and Type Stats -->
        <div class="row">
            <div class="col-md-6">
                <!-- Post Badge Stats -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.post_badge_distribution') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="badgeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Post Type Stats -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.post_type_distribution') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="postTypeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6">
                <!-- Posts by Month Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.posts_by_month') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="postsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Users by Month Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.users_by_month') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="usersChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <!-- Posts by Category Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.posts_by_category') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Point Transactions Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.point_transactions_by_month') }}</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="pointsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Row -->
        <div class="row">
            <div class="col-md-12">
                <!-- Map Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.service_posts_map') }}</h3>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row">
            <div class="col-md-6">
                <!-- Latest Service Posts -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.latest_service_posts') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ __('dashboard.id') }}</th>
                                <th>{{ __('dashboard.title') }}</th>
                                <th>{{ __('dashboard.user') }}</th>
                                <th>{{ __('dashboard.status') }}</th>
                                <th>{{ __('dashboard.date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentPosts as $post)
                                <tr>
                                    <td>{{ $post->id }}</td>
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->user->user_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($post->state == 'published')
                                            <span class="badge badge-success">{{ __('dashboard.published') }}</span>
                                        @elseif($post->state == 'not published')
                                            <span class="badge badge-warning">{{ __('dashboard.not_published') }}</span>
                                        @elseif($post->state == 'archive')
                                            <span class="badge badge-secondary">{{ __('dashboard.archived') }}</span>
                                        @elseif($post->state == 'rejected')
                                            <span class="badge badge-danger">{{ __('dashboard.rejected') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $post->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        <a href="{{ route('service_posts.index') }}" class="btn btn-sm btn-info float-right">{{ __('dashboard.view_all_service_posts') }}</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Latest Users -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('dashboard.recent_users') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ __('dashboard.id') }}</th>
                                <th>{{ __('dashboard.username') }}</th>
                                <th>{{ __('dashboard.email') }}</th>
                                <th>{{ __('dashboard.status') }}</th>
                                <th>{{ __('dashboard.date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentUsers as $ruser)
                                <tr>
                                    <td>{{ $ruser->id }}</td>
                                    <td>{{ $ruser->user_name }}</td>
                                    <td>{{ $ruser->email }}</td>
                                    <td>
                                        @if($ruser->is_active == 'active')
                                            <span class="badge badge-success">{{ __('dashboard.active') }}</span>
                                        @elseif($ruser->is_active == 'inactive')
                                            <span class="badge badge-warning">{{ __('dashboard.inactive') }}</span>
                                        @elseif($ruser->is_active == 'banned')
                                            <span class="badge badge-danger">{{ __('dashboard.banned') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $ruser->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-info float-right">{{ __('dashboard.view_all_users') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        #map {
            width: 100%;
            height: 400px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if(config('services.google.maps_api_key_web'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key_web') }}&callback=initMap" async defer></script>
    @else
        <script>
            // Fallback when API key is not configured
            function initMap() {
                document.getElementById('map').innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Google Maps API key is not configured. Please add GOOGLE_MAPS_API_KEY_WEB to your .env file.</div>';
            }
        </script>
    @endif    <script>
        $(function () {
            // Badge Distribution Chart
            var badgeChartCanvas = document.getElementById('badgeChart').getContext('2d');
            var badgeChart = new Chart(badgeChartCanvas, {
                type: 'pie',
                data: {
                    labels: ['Normal', 'Golden', 'Diamond'],
                    datasets: [{
                        data: [{{ $normalPosts }}, {{ $goldenPosts }}, {{ $diamondPosts }}],
                        backgroundColor: ['#6c757d', '#ffc107', '#17a2b8']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Post Type Chart
            var postTypeChartCanvas = document.getElementById('postTypeChart').getContext('2d');
            var postTypeChart = new Chart(postTypeChartCanvas, {
                type: 'pie',
                data: {
                    labels: ['Offers', 'Requests'],
                    datasets: [{
                        data: [{{ $offerPosts }}, {{ $requestPosts }}],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Posts by Month Chart
            var postsChartCanvas = document.getElementById('postsChart').getContext('2d');
            var postsChart = new Chart(postsChartCanvas, {
                type: 'line',
                data: {
                    labels: {!! json_encode($postsByMonth['labels']) !!},
                    datasets: [{
                        label: 'Posts',
                        data: {!! json_encode($postsByMonth['data']) !!},
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Users by Month Chart
            var usersChartCanvas = document.getElementById('usersChart').getContext('2d');
            var usersChart = new Chart(usersChartCanvas, {
                type: 'line',
                data: {
                    labels: {!! json_encode($usersByMonth['labels']) !!},
                    datasets: [{
                        label: 'New Users',
                        data: {!! json_encode($usersByMonth['data']) !!},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Categories Chart
            var categoryChartCanvas = document.getElementById('categoryChart').getContext('2d');
            var categoryChart = new Chart(categoryChartCanvas, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($postsByCategory['labels']) !!},
                    datasets: [{
                        label: 'Posts Count',
                        data: {!! json_encode($postsByCategory['data']) !!},
                        backgroundColor: 'rgba(60, 141, 188, 0.7)',
                        borderColor: 'rgba(60, 141, 188, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Points Chart
            var pointsChartCanvas = document.getElementById('pointsChart').getContext('2d');
            var pointsChart = new Chart(pointsChartCanvas, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($pointTransactionsByMonth['labels']) !!},
                    datasets: [
                        {
                            label: 'Transactions Count',
                            data: {!! json_encode($pointTransactionsByMonth['counts']) !!},
                            backgroundColor: 'rgba(60, 141, 188, 0.7)',
                            borderColor: 'rgba(60, 141, 188, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Points Volume',
                            data: {!! json_encode($pointTransactionsByMonth['points']) !!},
                            type: 'line',
                            borderColor: '#ffc107',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointBackgroundColor: '#ffc107',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Transactions'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Points'
                            }
                        }
                    }
                }
            });

            // Initialize the map
            function initMap() {
                // Check if Google Maps API is loaded
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    document.getElementById('map').innerHTML = '<div class="alert alert-warning">{{ __('dashboard.google_maps_could_not_be_loaded_please_') }}</div>';
                    return;
                }

                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {lat: 0, lng: 0},
                    zoom: 2
                });

                // Add markers for service posts
                var markers = [];
                var bounds = new google.maps.LatLngBounds();
                var infoWindow = new google.maps.InfoWindow();

                @foreach($mapData as $post)
                @if($post->location_latitudes && $post->location_longitudes)
                var position = {
                    lat: {{ $post->location_latitudes }},
                    lng: {{ $post->location_longitudes }}
                };

                var marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: "{{ $post->title }}"
                });

                // Create info window content
                var content = '<div><strong>{{ $post->title }}</strong><br>{{ __('dashboard._') }}<br><a href="{{ route('service_posts.show', $post->id) }}">{{ __('dashboard.view_post') }}</a></div>';

                // Add click listener to marker
                google.maps.event.addListener(marker, 'click', (function(marker, content) {
                    return function() {
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    }
                })(marker, content));

                markers.push(marker);
                bounds.extend(position);
                @endif
                @endforeach

                // If we have markers, fit the map to show all of them
                if (markers.length > 0) {
                    map.fitBounds(bounds);
                }
            }

            // Load the map when the page is fully loaded
            // Note: initMap is called by the Google Maps API callback
            // No need to call it again in document.ready
        });
    </script>
@stop
