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
                        <h3 class="card-title">{{('users\create.user_information') }}</h3>
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
                                                <label class="custom-file-label" for="profile-image">{{('users\create.choose_profile_image') }}</label>
                                            </div>
                                        </div>
                                        @error('profile_image')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="auth_type">{{('users\create.authentication_type') }}</label>
                                        <select class="form-control @error('auth_type') is-invalid @enderror" id="auth_type" name="auth_type">
                                            <option value="email" selected>{{('users\create.email') }}</option>
                                            <option value="google">{{('users\create.google') }}</option>
                                        </select>
                                        @error('auth_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="data_saver_enabled" name="data_saver_enabled" value="1">
                                            <label class="custom-control-label" for="data_saver_enabled">{{('users\create.data_saver_enabled') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Full Name <span class="text-danger">{{('users\create._') }}</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                                                @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="user_name">Username <span class="text-danger">{{('users\create._') }}</span></label>
                                                <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ old('user_name') }}" placeholder="Enter username" required>
                                                @error('user_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address <span class="text-danger">{{('users\create._') }}</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required>
                                                @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password <span class="text-danger">{{('users\create._') }}</span></label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter password" required>
                                                @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password_confirmation">Confirm Password <span class="text-danger">{{('users\create._') }}</span></label>
                                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="gender">{{('users\create.gender') }}</label>
                                                <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="ذكر" {{ old('gender') == 'ذكر' ? 'selected' : '' }}>{{('users\create._') }}</option>
                                                    <option value="انثى" {{ old('gender') == 'انثى' ? 'selected' : '' }}>{{('users\create._') }}</option>
                                                </select>
                                                @error('gender')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country_id">{{('users\create.country') }}</label>
                                                <select class="form-control select2 @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
                                                    <option value="">{{('users\create.select_country') }}</option>
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
                                                <label for="city_id">{{('users\create.city') }}</label>
                                                <select class="form-control select2 @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                                    <option value="">{{('users\create.select_city') }}</option>
                                                    <!-- Cities will be populated via AJAX based on country selection -->
                                                </select>
                                                @error('city_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_of_birth">{{('users\create.date_of_birth') }}</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                                @error('date_of_birth')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phones">{{('users\create.phone_number') }}</label>
                                                <input type="text" class="form-control @error('phones') is-invalid @enderror" id="phones" name="phones" value="{{ old('phones') }}" placeholder="Enter phone number">
                                                @error('phones')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="WatsNumber">{{('users\create.whatsapp_number') }}</label>
                                                <input type="text" class="form-control @error('WatsNumber') is-invalid @enderror" id="WatsNumber" name="WatsNumber" value="{{ old('WatsNumber') }}" placeholder="Enter WhatsApp number">
                                                @error('WatsNumber')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_active">{{('users\create.account_status') }}</label>
                                                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                                    <option value="active" selected>{{('users\create.active') }}</option>
                                                    <option value="inactive">{{('users\create.inactive') }}</option>
                                                    <option value="banned">{{('users\create.banned') }}</option>
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
                                                <label>{{('users\create.user_location') }}</label>
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
                                                <label>{{('users\create.assign_roles') }}</label>
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
                            $('#city_id').empty().append('<option value="">{{('users\create.select_city') }}</option>');
                            if (data.cities && data.cities.length > 0) {
                                data.cities.forEach(function(city) {
                                    $('#city_id').append('<option value="' + city.id + '">{{('users\create._cityname_') }}</option>');
                                });
                            } else {
                                $('#city_id').append('<option value="">{{('users\create.select_city') }}</option>');
                            }
                            $('#city_id').select2({
                                theme: 'bootstrap4',
                                placeholder: '{{('users\create.select_city') }}',
                                allowClear: true
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading cities:', error);
                            $('#city_id').empty().append('<option value="">{{('users\create.error_loading_cities') }}</option>');
                            $('#city_id').select2({
                                theme: 'bootstrap4',
                                placeholder: '{{('users\create.select_city') }}',
                                allowClear: true
                            });
                        }
                    });
                } else {
                    $('#city_id').empty().append('<option value="">{{('users\create.select_city') }}</option>');
                    $('#city_id').select2({
                        theme: 'bootstrap4',
                        placeholder: '{{('users\create.select_city') }}',
                        allowClear: true
                    });
                }
            });

            // Initialize Select2 for city dropdown
            $('#city_id').select2({
                theme: 'bootstrap4',
                placeholder: '{{('users\create.select_city') }}',
                allowClear: true
            });

            // Google Maps initialization
            var map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 20.5937, lng: 78.9629 }, // Default center for India
                zoom: 6
            });

            var geocoder = new google.maps.Geocoder();
            var marker = new google.maps.Marker({
                map: map,
                draggable: true,
                position: { lat: 20.5937, lng: 78.9629 } // Default position for India
            });

            google.maps.event.addListener(marker, 'dragend', function() {
                geocodeLatLng(marker.getPosition());
            });

            function geocodeLatLng(latLng) {
                var lat = latLng.lat();
                var lng = latLng.lng();
                $('#location_latitudes').val(lat);
                $('#location_longitudes').val(lng);
            }

            // Handle form submission
            $('form').on('submit', function(e) {
                var lat = $('#location_latitudes').val();
                var lng = $('#location_longitudes').val();

                if (lat === '' || lng === '') {
                    e.preventDefault();
                    alert('{{('users\create.please_select_a_location_on_the_map') }}');
                    return false;
                }
            });
        });
    </script>
@stop







