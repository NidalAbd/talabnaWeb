@extends('adminlte::page')

@section('title', 'Create Badge Type - Talabna Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-plus-circle text-primary mr-2"></i>
                Create Badge Type
            </h1>
            <p class="text-muted mb-0">Add a new badge type for service posts</p>
        </div>
        <a href="{{ route('badge_types.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-award mr-2"></i> Badge Type Information
                    </h3>
                </div>
                <form action="{{ route('badge_types.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                                           id="name_ar" name="name_ar" value="{{ old('name_ar') }}"
                                           placeholder="e.g., ماسي" required>
                                    @error('name_ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                                           id="name_en" name="name_en" value="{{ old('name_en') }}"
                                           placeholder="e.g., Diamond" required>
                                    @error('name_en')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                           id="slug" name="slug" value="{{ old('slug') }}"
                                           placeholder="Auto-generated from English name">
                                    <small class="text-muted">Leave empty to auto-generate from English name</small>
                                    @error('slug')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="icon">Icon Class <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="icon-preview">
                                                <i class="fas fa-gem"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                               id="icon" name="icon" value="{{ old('icon', 'fas fa-gem') }}"
                                               placeholder="e.g., fas fa-gem" required>
                                    </div>
                                    <small class="text-muted">Use FontAwesome classes (e.g., fas fa-gem, fas fa-crown)</small>
                                    @error('icon')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="color_picker"
                                               value="{{ old('color', '#FFD700') }}" style="width: 50px; padding: 2px;">
                                        <input type="text" class="form-control @error('color') is-invalid @enderror"
                                               id="color" name="color" value="{{ old('color', '#FFD700') }}"
                                               placeholder="#FFD700" required>
                                    </div>
                                    @error('color')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror"
                                           id="priority" name="priority" value="{{ old('priority', 1) }}"
                                           min="1" required>
                                    <small class="text-muted">Lower number = higher priority (shown first)</small>
                                    @error('priority')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="points_per_day">Points per Day <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('points_per_day') is-invalid @enderror"
                                               id="points_per_day" name="points_per_day" value="{{ old('points_per_day', 0) }}"
                                               min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">pts/day</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Set to 0 for free/default badge</small>
                                    @error('points_per_day')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_boost_percent">View Boost Percentage</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('view_boost_percent') is-invalid @enderror"
                                               id="view_boost_percent" name="view_boost_percent"
                                               value="{{ old('view_boost_percent', 0) }}" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Percentage boost for post visibility</small>
                                    @error('view_boost_percent')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="text-muted">Inactive badges won't be available for selection</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Create Badge Type
                        </button>
                        <a href="{{ route('badge_types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i> Preview
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="badge-preview p-4 rounded" style="background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                        <i id="preview-icon" class="fas fa-gem" style="font-size: 64px; color: #FFD700;"></i>
                        <h4 id="preview-name-en" class="mt-3 mb-1">Diamond</h4>
                        <p id="preview-name-ar" class="text-muted mb-3">ماسي</p>
                        <div class="d-flex justify-content-center">
                            <span id="preview-points" class="badge badge-warning mr-2">0 pts/day</span>
                            <span id="preview-boost" class="badge badge-info">+0% boost</span>
                        </div>
                    </div>

                    <hr>

                    <h6>Common Icons</h6>
                    <div class="d-flex flex-wrap justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-gem">
                            <i class="fas fa-gem"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-crown">
                            <i class="fas fa-crown"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-star">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-medal">
                            <i class="fas fa-medal"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-trophy">
                            <i class="fas fa-trophy"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-award">
                            <i class="fas fa-award"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-fire">
                            <i class="fas fa-fire"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm m-1 icon-btn" data-icon="fas fa-bolt">
                            <i class="fas fa-bolt"></i>
                        </button>
                    </div>

                    <hr>

                    <h6>Suggested Colors</h6>
                    <div class="d-flex flex-wrap justify-content-center">
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#00BCD4" style="background-color: #00BCD4; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#FFD700" style="background-color: #FFD700; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#C0C0C0" style="background-color: #C0C0C0; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#CD7F32" style="background-color: #CD7F32; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#9C27B0" style="background-color: #9C27B0; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#4CAF50" style="background-color: #4CAF50; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#F44336" style="background-color: #F44336; width: 30px; height: 30px;"></button>
                        <button type="button" class="btn btn-sm m-1 color-btn" data-color="#2196F3" style="background-color: #2196F3; width: 30px; height: 30px;"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Update preview on input change
    function updatePreview() {
        const icon = $('#icon').val() || 'fas fa-gem';
        const color = $('#color').val() || '#FFD700';
        const nameEn = $('#name_en').val() || 'Badge Name';
        const nameAr = $('#name_ar').val() || 'اسم الشارة';
        const points = $('#points_per_day').val() || 0;
        const boost = $('#view_boost_percent').val() || 0;

        $('#preview-icon').attr('class', icon).css('color', color);
        $('#icon-preview i').attr('class', icon);
        $('#preview-name-en').text(nameEn);
        $('#preview-name-ar').text(nameAr);
        $('#preview-points').text(points + ' pts/day');
        $('#preview-boost').text('+' + boost + '% boost');
    }

    // Bind input events
    $('#icon, #color, #name_en, #name_ar, #points_per_day, #view_boost_percent').on('input change', updatePreview);

    // Color picker sync
    $('#color_picker').on('input', function() {
        $('#color').val($(this).val());
        updatePreview();
    });

    $('#color').on('input', function() {
        $('#color_picker').val($(this).val());
        updatePreview();
    });

    // Icon button click
    $('.icon-btn').on('click', function() {
        const icon = $(this).data('icon');
        $('#icon').val(icon);
        updatePreview();
    });

    // Color button click
    $('.color-btn').on('click', function() {
        const color = $(this).data('color');
        $('#color').val(color);
        $('#color_picker').val(color);
        updatePreview();
    });

    // Auto-generate slug from English name
    $('#name_en').on('blur', function() {
        if ($('#slug').val() === '') {
            const slug = $(this).val().toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            $('#slug').val(slug);
        }
    });

    // Initial preview update
    updatePreview();
});
</script>
@endpush
