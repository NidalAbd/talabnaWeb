@extends('adminlte::page')
@section('title', "Create New Service Post")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-plus-circle text-primary mr-2"></i> Create New Service Post</h1>
        <div>
            <a href="{{ route('service_posts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle text-primary mr-2"></i>
                            Create New Service Post
                        </h5>
                    </div>

                    <form action="{{ route('service_posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Your Point Balance:</strong> {{ Auth::user()->pointsBalance ?? 0 }} points
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title" class="font-weight-bold">
                                                    Title <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" id="title" name="title"
                                                       class="form-control @error('title') is-invalid @enderror"
                                                       value="{{ old('title') }}" required>
                                                @error('title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="description" class="font-weight-bold">
                                                    Description <span class="text-danger">*</span>
                                                </label>
                                                <textarea id="description" name="description" rows="5"
                                                          class="form-control @error('description') is-invalid @enderror"
                                                          required>{{ old('description') }}</textarea>
                                                @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type" class="font-weight-bold">
                                                    Type <span class="text-danger">*</span>
                                                </label>
                                                <select name="type" id="type"
                                                        class="form-control @error('type') is-invalid @enderror" required>
                                                    <option value="عرض" {{ old('type') == 'عرض' ? 'selected' : '' }}>Offer</option>
                                                    <option value="طلب" {{ old('type') == 'طلب' ? 'selected' : '' }}>Request</option>
                                                </select>
                                                @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="state" class="font-weight-bold">
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select name="state" id="state"
                                                        class="form-control @error('state') is-invalid @enderror" required>
                                                    <option value="published" {{ old('state') == 'published' ? 'selected' : '' }}>Published</option>
                                                    <option value="archive" {{ old('state') == 'archive' ? 'selected' : '' }}>Archive</option>
                                                    <option value="not published" {{ old('state') == 'not published' ? 'selected' : '' }}>Draft</option>
                                                </select>
                                                @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category Selection -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-th-large mr-1"></i>
                                        Category & Subcategory
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="categories_id" class="font-weight-bold">
                                                    Category <span class="text-danger">*</span>
                                                </label>
                                                <select name="categories_id" id="categories_id"
                                                        class="form-control @error('categories_id') is-invalid @enderror" required>
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ old('categories_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name[app()->getLocale()] ?? $category->name['en'] ?? 'Unknown' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('categories_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sub_categories_id" class="font-weight-bold">
                                                    Subcategory <span class="text-danger">*</span>
                                                </label>
                                                <select name="sub_categories_id" id="sub_categories_id"
                                                        class="form-control @error('sub_categories_id') is-invalid @enderror" required>
                                                    <option value="">Select Category First</option>
                                                </select>
                                                @error('sub_categories_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Price & Location -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-tag mr-1"></i>
                                        Price & Location
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="price" class="font-weight-bold">Price</label>
                                                <input type="number" id="price" name="price"
                                                       class="form-control @error('price') is-invalid @enderror"
                                                       value="{{ old('price', 0) }}">
                                                @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="price_currency_code" class="font-weight-bold">Currency</label>
                                                <select name="price_currency_code" id="price_currency_code"
                                                        class="form-control @error('price_currency_code') is-invalid @enderror">
                                                    <option value="USD" {{ old('price_currency_code') == 'USD' ? 'selected' : '' }}>USD</option>
                                                    @foreach($countries ?? [] as $country)
                                                        @if($country->currency_code)
                                                            <option value="{{ $country->currency_code }}" {{ old('price_currency_code') == $country->currency_code ? 'selected' : '' }}>
                                                                {{ $country->currency_code }} - {{ getTranslatedName($country->name) }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                @error('price_currency_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="country_id" class="font-weight-bold">Country</label>
                                                <select name="country_id" id="country_id"
                                                        class="form-control @error('country_id') is-invalid @enderror">
                                                    <option value="">Select Country</option>
                                                    @foreach($countries ?? [] as $country)
                                                        <option value="{{ $country->id }}"
                                                                data-currency="{{ $country->currency_code }}"
                                                            {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                            {{ getTranslatedName($country->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('country_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city_id" class="font-weight-bold">City</label>
                                                <select name="city_id" id="city_id"
                                                        class="form-control @error('city_id') is-invalid @enderror">
                                                    <option value="">Select Country First</option>
                                                </select>
                                                @error('city_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="location_latitudes" class="font-weight-bold">
                                                    Latitude <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" step="any" id="location_latitudes" name="location_latitudes"
                                                       class="form-control @error('location_latitudes') is-invalid @enderror"
                                                       value="{{ old('location_latitudes') }}" required>
                                                @error('location_latitudes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="location_longitudes" class="font-weight-bold">
                                                    Longitude <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" step="any" id="location_longitudes" name="location_longitudes"
                                                       class="form-control @error('location_longitudes') is-invalid @enderror"
                                                       value="{{ old('location_longitudes') }}" required>
                                                @error('location_longitudes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <button type="button" id="use-my-location" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-map-marker-alt mr-1"></i> Use My Location
                                                </button>
                                                <small class="form-text text-muted">
                                                    Click to use your current location (requires location permission)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-award mr-1"></i>
                                        Promotion Badge (Optional)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="have_badge" class="font-weight-bold">Badge Type</label>
                                                <select name="have_badge" id="have_badge"
                                                        class="form-control @error('have_badge') is-invalid @enderror">
                                                    <option value="عادي" {{ old('have_badge') == 'عادي' ? 'selected' : '' }}>Standard (Free)</option>
                                                    <option value="ذهبي" {{ old('have_badge') == 'ذهبي' ? 'selected' : '' }}>Gold (1 point per day)</option>
                                                    <option value="ماسي" {{ old('have_badge') == 'ماسي' ? 'selected' : '' }}>Diamond (3 points per day)</option>
                                                </select>
                                                @error('have_badge')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">
                                                    Promoted posts appear at the top of search results
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="badge_duration" class="font-weight-bold">Duration (days)</label>
                                                <input type="number" id="badge_duration" name="badge_duration"
                                                       class="form-control @error('badge_duration') is-invalid @enderror"
                                                       value="{{ old('badge_duration', 0) }}" min="0">
                                                @error('badge_duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">
                                                    <span id="total-cost">Total cost: 0 points</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photos -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-images mr-1"></i>
                                        Photos <span class="text-danger">*</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                                   id="photos" name="images[]" multiple required onchange="previewImages(this)">
                                            <label class="custom-file-label" for="images">Choose files...</label>
                                            @error('photos')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            @error('photos.*')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            You can upload multiple images. Max file size: 2MB per image.
                                            Allowed formats: JPG, PNG, HEIC, HEIF, MP3, MP4
                                        </small>
                                    </div>

                                    <div class="row mt-3" id="image-preview-container">
                                        <!-- Image previews will be inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-between">
                            <a href="{{ route('service_posts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Service Posts
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Create Service Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @section('js')
        <script>
            console.log("Testing if AdminLTE JS section works");
            $(document).ready(function() {
                console.log("Document ready with jQuery");

                // Initialize file input with file name display
                if (typeof bsCustomFileInput !== 'undefined') {
                    bsCustomFileInput.init();
                }

                // Category change event
                $('#categories_id').on('change', function() {
                    console.log("Category changed:", $(this).val());
                    const categoryId = $(this).val();

                    if (categoryId) {
                        $.ajax({
                            url: "{{ route('fetchSubcategories') }}",
                            type: 'GET',
                            data: { category_id: categoryId },
                            success: function(data) {
                                console.log("Data received:", data);
                                let options = '<option value="">Select Subcategory</option>';
                                data.forEach(function(subcategory, index) {
                                    const name = subcategory.name['{{ app()->getLocale() }}'] || subcategory.name['en'] || 'Unknown';
                                    // Set selected attribute on the first subcategory
                                    const selected = index === 0 ? 'selected' : '';
                                    options += `<option value="${subcategory.id}" ${selected}>${name}</option>`;
                                });
                                $('#sub_categories_id').html(options);

                                // Trigger change event to ensure any dependent fields update
                                $('#sub_categories_id').trigger('change');
                            },
                            error: function(xhr, status, error) {
                                console.error("Error:", error);
                                $('#sub_categories_id').html('<option value="">Error loading subcategories</option>');
                            }
                        });
                    } else {
                        $('#sub_categories_id').html('<option value="">Select Category First</option>');
                    }
                });

                // Country change event
                $('#country_id').on('change', function() {
                    const countryId = $(this).val();
                    console.log("Country changed:", countryId);

                    if (countryId) {
                        $.ajax({
                            url: `/get-cities-for-form/${countryId}`,
                            type: 'GET',
                            success: function(data) {
                                console.log("Cities received:", data);
                                let options = '<option value="">Select City</option>';

                                data.forEach(function(city, index) {
                                    let cityName;

                                    // Handle various name formats
                                    if (typeof city.name === 'string' && city.name.startsWith('{')) {
                                        try {
                                            const nameObj = JSON.parse(city.name);
                                            const locale = $('html').attr('lang') || 'en';
                                            cityName = nameObj[locale] || nameObj['en'] || Object.values(nameObj)[0];
                                        } catch (e) {
                                            cityName = city.name;
                                        }
                                    }
                                    else if (typeof city.name === 'object') {
                                        const locale = $('html').attr('lang') || 'en';
                                        cityName = city.name[locale] || city.name['en'] || Object.values(city.name)[0];
                                    }
                                    else {
                                        cityName = city.name;
                                    }

                                    // Set selected attribute on the first city
                                    const selected = index === 0 ? 'selected' : '';
                                    options += `<option value="${city.id}" ${selected}>${cityName}</option>`;
                                });

                                $('#city_id').html(options);

                                // Trigger change event to ensure any dependent fields update
                                $('#city_id').trigger('change');
                            },
                            error: function(xhr, status, error) {
                                console.error("Error loading cities:", error);
                                $('#city_id').html('<option value="">Error loading cities</option>');
                            }
                        });

                        // Update currency code
                        const currencyCode = $(this).find('option:selected').data('currency');
                        if (currencyCode) {
                            $('#price_currency_code').val(currencyCode);
                        }
                    } else {
                        $('#city_id').html('<option value="">Select Country First</option>');
                    }
                });

                // Calculate points cost when badge type or duration changes
                $('#have_badge, #badge_duration').on('change input', function() {
                    const badgeType = $('#have_badge').val();
                    const duration = parseInt($('#badge_duration').val()) || 0;
                    let cost = 0;

                    if (badgeType === 'ذهبي') { // Gold
                        cost = duration * 1;
                    } else if (badgeType === 'ماسي') { // Diamond
                        cost = duration * 3;
                    }

                    $('#total-cost').text(`Total cost: ${cost} points`);
                });

                // Use current location button
                $('#use-my-location').on('click', function() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            $('#location_latitudes').val(position.coords.latitude);
                            $('#location_longitudes').val(position.coords.longitude);
                        }, function(error) {
                            alert('Error getting location: ' + error.message);
                        });
                    } else {
                        alert('Geolocation is not supported by this browser.');
                    }
                });
            });

            // Preview images before upload function
            function previewImages(input) {
                const container = document.getElementById('image-preview-container');
                container.innerHTML = '';

                if (input.files && input.files.length > 0) {
                    for (let i = 0; i < input.files.length; i++) {
                        const reader = new FileReader();
                        const file = input.files[i];

                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 mb-3';

                            const card = document.createElement('div');
                            card.className = 'card h-100';

                            const cardBody = document.createElement('div');
                            cardBody.className = 'card-body text-center p-2';

                            // Create preview based on file type
                            if (file.type.startsWith('image/')) {
                                // Image preview
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'img-fluid';
                                img.style.maxHeight = '150px';
                                cardBody.appendChild(img);
                            } else if (file.type.startsWith('audio/')) {
                                // Audio preview
                                const audioIcon = document.createElement('i');
                                audioIcon.className = 'fas fa-music fa-3x text-info mb-2';

                                const audioName = document.createElement('p');
                                audioName.className = 'mb-0 text-truncate';
                                audioName.textContent = file.name;

                                cardBody.appendChild(audioIcon);
                                cardBody.appendChild(audioName);
                            } else if (file.type.startsWith('video/')) {
                                // Video preview
                                const videoIcon = document.createElement('i');
                                videoIcon.className = 'fas fa-video fa-3x text-danger mb-2';

                                const videoName = document.createElement('p');
                                videoName.className = 'mb-0 text-truncate';
                                videoName.textContent = file.name;

                                cardBody.appendChild(videoIcon);
                                cardBody.appendChild(videoName);
                            }

                            const cardFooter = document.createElement('div');
                            cardFooter.className = 'card-footer bg-light p-1';
                            cardFooter.innerHTML = `<small class="text-muted">${file.name.substring(0, 20)}${file.name.length > 20 ? '...' : ''}</small>`;

                            card.appendChild(cardBody);
                            card.appendChild(cardFooter);
                            col.appendChild(card);
                            container.appendChild(col);
                        }

                        reader.readAsDataURL(file);
                    }
                }
            }
        </script>
    @stop
@endsection
