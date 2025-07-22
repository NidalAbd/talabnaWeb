@extends('adminlte::page')

@section('title', 'Create Premium Feature')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-crown mr-2"></i>
            Create Premium Feature
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
                        <i class="fas fa-plus mr-2"></i>
                        Add New Premium Feature
                    </h3>
                </div>
                <form action="{{ route('admin.premium-features.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Arabic Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar">Name (Arabic) <span class="text-danger">{{('admin\premium_features\create._') }}</span></label>
                                    <input type="text" class="form-control @error('name.ar') is-invalid @enderror" 
                                           id="name_ar" name="name[ar]" value="{{ old('name.ar') }}" required>
                                    @error('name.ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- English Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en">Name (English) <span class="text-danger">{{('admin\premium_features\create._') }}</span></label>
                                    <input type="text" class="form-control @error('name.en') is-invalid @enderror" 
                                           id="name_en" name="name[en]" value="{{ old('name.en') }}" required>
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
                                    <label for="description_ar">{{('admin\premium_features\create.description_arabic_') }}</label>
                                    <textarea class="form-control @error('description.ar') is-invalid @enderror" 
                                              id="description_ar" name="description[ar]" rows="3">{{ old('description.ar') }}</textarea>
                                    @error('description.ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- English Description -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_en">{{('admin\premium_features\create.description_english_') }}</label>
                                    <textarea class="form-control @error('description.en') is-invalid @enderror" 
                                              id="description_en" name="description[en]" rows="3">{{ old('description.en') }}</textarea>
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
                                    <label for="points_cost">Points Cost <span class="text-danger">{{('admin\premium_features\create._') }}</span></label>
                                    <input type="number" class="form-control @error('points_cost') is-invalid @enderror" 
                                           id="points_cost" name="points_cost" value="{{ old('points_cost') }}" min="1" required>
                                    @error('points_cost')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Duration Days -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration_days">Duration (Days) <span class="text-danger">{{('admin\premium_features\create._') }}</span></label>
                                    <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                           id="duration_days" name="duration_days" value="{{ old('duration_days', 30) }}" min="1" required>
                                    @error('duration_days')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">{{('admin\premium_features\create._') }}</span></label>
                                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">{{('admin\premium_features\create.select_category') }}</option>
                                        <option value="post_enhancement" {{ old('category') == 'post_enhancement' ? 'selected' : '' }}>{{('admin\premium_features\create.post_enhancement') }}</option>
                                        <option value="user_benefit" {{ old('category') == 'user_benefit' ? 'selected' : '' }}>{{('admin\premium_features\create.user_benefit') }}</option>
                                        <option value="system_feature" {{ old('category') == 'system_feature' ? 'selected' : '' }}>{{('admin\premium_features\create.system_feature') }}</option>
                                        <option value="premium_access" {{ old('category') == 'premium_access' ? 'selected' : '' }}>{{('admin\premium_features\create.premium_access') }}</option>
                                        <option value="analytics" {{ old('category') == 'analytics' ? 'selected' : '' }}>{{('admin\premium_features\create.analytics') }}</option>
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
                                    <label for="icon">{{('admin\premium_features\create.icon_fontawesome_class_') }}</label>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                           id="icon" name="icon" value="{{ old('icon', 'fas fa-crown') }}" 
                                           placeholder="fas fa-crown">
                                    <small class="form-text text-muted">{{('admin\premium_features\create.enter_fontawesome_icon_class_e_g_fas_') }}</small>
                                    @error('icon')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Color -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="color">{{('admin\premium_features\create.color') }}</label>
                                    <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color', '#ffc107') }}">
                                    @error('color')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{('admin\premium_features\create.status') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">{{('admin\premium_features\create.active') }}</label>
                                    </div>
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" value="1" 
                                               {{ old('is_popular') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_popular">{{('admin\premium_features\create.popular_feature') }}</label>
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
                                <i class="fas fa-save mr-1"></i> Create Feature
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
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Preview icon
    $('#icon').on('input', function() {
        const iconClass = $(this).val();
        if (iconClass) {
            $('#icon-preview').html(`<i class="${iconClass}" style="font-size: 24px; color: ${$('#color').val() || '#ffc107'};"></i>`);
        }
    });

    // Preview color
    $('#color').on('input', function() {
        const color = $(this).val();
        $('#icon-preview i').css('color', color);
    });
});
</script>
@stop 






