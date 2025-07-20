@extends('adminlte::page')

@section('title', 'Edit Level')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Level</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('levels.index') }}">Levels</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                                <i class="fas fa-edit mr-2"></i>
                                Edit Level: {{ $level->getLocalizedName('ar') }} / {{ $level->getLocalizedName('en') }}
                            </h3>
                        </div>
                        <form action="{{ route('levels.update', $level->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <!-- Arabic Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name.ar') is-invalid @enderror" 
                                                   id="name_ar" name="name[ar]" 
                                                   value="{{ old('name.ar', $level->name['ar'] ?? '') }}" 
                                                   placeholder="اسم المستوى" required>
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
                                                   id="name_en" name="name[en]" 
                                                   value="{{ old('name.en', $level->name['en'] ?? '') }}" 
                                                   placeholder="Level Name" required>
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
                                                      placeholder="وصف المستوى">{{ old('description.ar', $level->description['ar'] ?? '') }}</textarea>
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
                                                      placeholder="Level description">{{ old('description.en', $level->description['en'] ?? '') }}</textarea>
                                            @error('description.en')
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
                                                   id="icon" name="icon" 
                                                   value="{{ old('icon', $level->icon) }}" 
                                                   placeholder="fas fa-star">
                                            <small class="form-text text-muted">FontAwesome icon class (e.g., fas fa-star)</small>
                                            @error('icon')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Color -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="color">Color <span class="text-danger">*</span></label>
                                            <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                                   id="color" name="color" 
                                                   value="{{ old('color', $level->color) }}" required>
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
                                                   id="display_order" name="display_order" 
                                                   value="{{ old('display_order', $level->display_order) }}" 
                                                   min="1" max="100">
                                            @error('display_order')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Points Per Day -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="points_per_day">Points Per Day</label>
                                            <input type="number" class="form-control @error('points_per_day') is-invalid @enderror" 
                                                   id="points_per_day" name="points_per_day" 
                                                   value="{{ old('points_per_day', $level->points_per_day) }}" 
                                                   min="0" max="1000">
                                            <small class="form-text text-muted">Cost in points per day for this level</small>
                                            @error('points_per_day')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- View Boost Percentage -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="view_boost_percentage">View Boost (%)</label>
                                            <input type="number" class="form-control @error('view_boost_percentage') is-invalid @enderror" 
                                                   id="view_boost_percentage" name="view_boost_percentage" 
                                                   value="{{ old('view_boost_percentage', $level->view_boost_percentage) }}" 
                                                   min="0" max="1000">
                                            <small class="form-text text-muted">Percentage increase in views for posts with this level</small>
                                            @error('view_boost_percentage')
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
                                                      placeholder="مميزات المستوى (سطر واحد لكل مميزة)">{{ old('features.ar', implode("\n", $level->features['ar'] ?? [])) }}</textarea>
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
                                                      placeholder="Level features (one per line)">{{ old('features.en', implode("\n", $level->features['en'] ?? [])) }}</textarea>
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
                                                       name="is_active" value="1" 
                                                       {{ old('is_active', $level->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">Active</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Premium -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_premium" 
                                                       name="is_premium" value="1" 
                                                       {{ old('is_premium', $level->is_premium) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_premium">Premium Level</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('levels.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Levels
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Update Level
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
                                Level Preview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="level-preview">
                                <div class="text-center mb-3">
                                    <div class="level-badge mx-auto" id="preview-badge" style="background-color: {{ $level->color }};">
                                        <i class="{{ $level->icon ?? 'fas fa-star' }}" id="preview-icon"></i>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h5 id="preview-name">{{ $level->getLocalizedName('ar') }} / {{ $level->getLocalizedName('en') }}</h5>
                                    <p class="text-muted" id="preview-description">{{ $level->getLocalizedDescription('ar') }}</p>
                                    <div class="badge badge-info" id="preview-points">{{ $level->points_per_day }} pts/day</div>
                                    <div class="badge badge-success" id="preview-boost">+{{ $level->view_boost_percentage }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Level Statistics -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Level Statistics
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Posts</span>
                                            <span class="info-box-number">{{ $level->servicePosts()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Views</span>
                                            <span class="info-box-number">{{ $level->servicePosts()->sum('view_count') }}</span>
                                        </div>
                                    </div>
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
.level-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-bottom: 10px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update preview when form fields change
    $('#name_ar, #name_en').on('input', function() {
        var arName = $('#name_ar').val() || 'اسم المستوى';
        var enName = $('#name_en').val() || 'Level Name';
        $('#preview-name').text(arName + ' / ' + enName);
    });

    $('#description_ar, #description_en').on('input', function() {
        var arDesc = $('#description_ar').val() || 'وصف المستوى';
        $('#preview-description').text(arDesc);
    });

    $('#icon').on('input', function() {
        var icon = $(this).val() || 'fas fa-star';
        $('#preview-icon').attr('class', icon);
    });

    $('#color').on('input', function() {
        var color = $(this).val();
        $('#preview-badge').css('background-color', color);
    });

    $('#points_per_day').on('input', function() {
        var points = $(this).val() || 0;
        $('#preview-points').text(points + ' pts/day');
    });

    $('#view_boost_percentage').on('input', function() {
        var boost = $(this).val() || 0;
        $('#preview-boost').text('+' + boost + '%');
    });
});
</script>
@endpush
@endsection 