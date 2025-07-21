@extends('adminlte::page')
@section('title', "Edit Category")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit text-primary mr-2"></i> Edit Category</h1>
        <div>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Categories
            </a>
            <a href="{{ route('categories.show', $category->id) }}" class="btn btn-info">
                <i class="fas fa-eye mr-1"></i> View
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
                            <i class="fas fa-edit text-warning mr-2"></i>
                            Edit Category: <span class="text-primary">{{ $category->field</span>
                        </h5>
                    </div>

                    <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">{{ __('categories\edit._times_') }}</span>
                                    </button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">{{ __('categories\edit._times_') }}</span>
                                    </button>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_ar" class="font-weight-bold">
                                            <i class="fas fa-language mr-1"></i>
                                            Arabic Name <span class="text-danger">{{ __('categories\edit._') }}</span>
                                        </label>
                                        <input type="text" id="name_ar" name="name[ar]"
                                               class="form-control @error('name.ar') is-invalid @enderror"
                                               value="{{ old('name.ar', $category->name['ar'] ?? '') }}" required>
                                        @error('name.ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_en" class="font-weight-bold">
                                            <i class="fas fa-language mr-1"></i>
                                            English Name <span class="text-danger">{{ __('categories\edit._') }}</span>
                                        </label>
                                        <input type="text" id="name_en" name="name[en]"
                                               class="form-control @error('name.en') is-invalid @enderror"
                                               value="{{ old('name.en', $category->name['en'] ?? '') }}" required>
                                        @error('name.en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="font-weight-bold">
                                            <i class="fas fa-toggle-on mr-1"></i>
                                            Status
                                        </label>
                                        <select name="isSuspended" id="status" class="form-control @error('isSuspended') is-invalid @enderror">
                                            <option value="0" {{ old('isSuspended', $category->field</option>
                                            <option value="1" {{ old('isSuspended', $category->field</option>
                                        </select>
                                        @error('isSuspended')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="photo" class="font-weight-bold">
                                            <i class="fas fa-image mr-1"></i>
                                            Category Image
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('photo') is-invalid @enderror"
                                                   id="photo" name="photo" onchange="previewImage(this)">
                                            <label class="custom-file-label" for="photo">{{ __('categories\edit.choose_new_image_or_keep_existing_') }}</label>
                                            @error('photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            Recommended size: 256x256 pixels. Max file size: 2MB.
                                            Allowed formats: JPG, PNG, GIF
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('categories\edit.current_image') }}</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div id="image-preview-container" class="{{ $category->photos->count() > 0 ? '' : 'd-none' }}">
                                                @if($category->photos->count() > 0)
                                                    <img src="{{ asset($category->photos->first()->src) }}"
                                                         alt="{{ $category->name[app()->getLocale()] }}"
                                                         class="img-fluid img-thumbnail"
                                                         id="current-image" style="max-height: 200px;">
                                                @endif
                                                <img id="image-preview" class="img-fluid img-thumbnail d-none" style="max-height: 200px;">
                                            </div>
                                            @if($category->photos->count() == 0)
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    No image has been uploaded for this category
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-between">
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Categories
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Update Category
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
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize file input with file name display
                bsCustomFileInput.init();
            });

            function previewImage(input) {
                const currentImage = document.getElementById('current-image');
                const preview = document.getElementById('image-preview');
                const container = document.getElementById('image-preview-container');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        if (currentImage) {
                            currentImage.classList.add('d-none');
                        }
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        container.classList.remove('d-none');
                    }

                    reader.readAsDataURL(input.files[0]);
                } else {
                    if (currentImage) {
                        currentImage.classList.remove('d-none');
                        preview.classList.add('d-none');
                    } else {
                        container.classList.add('d-none');
                    }
                }
            }
        </script>
    @endpush
@endsection
