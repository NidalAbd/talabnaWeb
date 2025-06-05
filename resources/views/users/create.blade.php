@extends('adminlte::page')

@section('title', 'Create User')

@section('content_header')
    @include('partials.breadcrumbs')
    @include('partials.alert')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-plus text-success mr-2"></i> Create New User</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">User Information</h3>
                    </div>

                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center mb-4">
                                        <div class="img-preview mb-3">
                                            <img id="preview-image" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}"
                                                 alt="User profile picture" class="profile-user-img img-fluid img-circle"
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        </div>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="profile-image" name="profile_image">
                                                <label class="custom-file-label" for="profile-image">Choose Profile Image</label>
                                            </div>
                                        </div>
                                        @error('profile_image')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="auth_type">Authentication Type</label>
                                        <select class="form-control @error('auth_type') is-invalid @enderror" id="auth_type" name="auth_type">
                                            <option value="email" selected>Email</option>
                                            <option value="google">Google</option>
                                        </select>
                                        @error('auth_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="data_saver_enabled" name="data_saver_enabled" value="1">
                                            <label class="custom-control-label" for="data_saver_enabled">Data Saver Enabled</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                                                @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="user_name">Username <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Enter username" required>
                                                @error('user_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                                                @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter password" required>
                                                @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="ذكر" {{ old('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                                    <option value="انثى" {{ old('gender') == 'انثى' ? 'selected' : '' }}>انثى</option>
                                                </select>
                                                @error('gender')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country_id">Country</label>
                                                <select class="form-control select2 @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
                                                    <option value="">Select Country</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                            {{ getTranslatedName($country->name) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('country_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city_id">City</label>
                                                <select class="form-control select2 @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                                    <option value="">Select City</option>
                                                    <!-- Cities will be populated via AJAX based on country selection -->
                                                </select>
                                                @error('city_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_of_birth">Date of Birth</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                                @error('date_of_birth')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phones">Phone Number</label>
                                                <input type="text" class="form-control @error('phones') is-invalid @enderror" id="phones" name="phones" value="{{ old('phones') }}" placeholder="Enter phone number">
                                                @error('phones')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="WatsNumber">WhatsApp Number</label>
                                                <input type="text" class="form-control @error('WatsNumber') is-invalid @enderror" id="WatsNumber" name="WatsNumber" value="{{ old('WatsNumber') }}" placeholder="Enter WhatsApp number">
                                                @error('WatsNumber')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_active">Account Status</label>
                                                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                                    <option value="active" selected>Active</option>
                                                    <option value="inactive">Inactive</option>
                                                    <option value="banned">Banned</option>
                                                </select>
                                                @error('is_active')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>User Location</label>
                                                <div id="map" style="height: 300px;"></div>
                                                <input type="hidden" id="location_latitudes" name="location_latitudes" value="{{ old('location_latitudes') }}">
                                                <input type="hidden" id="location_longitudes" name="location_longitudes" value="{{ old('location_longitudes') }}">
                                                @error('location_latitudes')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Assign Roles</label>
                                                <div class="d-flex flex-wrap">
                                                    @foreach($roles as $role)
                                                        <div class="custom-control custom-checkbox mr-4 mb-2">
                                                            <input class="custom-control-input" type="checkbox" id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}">
                                                            <label for="role_{{ $role->id }}" class="custom-control-label">{{ $role->display_name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('roles')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Create User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-default">
                                <i class="fas fa-times-circle mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBtF4Mz-vpzJFGSuOj1o5krujUu-MZuW0k&libraries=places"></script>
    <script>
        $(function() {
            // Preview uploaded image
            $("#profile-image").change(function() {
                readURL(this);
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Country-City dependent dropdown
            $('#country_id').on('change', function() {
                var countryId = $(this).val();

                // Determine if we should use English (by checking if country name is in English)
                var useEnglish = $('#country_id option:selected').text().match(/[a-zA-Z]/) ? true : false;

                if (countryId) {
                    $.ajax({
                        url: '/form-cities/' + countryId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Clear the select options first
                            $('#city_id').empty().append('<option value="">Select City</option>');

                            if (data && data.length > 0) {
                                // Add cities to dropdown
                                $.each(data, function(key, city) {
                                    // Try to get the appropriate name
                                    let cityName;

                                    try {
                                        // If city.name is a string representation of JSON
                                        if (typeof city.name === 'string' && city.name.indexOf('{') === 0) {
                                            const nameObj = JSON.parse(city.name);
                                            // Use English or Arabic based on country language
                                            cityName = useEnglish ? (nameObj.en || nameObj.ar) : (nameObj.ar || nameObj.en);
                                        } else if (typeof city.name === 'object') {
                                            // If city.name is already an object
                                            cityName = useEnglish ? (city.name.en || city.name.ar) : (city.name.ar || city.name.en);
                                        } else {
                                            // Just use the name as is
                                            cityName = city.name;
                                        }
                                    } catch (e) {
                                        cityName = city.name;
                                    }

                                    // Add the option
                                    $('#city_id').append('<option value="' + city.id + '">' + cityName + '</option>');
                                });

                                // Select the first city
                                if ($('#city_id option').length > 1) {
                                    $('#city_id option:eq(1)').prop('selected', true);
                                }
                            }

                            // Properly refresh Select2
                            $('#city_id').trigger('change');
                        },
                        error: function() {
                            $('#city_id').empty().append('<option value="">Error loading cities</option>');
                        },
                        complete: function() {
                            // Make sure Select2 is properly refreshed
                            if ($.fn.select2) {
                                $('#city_id').select2('destroy');
                                $('#city_id').select2({
                                    theme: 'bootstrap4',
                                    placeholder: 'Select City'
                                });
                            }
                        }
                    });
                } else {
                    $('#city_id').empty().append('<option value="">Select City</option>');
                    if ($.fn.select2) {
                        $('#city_id').select2('destroy');
                        $('#city_id').select2({
                            theme: 'bootstrap4',
                            placeholder: 'Select City'
                        });
                    }
                }
            });

            // Initialize Google Maps with error handling
            function initMap() {
                try {
                    var defaultLocation = {lat: 24.774265, lng: 46.738586}; // Default to Riyadh
                    var userLocation = defaultLocation;

                    // Check if we have stored coordinates (for edit page)
                    var storedLat = $('#location_latitudes').val();
                    var storedLng = $('#location_longitudes').val();

                    if (storedLat && storedLng && !isNaN(storedLat) && !isNaN(storedLng)) {
                        userLocation = {
                            lat: parseFloat(storedLat),
                            lng: parseFloat(storedLng)
                        };
                    }

                    var map = new google.maps.Map(document.getElementById('map'), {
                        center: userLocation,
                        zoom: 12
                    });

                    var marker = new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        draggable: true
                    });

                    google.maps.event.addListener(marker, 'dragend', function() {
                        var position = marker.getPosition();
                        $('#location_latitudes').val(position.lat());
                        $('#location_longitudes').val(position.lng());
                    });
                } catch (error) {
                    console.error("Google Maps initialization error:", error);
                    document.getElementById('map').innerHTML = '<div class="alert alert-warning">Unable to load map. Please check your internet connection.</div>';
                }
            }

            // Call initMap when the page has loaded
            if (typeof google !== 'undefined' && google.maps && document.getElementById('map')) {
                initMap();
            } else if (document.getElementById('map')) {
                document.getElementById('map').innerHTML = '<div class="alert alert-warning">Google Maps could not be loaded. Please check your API key.</div>';
            }
        });
    </script>
@stop
