@extends('adminlte::page')
@section('title', 'Create New Subcategory')
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tag text-primary mr-2"></i> Subcategories Management</h1>
        <div>
            <a href="{{ route('subcategories.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Subcategory
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
                            Create New Subcategory
                        </h5>
                    </div>

                    <form action="{{ route('subcategories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">{{('sub_categories\create._times_') }}</span>
                                    </button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">{{('sub_categories\create._times_') }}</span>
                                    </button>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="form-group">
                                        <label for="categories_id" class="font-weight-bold">
                                            <i class="fas fa-th-large mr-1"></i>
                                            Parent Category <span class="text-danger">{{('sub_categories\create._') }}</span>
                                        </label>
                                        <select name="categories_id" id="categories_id"
                                                class="form-control @error('categories_id') is-invalid @enderror" required>
                                            <option value="">{{('sub_categories\create.select_parent_category') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('categories_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name[app()->getLocale()] ?? $category->name['en'] ?? 'Unknown' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('categories_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Select the parent category for this subcategory
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_ar" class="font-weight-bold">
                                            <i class="fas fa-language mr-1"></i>
                                            Arabic Name <span class="text-danger">{{('sub_categories\create._') }}</span>
                                        </label>
                                        <input type="text" id="name_ar" name="name[ar]"
                                               class="form-control @error('name.ar') is-invalid @enderror"
                                               value="{{ old('name.ar') }}" required>
                                        @error('name.ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_en" class="font-weight-bold">
                                            <i class="fas fa-language mr-1"></i>
                                            English Name <span class="text-danger">{{('sub_categories\create._') }}</span>
                                        </label>
                                        <input type="text" id="name_en" name="name[en]"
                                               class="form-control @error('name.en') is-invalid @enderror"
                                               value="{{ old('name.en') }}" required>
                                        @error('name.en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="photo" class="font-weight-bold">
                                            <i class="fas fa-image mr-1"></i>
                                            Subcategory Image <span class="text-danger">{{('sub_categories\create._') }}</span>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('photo') is-invalid @enderror"
                                                   id="photo" name="photo" required onchange="previewImage(this)">
                                            <label class="custom-file-label" for="photo">{{('sub_categories\create.choose_file_') }}</label>
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

                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div id="image-preview-container" class="text-center d-none mt-3">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">{{('sub_categories\create.image_preview') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <img id="image-preview" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-between">
                            <a href="{{ route('subcategories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Subcategories
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Create Subcategory
                            </button>
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
                const container = document.getElementById('image-preview-container');
                const preview = document.getElementById('image-preview');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.classList.remove('d-none');
                    }

                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.src = '';
                    container.classList.add('d-none');
                }
            }
        </script>
    @endpush
@endsection







