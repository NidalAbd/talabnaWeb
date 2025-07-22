@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    @include('partials.breadcrumbs')
    @include('partials.alert')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-edit text-warning mr-2"></i> Edit User</h1>
        <div>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-info mr-2">
                <i class="fas fa-eye mr-1"></i> View User
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Users
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Edit User Information</h3>
                    </div>

                    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center mb-4">
                                        <div class="img-preview mb-3">
                                            @if($user->photos && $user->photos->count() > 0)
                                                @php
                                                    $photo = $user->photos->first();
                                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img id="preview-image" src="{{ $imgSrc }}"
                                                     alt="{{ $user->name }}" class="profile-user-img img-fluid img-circle"
                                                     style="width: 150px; height: 150px; object-fit: cover;">
                                            @else
                                                <img id="preview-image" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}"
                                                     alt="User profile picture" class="profile-user-img img-fluid img-circle"
                                                     style="width: 150px; height: 150px; object-fit: cover;">
                                            @endif
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
                                            <option value="email" {{ $user->auth_type == 'email' ? 'selected' : '' }}>Email</option>
                                            <option value="google" {{ $user->auth_type == 'google' ? 'selected' : '' }}>Google</option>
                                        </select>
                                        @error('auth_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="data_saver_enabled"
                                                   name="data_saver_enabled" value="1" {{ $user->data_saver_enabled ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="data_saver_enabled">Data Saver Enabled</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name', $user->name) }}"
                                                       placeholder="Enter full name" required>
                                                @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="user_name">Username <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('user_name') is-invalid @enderror"
                                                       id="user_name" name="user_name" value="{{ old('user_name', $user->user_name) }}"
                                                       placeholder="Enter username" required>
                                                @error('user_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                       id="email" name="email" value="{{ old('email', $user->email) }}"
                                                       placeholder="Enter email address" required>
                                                @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password <small class="text-muted">(Leave empty to keep current password)</small></label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                       id="password" name="password" placeholder="Enter new password">
                                                @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password_confirmation">Confirm Password</label>
                                                <input type="password" class="form-control" id="password_confirmation"
                                                       name="password_confirmation" placeholder="Confirm new password">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="ذكر" {{ $user->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                                    <option value="انثى" {{ $user->gender == 'انثى' ? 'selected' : '' }}>انثى</option>
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
                                                        <option value="{{ $country->id }}"
                                                            {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>
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
                                                    @foreach ($cities as $city)
                                                        <option value="{{ $city->id }}"
                                                            {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('city_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_of_birth">Date of Birth</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                                       id="date_of_birth" name="date_of_birth"
                                                       value="{{ old('date_of_birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}">
                                                @error('date_of_birth')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phones">Phone Number</label>
                                                <input type="text" class="form-control @error('phones') is-invalid @enderror"
                                                       id="phones" name="phones" value="{{ old('phones', $user->phones) }}"
                                                       placeholder="Enter phone number">
                                                @error('phones')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="WatsNumber">WhatsApp Number</label>
                                                <input type="text" class="form-control @error('WatsNumber') is-invalid @enderror"
                                                       id="WatsNumber" name="WatsNumber" value="{{ old('WatsNumber', $user->WatsNumber) }}"
                                                       placeholder="Enter WhatsApp number">
                                                @error('WatsNumber')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_active">Account Status</label>
                                                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                                    <option value="active" {{ $user->is_active == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ $user->is_active == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="banned" {{ $user->is_active == 'banned' ? 'selected' : '' }}>Banned</option>
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
                                                <input type="hidden" id="location_latitudes" name="location_latitudes"
                                                       value="{{ old('location_latitudes', $user->location_latitudes) }}">
                                                <input type="hidden" id="location_longitudes" name="location_longitudes"
                                                       value="{{ old('location_longitudes', $user->location_longitudes) }}">
                                                <small class="form-text text-muted">Drag the marker to set the user's location</small>
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
                                                            <input class="custom-control-input" type="checkbox"
                                                                   id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                                                                {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i> Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-default">
                                <i class="fas fa-times-circle mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- User Points Management Card -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-coins mr-1"></i> Points Management
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Current Points Balance</span>
                                        <span class="info-box-number">{{ $user->pointsBalance }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('palservice_points.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <div class="form-group">
                                        <label for="point">Add Points</label>
                                        <div class="input-group">
                                            <input type="number" name="point" id="point" class="form-control" placeholder="Enter points amount">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-plus-circle mr-1"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .profile-user-img {
            border: 3px solid #adb5bd;
            margin: 0 auto;
            padding: 3px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <script>
        $(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            });

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
                if (countryId) {
                    $.ajax({
                        url: '/get-cities/' + countryId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#city_id').empty();
                            $('#city_id').append('<option value="">Select City</option>');
                            $.each(data, function(key, value) {
                                $('#city_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#city_id').empty();
                    $('#city_id').append('<option value="">Select City</option>');
                }
            });

            // Initialize Google Maps
            function initMap() {
                var defaultLocation = {lat: 24.774265, lng: 46.738586}; // Default to Riyadh

                @if($user->location_latitudes && $user->location_longitudes)
                var userLocation = {
                    lat: {{ $user->location_latitudes }},
                    lng: {{ $user->location_longitudes }}
                };
                @else
                var userLocation = defaultLocation;
                @endif

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
            }

            // Call initMap when the page has loaded
            if (document.getElementById('map')) {
                initMap();
            }
        });
    </script>
@stop
