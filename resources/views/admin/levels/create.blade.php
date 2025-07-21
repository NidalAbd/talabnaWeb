@extends('adminlte::page')

@section('title', 'Create New Level')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-layer-group mr-2"></i>
                Create New Level
            </h1>
            <p class="text-muted mt-1">{{ __('admin\levels\create.add_a_new_user_level_with_custom_feature') }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin\levels\create.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('levels.index') }}">{{ __('admin\levels\create.levels') }}</a></li>
                <li class="breadcrumb-item active">{{ __('admin\levels\create.create') }}</li>
            </ol>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ \App\Models\Level::count() }}</h3>
                            <p>{{ __('admin\levels\create.total_levels') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ \App\Models\Level::where('is_active', true)->{{ __('admin\levels\create.count_') }}</h3>
                            <p>{{ __('admin\levels\create.active_levels') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ \App\Models\Level::where('is_premium', true)->{{ __('admin\levels\create.count_') }}</h3>
                            <p>{{ __('admin\levels\create.premium_levels') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ \App\Models\ServicePost::count() }}</h3>
                            <p>{{ __('admin\levels\create.service_posts') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus mr-2"></i>
                                Level Information
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <form action="{{ route('levels.store') }}" method="POST" id="levelForm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <!-- Arabic Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">Name (Arabic) <span class="text-danger">{{ __('admin\levels\create._') }}</span></label>
                                            <input type="text" class="form-control @error('name.ar') is-invalid @enderror" 
                                                   id="name_ar" name="name[ar]" value="{{ old('name.ar') }}" 
                                                   placeholder="اسم المستوى" required>
                                            @error('name.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- English Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">Name (English) <span class="text-danger">{{ __('admin\levels\create._') }}</span></label>
                                            <input type="text" class="form-control @error('name.en') is-invalid @enderror" 
                                                   id="name_en" name="name[en]" value="{{ old('name.en') }}" 
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
                                            <label for="description_ar">{{ __('admin\levels\create.description_arabic_') }}</label>
                                            <textarea class="form-control @error('description.ar') is-invalid @enderror" 
                                                      id="description_ar" name="description[ar]" rows="3" 
                                                      placeholder="وصف المستوى">{{ old('description.ar') }}</textarea>
                                            @error('description.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- English Description -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_en">{{ __('admin\levels\create.description_english_') }}</label>
                                            <textarea class="form-control @error('description.en') is-invalid @enderror" 
                                                      id="description_en" name="description[en]" rows="3" 
                                                      placeholder="Level description">{{ old('description.en') }}</textarea>
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
                                            <label for="icon">{{ __('admin\levels\create.icon') }}</label>
                                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                                   id="icon" name="icon" value="{{ old('icon') }}" 
                                                   placeholder="fas fa-star">
                                            <small class="form-text text-muted">{{ __('admin\levels\create.fontawesome_icon_class_e_g_fas_fa_sta') }}</small>
                                            @error('icon')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Color -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="color">Color <span class="text-danger">{{ __('admin\levels\create._') }}</span></label>
                                            <input type="color" class="form-control @error('color') is-invalid @enderror" 
                                                   id="color" name="color" value="{{ old('color', '#6c757d') }}" required>
                                            @error('color')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Display Order -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="display_order">{{ __('admin\levels\create.display_order') }}</label>
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
                                    <!-- Points Per Day -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="points_per_day">{{ __('admin\levels\create.points_per_day') }}</label>
                                            <input type="number" class="form-control @error('points_per_day') is-invalid @enderror" 
                                                   id="points_per_day" name="points_per_day" value="{{ old('points_per_day', 0) }}" 
                                                   min="0" max="1000">
                                            <small class="form-text text-muted">{{ __('admin\levels\create.cost_in_points_per_day_for_this_level') }}</small>
                                            @error('points_per_day')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- View Boost Percentage -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="view_boost_percentage">{{ __('admin\levels\create.view_boost_') }}</label>
                                            <input type="number" class="form-control @error('view_boost_percentage') is-invalid @enderror" 
                                                   id="view_boost_percentage" name="view_boost_percentage" 
                                                   value="{{ old('view_boost_percentage', 0) }}" min="0" max="1000">
                                            <small class="form-text text-muted">{{ __('admin\levels\create.percentage_increase_in_views_for_posts_w') }}</small>
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
                                            <label for="features_ar">{{ __('admin\levels\create.features_arabic_') }}</label>
                                            <textarea class="form-control @error('features.ar') is-invalid @enderror" 
                                                      id="features_ar" name="features[ar]" rows="3" 
                                                      placeholder="مميزات المستوى (سطر واحد لكل مميزة)">{{ old('features.ar') }}</textarea>
                                            <small class="form-text text-muted">{{ __('admin\levels\create.one_feature_per_line') }}</small>
                                            @error('features.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Features English -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="features_en">{{ __('admin\levels\create.features_english_') }}</label>
                                            <textarea class="form-control @error('features.en') is-invalid @enderror" 
                                                      id="features_en" name="features[en]" rows="3" 
                                                      placeholder="Level features (one per line)">{{ old('features.en') }}</textarea>
                                            <small class="form-text text-muted">{{ __('admin\levels\create.one_feature_per_line') }}</small>
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
                                                <label class="custom-control-label" for="is_active">{{ __('admin\levels\create.active') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Premium -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_premium" 
                                                       name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_premium">{{ __('admin\levels\create.premium_level') }}</label>
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
                                    <div>
                                        <button type="button" class="btn btn-info mr-2" id="previewBtn">
                                            <i class="fas fa-eye mr-1"></i> Preview
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save mr-1"></i> Create Level
                                        </button>
                                    </div>
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
                                    <div class="level-badge mx-auto" id="preview-badge">
                                        <i class="fas fa-star" id="preview-icon"></i>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h5 id="preview-name">{{ __('admin\levels\create.level_name') }}</h5>
                                    <p class="text-muted" id="preview-description">{{ __('admin\levels\create.level_description') }}</p>
                                    <div class="badge badge-info" id="preview-points">{{ __('admin\levels\create.0_pts_day') }}</div>
                                    <div class="badge badge-success" id="preview-boost">{{ __('admin\levels\create._0_') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt mr-2"></i>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="btn-group-vertical w-100">
                                    <button type="button" class="btn btn-outline-primary mb-2" onclick="fillBasicLevel()">
                                        <i class="fas fa-star mr-1"></i> Basic Level
                                    </button>
                                    <button type="button" class="btn btn-outline-success mb-2" onclick="fillPremiumLevel()">
                                        <i class="fas fa-crown mr-1"></i> Premium Level
                                    </button>
                                    <button type="button" class="btn btn-outline-warning mb-2" onclick="fillAdvancedLevel()">
                                        <i class="fas fa-rocket mr-1"></i> Advanced Level
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="clearForm()">
                                        <i class="fas fa-eraser mr-1"></i> Clear Form
                                    </button>
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
    background-color: #6c757d;
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

    // Form submission with AJAX
    $('#levelForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this)[0];
        var formData = new FormData(form);
        
        // Manually set JSON fields
        formData.set('name', JSON.stringify({
            ar: $('#name_ar').val(),
            en: $('#name_en').val()
        }));
        formData.set('description', JSON.stringify({
            ar: $('#description_ar').val(),
            en: $('#description_en').val()
        }));
        formData.set('features', JSON.stringify({
            ar: $('#features_ar').val(),
            en: $('#features_en').val()
        }));
        
        var submitBtn = $('#submitBtn');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Level created successfully!',
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location.href = '{{ route("levels.index") }}';
                });
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMessage = 'Please fix the following errors:\n';
                
                for (var field in errors) {
                    errorMessage += '- ' + errors[field][0] + '\n';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Preview button
    $('#previewBtn').on('click', function() {
        Swal.fire({
            title: 'Level Preview',
            html: `
                <div class="text-center">
                    <div class="level-badge mx-auto mb-3" style="background-color: ${$('#color').val() || '#6c757d'};">
                        <i class="${$('#icon').val() || 'fas fa-star'}" style="color: white; font-size: 24px;"></i>
                    </div>
                    <h5>{{ __('admin\levels\create._name_ar_val_') }}</h5>
                    <p class="text-muted">{{ __('admin\levels\create._description_ar_val_') }}</p>
                    <div class="badge badge-info mr-2">{{ __('admin\levels\create._points_per_day_val_0_pts_d') }}</div>
                    <div class="badge badge-success">{{ __('admin\levels\create._view_boost_percentage_val_') }}</div>
                </div>
            `,
            confirmButtonText: 'OK'
        });
    });
});

// Quick action functions
function fillBasicLevel() {
    $('#name_ar').val('مبتدئ');
    $('#name_en').val('Basic');
    $('#description_ar').val('مستوى للمستخدمين الجدد');
    $('#description_en').val('Level for new users');
    $('#icon').val('fas fa-star');
    $('#color').val('#6c757d');
    $('#points_per_day').val(0);
    $('#view_boost_percentage').val(0);
    $('#display_order').val(1);
    $('#is_active').prop('checked', true);
    $('#is_premium').prop('checked', false);
}

function fillPremiumLevel() {
    $('#name_ar').val('مميز');
    $('#name_en').val('Premium');
    $('#description_ar').val('مستوى مميز مع مزايا إضافية');
    $('#description_en').val('Premium level with extra features');
    $('#icon').val('fas fa-crown');
    $('#color').val('#ffc107');
    $('#points_per_day').val(10);
    $('#view_boost_percentage').val(25);
    $('#display_order').val(2);
    $('#is_active').prop('checked', true);
    $('#is_premium').prop('checked', true);
}

function fillAdvancedLevel() {
    $('#name_ar').val('متقدم');
    $('#name_en').val('Advanced');
    $('#description_ar').val('مستوى متقدم للمستخدمين المحترفين');
    $('#description_en').val('Advanced level for professional users');
    $('#icon').val('fas fa-rocket');
    $('#color').val('#dc3545');
    $('#points_per_day').val(25);
    $('#view_boost_percentage').val(50);
    $('#display_order').val(3);
    $('#is_active').prop('checked', true);
    $('#is_premium').prop('checked', true);
}

function clearForm() {
    Swal.fire({
        title: 'Clear Form?',
        text: 'Are you sure you want to clear all form fields?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#levelForm')[0].reset();
            $('#preview-name').text('Level Name');
            $('#preview-description').text('Level description');
            $('#preview-icon').attr('class', 'fas fa-star');
            $('#preview-badge').css('background-color', '#6c757d');
            $('#preview-points').text('0 pts/day');
            $('#preview-boost').text('+0%');
        }
    });
}
</script>
@endpush
@endsection 