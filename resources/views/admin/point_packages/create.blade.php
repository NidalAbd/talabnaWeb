@extends('adminlte::page')

@section('title', 'Create Point Package')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Point Package</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.point_packages.index') }}">Point Packages</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus mr-2"></i>
                                Package Information
                            </h3>
                        </div>
                        <form action="{{ route('admin.point_packages.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <!-- Arabic Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name.ar') is-invalid @enderror" 
                                                   id="name_ar" name="name[ar]" value="{{ old('name.ar') }}" 
                                                   placeholder="اسم الباقة" required>
                                            @error('name.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name.en') is-invalid @enderror" 
                                                   id="name_en" name="name[en]" value="{{ old('name.en') }}" 
                                                   placeholder="Package Name" required>
                                            @error('name.en')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Arabic Description -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_ar">Description (Arabic)</label>
                                            <textarea class="form-control @error('description.ar') is-invalid @enderror" 
                                                      id="description_ar" name="description[ar]" rows="3" 
                                                      placeholder="وصف الباقة">{{ old('description.ar') }}</textarea>
                                            @error('description.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- English Description -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_en">Description (English)</label>
                                            <textarea class="form-control @error('description.en') is-invalid @enderror" 
                                                      id="description_en" name="description[en]" rows="3" 
                                                      placeholder="Package description">{{ old('description.en') }}</textarea>
                                            @error('description.en')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Points -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="points">Points <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('points') is-invalid @enderror" 
                                                   id="points" name="points" value="{{ old('points', 100) }}" 
                                                   min="1" max="1000000" required>
                                            <small class="form-text text-muted">Number of points in this package</small>
                                            @error('points')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="price">Price <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                                   id="price" name="price" value="{{ old('price', 10) }}" 
                                                   min="0" max="10000" step="0.01" required>
                                            <small class="form-text text-muted">Price in the selected currency</small>
                                            @error('price')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Currency -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="currency">Currency <span class="text-danger">*</span></label>
                                            <select class="form-control @error('currency') is-invalid @enderror" 
                                                    id="currency" name="currency" required>
                                                <option value="SAR" {{ old('currency', 'SAR') == 'SAR' ? 'selected' : '' }}>SAR</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                                            </select>
                                            @error('currency')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Duration Days -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="duration_days">Duration (Days) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                                   id="duration_days" name="duration_days" value="{{ old('duration_days', 30) }}" 
                                                   min="1" max="365" required>
                                            <small class="form-text text-muted">How long the points are valid</small>
                                            @error('duration_days')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount Percentage -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="discount_percentage">Discount (%)</label>
                                            <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                                   id="discount_percentage" name="discount_percentage" 
                                                   value="{{ old('discount_percentage', 0) }}" min="0" max="100">
                                            <small class="form-text text-muted">Discount percentage (0-100)</small>
                                            @error('discount_percentage')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Max Purchases -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_purchases">Max Purchases</label>
                                            <input type="number" class="form-control @error('max_purchases') is-invalid @enderror" 
                                                   id="max_purchases" name="max_purchases" 
                                                   value="{{ old('max_purchases', 0) }}" min="0" max="1000">
                                            <small class="form-text text-muted">0 = unlimited</small>
                                            @error('max_purchases')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Icon -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="icon">Icon</label>
                                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-gift') }}" 
                                                   placeholder="fas fa-gift">
                                            <small class="form-text text-muted">FontAwesome icon class</small>
                                            @error('icon')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Color -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="color">Color</label>
                                            <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                                   id="color" name="color" value="{{ old('color', '#007bff') }}">
                                            @error('color')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Display Order -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="display_order">Display Order</label>
                                            <input type="number" class="form-control @error('display_order') is-invalid @enderror" 
                                                   id="display_order" name="display_order" value="{{ old('display_order', 1) }}" 
                                                   min="1" max="100">
                                            @error('display_order')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Features -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="features_ar">Features (Arabic)</label>
                                            <textarea class="form-control @error('features.ar') is-invalid @enderror" 
                                                      id="features_ar" name="features[ar]" rows="3" 
                                                      placeholder="مميزات الباقة (سطر واحد لكل مميزة)">{{ old('features.ar') }}</textarea>
                                            <small class="form-text text-muted">One feature per line</small>
                                            @error('features.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Features English -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="features_en">Features (English)</label>
                                            <textarea class="form-control @error('features.en') is-invalid @enderror" 
                                                      id="features_en" name="features[en]" rows="3" 
                                                      placeholder="Package features (one per line)">{{ old('features.en') }}</textarea>
                                            <small class="form-text text-muted">One feature per line</small>
                                            @error('features.en')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Status -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">Active</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Popular -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_popular" 
                                                       name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_popular">Popular Package</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Featured -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_featured" 
                                                       name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_featured">Featured</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.point_packages.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Packages
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Create Package
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Package Preview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="package-preview">
                                <div class="text-center mb-3">
                                    <div class="package-badge mx-auto" id="preview-badge">
                                        <i class="fas fa-gift" id="preview-icon"></i>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h5 id="preview-name">Package Name</h5>
                                    <p class="text-muted" id="preview-description">Package description</p>
                                    <div class="badge badge-primary" id="preview-points">100 pts</div>
                                    <div class="badge badge-success" id="preview-price">10 SAR</div>
                                    <div class="badge badge-info" id="preview-duration">30 days</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
.package-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-bottom: 10px;
    background-color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview when form fields change
    $('#name_ar, #name_en').on('input', function() {
        var arName = $('#name_ar').val() || 'اسم الباقة';
        var enName = $('#name_en').val() || 'Package Name';
        $('#preview-name').text(arName + ' / ' + enName);
    });

    $('#description_ar, #description_en').on('input', function() {
        var arDesc = $('#description_ar').val() || 'وصف الباقة';
        $('#preview-description').text(arDesc);
    });

    $('#icon').on('input', function() {
        var icon = $(this).val() || 'fas fa-gift';
        $('#preview-icon').attr('class', icon);
    });

    $('#color').on('input', function() {
        var color = $(this).val();
        $('#preview-badge').css('background-color', color);
    });

    $('#points').on('input', function() {
        var points = $(this).val() || 0;
        $('#preview-points').text(points + ' pts');
    });

    $('#price, #currency').on('input', function() {
        var price = $('#price').val() || 0;
        var currency = $('#currency').val() || 'SAR';
        $('#preview-price').text(price + ' ' + currency);
    });

    $('#duration_days').on('input', function() {
        var days = $(this).val() || 0;
        $('#preview-duration').text(days + ' days');
    });
});
</script>
@endpush
@endsection 