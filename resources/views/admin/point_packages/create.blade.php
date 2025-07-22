@extends('adminlte::page')

@section('title', 'Create Point Package')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-gift mr-2"></i>
                Create New Point Package
            </h1>
            <p class="text-muted mt-1">{{('admin\point_packages\create.add_a_new_point_package_with_pricing_and') }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{('admin\point_packages\create.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.point_packages.index') }}">{{('admin\point_packages\create.point_packages') }}</a></li>
                <li class="breadcrumb-item active">{{('admin\point_packages\create.create') }}</li>
            </ol>
        </div>
    </div>
    <!-- Statistics Cards: Remove the card for PointPurchaseRequest -->
    <div class="row mb-4">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\PointPackage::count() }}</h3>
                    <p>{{('admin\point_packages\create.total_packages') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-gift"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\PointPackage::where('is_active', true)-> __('admin\point_packages\create.count_') }}</h3>
                    <p>{{('admin\point_packages\create.active_packages') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\PointPackage::where('is_popular', true)-> __('admin\point_packages\create.count_') }}</h3>
                    <p>{{('admin\point_packages\create.popular_packages') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
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
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <form action="{{ route('admin.point_packages.store') }}" method="POST" id="packageForm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <!-- Arabic Name -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">Name (Arabic) <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
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
                                            <label for="name_en">Name (English) <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
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
                                            <label for="description_ar">{{('admin\point_packages\create.description_arabic_') }}</label>
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
                                            <label for="description_en">{{('admin\point_packages\create.description_english_') }}</label>
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
                                            <label for="points">Points <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
                                            <input type="number" class="form-control @error('points') is-invalid @enderror" 
                                                   id="points" name="points" value="{{ old('points', 100) }}" 
                                                   min="1" max="1000000" required>
                                            <small class="form-text text-muted">{{('admin\point_packages\create.number_of_points_in_this_package') }}</small>
                                            @error('points')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="price">Price <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
                                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                                   id="price" name="price" value="{{ old('price', 10) }}" 
                                                   min="0" max="10000" step="0.01" required>
                                            <small class="form-text text-muted">{{('admin\point_packages\create.price_in_the_selected_currency') }}</small>
                                            @error('price')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Currency -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="currency">Currency <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
                                            <select class="form-control @error('currency') is-invalid @enderror" 
                                                    id="currency" name="currency" required>
                                                <option value="SAR" {{ old('currency', 'SAR') == 'SAR' ? 'selected' : '' }}>{{('admin\point_packages\create.sar') }}</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>{{('admin\point_packages\create.usd') }}</option>
                                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>{{('admin\point_packages\create.eur') }}</option>
                                                <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>{{('admin\point_packages\create.aed') }}</option>
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
                                            <label for="duration_days">Duration (Days) <span class="text-danger">{{('admin\point_packages\create._') }}</span></label>
                                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                                   id="duration_days" name="duration_days" value="{{ old('duration_days', 30) }}" 
                                                   min="1" max="365" required>
                                            <small class="form-text text-muted">{{('admin\point_packages\create.how_long_the_points_are_valid') }}</small>
                                            @error('duration_days')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount Percentage -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="discount_percentage">{{('admin\point_packages\create.discount_') }}</label>
                                            <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                                   id="discount_percentage" name="discount_percentage" 
                                                   value="{{ old('discount_percentage', 0) }}" min="0" max="100">
                                            <small class="form-text text-muted">{{('admin\point_packages\create.discount_percentage_0_100_') }}</small>
                                            @error('discount_percentage')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Max Purchases -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_purchases">{{('admin\point_packages\create.max_purchases') }}</label>
                                            <input type="number" class="form-control @error('max_purchases') is-invalid @enderror" 
                                                   id="max_purchases" name="max_purchases" 
                                                   value="{{ old('max_purchases', 0) }}" min="0" max="1000">
                                            <small class="form-text text-muted">{{('admin\point_packages\create.0_unlimited') }}</small>
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
                                            <label for="icon">{{('admin\point_packages\create.icon') }}</label>
                                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-gift') }}" 
                                                   placeholder="fas fa-gift">
                                            <small class="form-text text-muted">{{('admin\point_packages\create.fontawesome_icon_class') }}</small>
                                            @error('icon')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Color -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="color">{{('admin\point_packages\create.color') }}</label>
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
                                            <label for="display_order">{{('admin\point_packages\create.display_order') }}</label>
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
                                            <label for="features_ar">{{('admin\point_packages\create.features_arabic_') }}</label>
                                            <textarea class="form-control @error('features.ar') is-invalid @enderror" 
                                                      id="features_ar" name="features[ar]" rows="3" 
                                                      placeholder="مميزات الباقة (سطر واحد لكل مميزة)">{{ old('features.ar') }}</textarea>
                                            <small class="form-text text-muted">{{('admin\point_packages\create.one_feature_per_line') }}</small>
                                            @error('features.ar')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Features English -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="features_en">{{('admin\point_packages\create.features_english_') }}</label>
                                            <textarea class="form-control @error('features.en') is-invalid @enderror" 
                                                      id="features_en" name="features[en]" rows="3" 
                                                      placeholder="Package features (one per line)">{{ old('features.en') }}</textarea>
                                            <small class="form-text text-muted">{{('admin\point_packages\create.one_feature_per_line') }}</small>
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
                                                <label class="custom-control-label" for="is_active">{{('admin\point_packages\create.active') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Popular -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_popular" 
                                                       name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_popular">{{('admin\point_packages\create.popular_package') }}</label>
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
                                    <div>
                                        <button type="button" class="btn btn-info mr-2" id="previewBtn">
                                            <i class="fas fa-eye mr-1"></i> Preview
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-save mr-1"></i> Create Package
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
                                    <h5 id="preview-name">{{('admin\point_packages\create.package_name') }}</h5>
                                    <p class="text-muted" id="preview-description">{{('admin\point_packages\create.package_description') }}</p>
                                    <div class="badge badge-primary" id="preview-points">{{('admin\point_packages\create.100_pts') }}</div>
                                    <div class="badge badge-success" id="preview-price">{{('admin\point_packages\create.10_sar') }}</div>
                                    <div class="badge badge-info" id="preview-duration">{{('admin\point_packages\create.30_days') }}</div>
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
                                    <button type="button" class="btn btn-outline-primary mb-2" onclick="fillBasicPackage()">
                                        <i class="fas fa-gift mr-1"></i> Basic Package
                                    </button>
                                    <button type="button" class="btn btn-outline-success mb-2" onclick="fillPopularPackage()">
                                        <i class="fas fa-star mr-1"></i> Popular Package
                                    </button>
                                    <button type="button" class="btn btn-outline-warning mb-2" onclick="fillPremiumPackage()">
                                        <i class="fas fa-crown mr-1"></i> Premium Package
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

    // Form submission with AJAX
    $('#packageForm').on('submit', function(e) {
        e.preventDefault();

        var submitBtn = $('#submitBtn');
        var originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Creating...');

        // Prepare form data as flat fields
        var formData = {
            _token: $('input[name="_token"]').val(),
            'name[ar]': $('#name_ar').val(),
            'name[en]': $('#name_en').val(),
            'description[ar]': $('#description_ar').val(),
            'description[en]': $('#description_en').val(),
            'features[ar]': $('#features_ar').val(),
            'features[en]': $('#features_en').val(),
            'points': $('#points').val(),
            'price': $('#price').val(),
            'currency': $('#currency').val(),
            'duration_days': $('#duration_days').val(),
            'discount_percentage': $('#discount_percentage').val(),
            'max_purchases': $('#max_purchases').val(),
            'icon': $('#icon').val(),
            'color': $('#color').val(),
            'display_order': $('#display_order').val(),
            'is_active': $('#is_active').is(':checked') ? 1 : 0,
            'is_popular': $('#is_popular').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Package created successfully!',
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location.href = '{{ route("admin.point_packages.index") }}';
                });
            },
            error: function(xhr, status, error) {
                var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                var errorMessage = 'Please fix the following errors:\n';

                if (errors) {
                    for (var field in errors) {
                        errorMessage += '- ' + errors[field][0] + '\n';
                    }
                } else {
                    errorMessage = 'An error occurred while creating the package.';
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
            title: 'Package Preview',
            html: `
                <div class="text-center">
                    <div class="package-badge mx-auto mb-3" style="background-color: ${$('#color').val() || '#007bff'};">
                        <i class="${$('#icon').val() || 'fas fa-gift'}" style="color: white; font-size: 24px;"></i>
                    </div>
                    <h5>{{('admin\point_packages\create._name_ar_val_') }}</h5>
                    <p class="text-muted">{{('admin\point_packages\create._description_ar_val_') }}</p>
                    <div class="badge badge-primary mr-2">{{('admin\point_packages\create._points_val_0_pts') }}</div>
                    <div class="badge badge-success mr-2">{{('admin\point_packages\create._price_val_0_currency') }}</div>
                    <div class="badge badge-info">{{('admin\point_packages\create._duration_days_val_0_days') }}</div>
                </div>
            `,
            confirmButtonText: 'OK'
        });
    });
});

// Quick action functions
function fillBasicPackage() {
    $('#name_ar').val('باقة أساسية');
    $('#name_en').val('Basic Package');
    $('#description_ar').val('باقة مناسبة للمستخدمين الجدد');
    $('#description_en').val('Suitable package for new users');
    $('#points').val(100);
    $('#price').val(10);
    $('#currency').val('SAR');
    $('#duration_days').val(30);
    $('#discount_percentage').val(0);
    $('#max_purchases').val(0);
    $('#icon').val('fas fa-gift');
    $('#color').val('#007bff');
    $('#display_order').val(1);
    $('#is_active').prop('checked', true);
    $('#is_popular').prop('checked', false);
}

function fillPopularPackage() {
    $('#name_ar').val('باقة شائعة');
    $('#name_en').val('Popular Package');
    $('#description_ar').val('الباقة الأكثر شعبية بين المستخدمين');
    $('#description_en').val('Most popular package among users');
    $('#points').val(500);
    $('#price').val(45);
    $('#currency').val('SAR');
    $('#duration_days').val(60);
    $('#discount_percentage').val(10);
    $('#max_purchases').val(100);
    $('#icon').val('fas fa-star');
    $('#color').val('#ffc107');
    $('#display_order').val(2);
    $('#is_active').prop('checked', true);
    $('#is_popular').prop('checked', true);
}

function fillPremiumPackage() {
    $('#name_ar').val('باقة مميزة');
    $('#name_en').val('Premium Package');
    $('#description_ar').val('باقة مميزة مع مزايا إضافية');
    $('#description_en').val('Premium package with extra features');
    $('#points').val(1000);
    $('#price').val(80);
    $('#currency').val('SAR');
    $('#duration_days').val(90);
    $('#discount_percentage').val(20);
    $('#max_purchases').val(50);
    $('#icon').val('fas fa-crown');
    $('#color').val('#dc3545');
    $('#display_order').val(3);
    $('#is_active').prop('checked', true);
    $('#is_popular').prop('checked', true);
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
            $('#packageForm')[0].reset();
            $('#preview-name').text('Package Name');
            $('#preview-description').text('Package description');
            $('#preview-icon').attr('class', 'fas fa-gift');
            $('#preview-badge').css('background-color', '#007bff');
            $('#preview-points').text('100 pts');
            $('#preview-price').text('10 SAR');
            $('#preview-duration').text('30 days');
        }
    });
}
</script>
@endpush
@endsection 






