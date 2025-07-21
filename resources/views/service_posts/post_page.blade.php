@extends('adminlte::page')
@section('title', "Service Posts")
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter text-primary mr-2"></i>
                            Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filter-form" action="{{ route('user_all_service.index') }}" method="GET">
                            <!-- Categories -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.category') }}</label>
                                <select name="category" id="category-filter" class="form-control">
                                    <option value="">{{ __('service_posts\post_page.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name[app()->getLocale()] ?? $category->name['en'] ?? 'Unknown' }}
                                            ({{ $category->service_posts_count ?? 0 }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subcategories -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.subcategory') }}</label>
                                <select name="subcategory" id="subcategory-filter" class="form-control">
                                    <option value="">{{ __('service_posts\post_page.all_subcategories') }}</option>
                                    <!-- Populated by JavaScript -->
                                </select>
                            </div>

                            <!-- Type -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.type') }}</label>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type-all" name="type" value="" class="custom-control-input"
                                        {{ !request('type') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type-all">{{ __('service_posts\post_page.all') }}</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type-offer" name="type" value="عرض" class="custom-control-input"
                                        {{ request('type') == 'عرض' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type-offer">{{ __('service_posts\post_page.offers') }}</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type-request" name="type" value="طلب" class="custom-control-input"
                                        {{ request('type') == 'طلب' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="type-request">{{ __('service_posts\post_page.requests') }}</label>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.price_range') }}</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" name="min_price" class="form-control form-control-sm mr-2" placeholder="Min" value="{{ request('min_price') }}">
                                    <span>{{ __('service_posts\post_page._') }}</span>
                                    <input type="number" name="max_price" class="form-control form-control-sm ml-2" placeholder="Max" value="{{ request('max_price') }}">
                                </div>
                            </div>

                            <!-- Sort By -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.sort_by') }}</label>
                                <select name="sort" class="form-control">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>{{ __('service_posts\post_page.newest_first') }}</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('service_posts\post_page.oldest_first') }}</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>{{ __('service_posts\post_page.price_low_to_high_') }}</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>{{ __('service_posts\post_page.price_high_to_low_') }}</option>
                                    <option value="most_viewed" {{ request('sort') == 'most_viewed' ? 'selected' : '' }}>{{ __('service_posts\post_page.most_viewed') }}</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.location') }}</label>
                                <select name="country" id="country-filter" class="form-control mb-2">
                                    <option value="">{{ __('service_posts\post_page.all_countries') }}</option>
                                    @foreach(\App\Models\countries::all() as $country)
                                        <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                                            {{ getTranslatedName($country->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="city" id="city-filter" class="form-control">
                                    <option value="">{{ __('service_posts\post_page.all_cities') }}</option>
                                    <!-- Populated by JavaScript -->
                                </select>
                            </div>

                            <!-- Distance -->
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('service_posts\post_page.distance') }}</label>
                                <div class="input-group">
                                    <input type="number" name="distance" class="form-control" placeholder="km" value="{{ request('distance') }}">
                                    <div class="input-group-append">
                                        <button type="button" id="use-my-location" class="btn btn-outline-secondary">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Distance from your location
                                </small>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter mr-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('user_all_service.index') }}" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-undo mr-1"></i> Reset Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Popular Categories Card -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-th-large text-primary mr-2"></i>
                            Popular Categories
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($categories->sortByDesc('service_posts_count')->take(5) as $popularCategory)
                                    <div>
                                        @if($popularCategory->photos->count() > 0)
                                            <img src="{{ asset($popularCategory->photos->first()->src) }}"
                                                 alt="{{ $popularCategory->name[app()->getLocale()] ?? 'Category' }}"
                                                 class="mr-2" style="width: 24px; height: 24px;">
                                        @else
                                            <i class="fas fa-folder mr-2 text-muted"></i>
                                        @endif
                                        {{ $popularCategory->name[app()->getLocale()] ?? $popularCategory->name['en'] ?? 'Unknown' }}
                                    </div>
                                    <span class="badge badge-primary badge-pill">{{ $popularCategory->field</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            @if(isset($category))
                                {{ $category->name[app()->getLocale()] ?? $category->name['en'] ?? 'Category' }}
                                @if(isset($subcategory))
                                    <i class="fas fa-chevron-right mx-2 small"></i>
                                    {{ $subcategory->name[app()->getLocale()] ?? $subcategory->name['en'] ?? 'Subcategory' }}
                                @endif
                            @else
                                Service Posts
                            @endif
                        </h4>
                        <p class="text-muted mb-0">{{ $servicePosts->field</p>
                    </div>
                    <div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary active" data-view="grid">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="search-form" action="{{ url()->current() }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search for services..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Service Posts Grid -->
                <div id="grid-view" class="row">
                    @forelse($servicePosts as $post)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="position-relative">
                                    <a href="{{ route('service_posts.show', $post->id) }}">
                                        @if($post->photos->count() > 0)
                                            <img src="{{ asset($post->photos->first()->src) }}"
                                                 class="card-img-top" style="height: 180px; object-fit: cover;"
                                                 alt="{{ $post->title }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="height: 180px;">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </a>

                                    @if($post->level && $post->level->name['ar'] == 'ماسي')
                                        <span class="badge badge-primary position-absolute" style="top: 10px; left: 10px;">{{ __('service_posts\post_page.diamond') }}</span>
                                    @elseif($post->level && $post->level->name['ar'] == 'ذهبي')
                                        <span class="badge badge-warning position-absolute" style="top: 10px; left: 10px;">{{ __('service_posts\post_page.gold') }}</span>
                                    @endif

                                    <span class="badge {{ $post->type == 'عرض' ? 'badge-info' : 'badge-secondary' }} position-absolute"
                                          style="top: 10px; right: 10px;">
                                    {{ $post->type == 'عرض' ? 'Offer' : 'Request' }}
                                </span>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title mb-1">
                                        <a href="{{ route('service_posts.show', $post->id) }}" class="text-decoration-none text-dark">
                                            {{ Str::limit($post->title, 40) }}
                                        </a>
                                    </h5>

                                    <div class="d-flex align-items-center mb-2">
                                        <small class="text-muted mr-2">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? 'Unknown' }}
                                        </small>

                                        @if($post->price > 0)
                                            <small class="text-primary ml-auto">
                                                <strong>{{ number_format($post->price, 0) }} {{ $post->price_currency_code }}</strong>
                                            </small>
                                        @endif
                                    </div>

                                    <p class="card-text text-muted small" style="min-height: 60px;">
                                        {{ Str::limit($post->description, 100) }}
                                    </p>
                                </div>

                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            @if($post->user && $post->user->photos->count() > 0)
                                                <img src="{{ asset($post->user->photos->first()->src) }}"
                                                     class="rounded-circle mr-1" style="width: 25px; height: 25px; object-fit: cover;">
                                            @else
                                                <i class="fas fa-user-circle mr-1 text-muted"></i>
                                            @endif
                                            <small class="text-muted">
                                                {{ $post->user->user_name ?? 'Unknown' }}
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt mr-1"></i> {{ $post->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                                    <h4>{{ __('service_posts\post_page.no_service_posts_found') }}</h4>
                                    <p class="mb-4">{{ __('service_posts\post_page.try_adjusting_your_search_or_filter_crit') }}</p>
                                    <a href="{{ route('user_all_service.index') }}" class="btn btn-primary">
                                        <i class="fas fa-undo mr-1"></i> Reset Filters
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Service Posts List View (Hidden by default) -->
                <div id="list-view" class="d-none">
                    @forelse($servicePosts as $post)
                        <div class="card shadow-sm mb-3">
                            <div class="row no-gutters">
                                <div class="col-md-3">
                                    <a href="{{ route('service_posts.show', $post->id) }}">
                                        @if($post->photos->count() > 0)
                                            <img src="{{ asset($post->photos->first()->src) }}"
                                                 class="img-fluid h-100" style="object-fit: cover;"
                                                 alt="{{ $post->title }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title mb-1">
                                                <a href="{{ route('service_posts.show', $post->id) }}" class="text-decoration-none text-dark">
                                                    {{ $post->title }}
                                                </a>
                                            </h5>
                                            <div>
                                                @if($post->level && $post->level->name['ar'] == 'ماسي')
                                                    <span class="badge badge-primary">{{ __('service_posts\post_page.diamond') }}</span>
                                                @elseif($post->level && $post->level->name['ar'] == 'ذهبي')
                                                    <span class="badge badge-warning">{{ __('service_posts\post_page.gold') }}</span>
                                                @endif

                                                <span class="badge {{ $post->type == 'عرض' ? 'badge-info' : 'badge-secondary' }}">
                                                {{ $post->type == 'عرض' ? 'Offer' : 'Request' }}
                                            </span>
                                            </div>
                                        </div>

                                        <div class="d-flex mb-2">
                                            <small class="text-muted mr-3">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? 'Unknown' }}
                                                @if($post->subCategory)
                                                    <i class="fas fa-chevron-right mx-1"></i>
                                                    {{ $post->subCategory->name[app()->getLocale()] ?? $post->subCategory->name['en'] ?? 'Unknown' }}
                                                @endif
                                            </small>

                                            @if($post->price > 0)
                                                <small class="text-primary font-weight-bold mr-3">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    {{ number_format($post->price, 0) }} {{ $post->price_currency_code }}
                                                </small>
                                            @endif

                                            @if($post->country || $post->city)
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    @if($post->city)
                                                        {{ $post->city->name }},
                                                    @endif
                                                    @if($post->country)
                                                        {{ getTranslatedName($country->name) }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>

                                        <p class="card-text text-muted">{{ Str::limit($post->field</p>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div class="d-flex align-items-center">
                                                @if($post->user && $post->user->photos->count() > 0)
                                                    <img src="{{ asset($post->user->photos->first()->src) }}"
                                                         class="rounded-circle mr-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                @else
                                                    <i class="fas fa-user-circle mr-2 text-muted" style="font-size: 30px;"></i>
                                                @endif
                                                <span class="text-muted">
                                                {{ $post->user->user_name ?? 'Unknown' }}
                                            </span>
                                            </div>

                                            <div>
                                                <small class="text-muted mr-3">
                                                    <i class="far fa-calendar-alt mr-1"></i> {{ $post->created_at->diffForHumans() }}
                                                </small>
                                                <small class="text-muted mr-3">
                                                    <i class="far fa-eye mr-1"></i> {{ $post->view_count }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="far fa-heart mr-1"></i> {{ $post->favorites_count ?? 0 }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-4"></i>
                                <h4>{{ __('service_posts\post_page.no_service_posts_found') }}</h4>
                                <p class="mb-4">{{ __('service_posts\post_page.try_adjusting_your_search_or_filter_crit') }}</p>
                                <a href="{{ route('user_all_service.index') }}" class="btn btn-primary">
                                    <i class="fas fa-undo mr-1"></i> Reset Filters
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $servicePosts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle between grid and list view
                document.querySelectorAll('[data-view]').forEach(button => {
                    button.addEventListener('click', function() {
                        const view = this.getAttribute('data-view');

                        // Toggle active class
                        document.querySelectorAll('[data-view]').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        this.classList.add('active');

                        // Show/hide the appropriate view
                        if (view === 'grid') {
                            document.getElementById('grid-view').classList.remove('d-none');
                            document.getElementById('list-view').classList.add('d-none');
                        } else {
                            document.getElementById('grid-view').classList.add('d-none');
                            document.getElementById('list-view').classList.remove('d-none');
                        }
                    });
                });

                // Function to submit filter form automatically
                function autoSubmitFilterForm() {
                    $('#filter-form').submit();
                }

                // Load subcategories when category changes
                $('#category-filter').on('change', function() {
                    const categoryId = $(this).val();
                    if (categoryId) {
                        $.ajax({
                            url: "{{ url('/sub-categories') }}/" + categoryId,
                            type: 'GET',
                            success: function(data) {
                                let options = '<option value="">{{ __('service_posts\post_page.all_subcategories') }}</option>';
                                data.subcategories.forEach(function(subcategory) {
                                    const name = subcategory.name['{{ app()->getLocale() }}'] || subcategory.name['en'] || 'Unknown';
                                    options += `<option value="${subcategory.id}">{{ __('service_posts\post_page._name_subcategory_service_posts_cou') }}</option>{{ __('service_posts\post_page._') }}<option value="">{{ __('service_posts\post_page.all_subcategories') }}</option>{{ __('service_posts\post_page._') }}<option value="">{{ __('service_posts\post_page.all_cities') }}</option>';
                                data.forEach(function(city) {
                                    const cityName = city.name['{{ app()->getLocale() }}'] || city.name['en'] || city.name;
                                    options += `<option value="${city.id}">{{ __('service_posts\post_page._cityname_') }}</option>{{ __('service_posts\post_page._') }}<option value="">{{ __('service_posts\post_page.all_cities') }}</option>{{ __('service_posts\post_page._') }}<input type="hidden" name="lat" value="${position.coords.latitude}">{{ __('service_posts\post_page._fil') }}<input type="hidden" name="lng" value="${position.coords.longitude}">{{ __('service_posts\post_page._if') }}<div class="text-center mt-3"><div class="spinner-border text-primary" role="status"><span class="sr-only">{{ __('service_posts\post_page.loading_') }}</span></div></div>')
                        .insertAfter($(this).find('button[type="submit"]'));
                });
            });
        </script>
    @endpush
@endsection
