@extends('adminlte::page')
@section('title', "Add New City")
@section('content_header')
    @include('partials.breadcrumbs')
    <h1><i class="fas fa-plus-circle text-success mr-2"></i> Add New City</h1>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-city text-primary mr-2"></i>
                            City Details
                        </h5>
                    </div>

                    <form action="{{ route('cities.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_en">City Name (English) <span class="text-danger">{{('cities\create._') }}</span></label>
                                        <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                               id="name_en" name="name_en" value="{{ old('name_en') }}" required>
                                        @error('name_en')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_ar">{{('cities\create.city_name_arabic_') }}</label>
                                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                               id="name_ar" name="name_ar" value="{{ old('name_ar') }}" dir="rtl">
                                        @error('name_ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="country_id">Country <span class="text-danger">{{('cities\create._') }}</span></label>
                                <select class="form-control @error('country_id') is-invalid @enderror"
                                        id="country_id" name="country_id" required>
                                    <option value="">{{('cities\create._select_country_') }}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->getTranslatedName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="image">{{('cities\create.city_image') }}</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                                           id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image">{{('cities\create.choose_file') }}</label>
                                    @error('image')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">{{('cities\create.recommended_size_200x120_pixels') }}</small>
                            </div>

                            <div class="form-group">
                                <div class="img-preview mt-3" style="display: none;">
                                    <img id="preview-image" src="#" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('cities.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save City
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Preview uploaded image
                $('#image').change(function() {
                    let file = this.files[0];
                    if (file) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            $('.img-preview').show();
                            $('#preview-image').attr('src', e.target.result);
                        }
                        reader.readAsDataURL(file);
                        $('.custom-file-label').text(file.name);
                    }
                });
            });
        </script>
    @endpush
@endsection







