@extends('adminlte::page')

@section('title', 'Edit Premium Feature')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-edit mr-2"></i>
            Edit Premium Feature
        </h1>
        <a href="{{ route('admin.premium-features.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Features
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Premium Feature: {{ $feature->name['en'] ?? 'Unknown' }}
                    </h3>
                </div>
                <form action="{{ route('admin.premium-features.update', $feature->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Arabic Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name.ar') is-invalid @enderror" 
                                           id="name_ar" name="name[ar]" value="{{ old('name.ar', $feature->name['ar'] ?? '') }}" required>
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
                                           id="name_en" name="name[en]" value="{{ old('name.en', $feature->name['en'] ?? '') }}" required>
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
                                              id="description_ar" name="description[ar]" rows="3">{{ old('description.ar', $feature->description['ar'] ?? '') }}</textarea>
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
                                              id="description_en" name="description[en]" rows="3">{{ old('description.en', $feature->description['en'] ?? '') }}</textarea>
                                    @error('description.en')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Points Cost -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="points_cost">Points Cost <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('points_cost') is-invalid @enderror" 
                                           id="points_cost" name="points_cost" value="{{ old('points_cost', $feature->points_cost) }}" min="1" required>
                                    @error('points_cost')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Duration Days -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration_days">Duration (Days) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                           id="duration_days" name="duration_days" value="{{ old('duration_days', $feature->duration_days) }}" min="1" required>
                                    @error('duration_days')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="post_enhancement" {{ old('category', $feature->category) == 'post_enhancement' ? 'selected' : '' }}>Post Enhancement</option>
                                        <option value="user_benefit" {{ old('category', $feature->category) == 'user_benefit' ? 'selected' : '' }}>User Benefit</option>
                                        <option value="system_feature" {{ old('category', $feature->category) == 'system_feature' ? 'selected' : '' }}>System Feature</option>
                                        <option value="premium_access" {{ old('category', $feature->category) == 'premium_access' ? 'selected' : '' }}>Premium Access</option>
                                        <option value="analytics" {{ old('category', $feature->category) == 'analytics' ? 'selected' : '' }}>Analytics</option>
                                    </select>
                                    @error('category')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Icon -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="icon">Icon (FontAwesome Class)</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                           id="icon" name="icon" value="{{ old('icon', $feature->icon) }}" 
                                           placeholder="fas fa-crown">
                                    <small class="form-text text-muted">Enter FontAwesome icon class (e.g., fas fa-crown)</small>
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
                                           id="color" name="color" value="{{ old('color', $feature->color ?? '#ffc107') }}">
                                    @error('color')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $feature->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" value="1" 
                                               {{ old('is_popular', $feature->is_popular) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_popular">Popular Feature</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feature Preview -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-eye mr-2"></i>
                                            Feature Preview
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="feature-preview">
                                            <div class="mb-3">
                                                <i class="{{ $feature->icon ?? 'fas fa-crown' }}" 
                                                   style="font-size: 48px; color: {{ $feature->color ?? '#ffc107' }};"></i>
                                            </div>
                                            <h4>{{ $feature->name['en'] ?? 'Feature Name' }}</h4>
                                            <p class="text-muted">{{ $feature->description['en'] ?? 'Feature description' }}</p>
                                            <div class="badge badge-primary mr-2">{{ number_format($feature->points_cost) }} pts</div>
                                            <div class="badge badge-info">{{ $feature->duration_days }} days</div>
                                            @if($feature->is_popular)
                                                <div class="badge badge-warning ml-2">Popular</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.premium-features.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Update Feature
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #007bff;
    border-color: #007bff;
}
.feature-preview {
    padding: 20px;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Live preview updates
    $('#name_en').on('input', function() {
        $('.feature-preview h4').text($(this).val() || 'Feature Name');
    });

    $('#description_en').on('input', function() {
        $('.feature-preview p').text($(this).val() || 'Feature description');
    });

    $('#points_cost').on('input', function() {
        $('.feature-preview .badge-primary').text($(this).val() + ' pts');
    });

    $('#duration_days').on('input', function() {
        $('.feature-preview .badge-info').text($(this).val() + ' days');
    });

    $('#icon').on('input', function() {
        const iconClass = $(this).val() || 'fas fa-crown';
        $('.feature-preview i').attr('class', iconClass);
    });

    $('#color').on('input', function() {
        const color = $(this).val();
        $('.feature-preview i').css('color', color);
    });
});
</script>
@stop 