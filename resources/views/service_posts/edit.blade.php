@extends('dashboard')
@section('title', "Edit Service Post")
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            {{ __('Edit Service Post') }}
                        </h3>
                    </div>
                    <form action="{{ route('service_posts.update', $servicePost) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="title" class="col-md-4">Title</label>
                                    <input type="text" name="title" class="form-control col-md-8 @error('title') is-invalid @enderror" value="{{ old('title', $servicePost->title) }}" required autofocus>
                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="category" class="col-md-4">Category</label>
                                    <select class="form-control col-md-8" id="category" name="category">
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @if ($category->id == $servicePost->categories_id) selected @endif>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="description" class="col-md-4">{{ __('Description') }}</label>
                                    <textarea id="description" class="form-control col-md-8 @error('description') is-invalid @enderror" name="description" required>{{ old('description', $servicePost->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="sub_category" class="col-md-4">Sub Category</label>
                                    <select class="form-control col-md-8" id="sub_category" name="sub_category">
                                        <option value="">Select subcategory</option>
                                        @foreach ($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" @if ($subcategory->id == $servicePost->sub_categories_id) selected @endif>{{ $subcategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="location_latitudes" class="col-md-4">Location Latitudes</label>
                                    <input type="text" class="form-control col-md-8" id="location_latitudes" name="location_latitudes" value="{{ old('location_latitudes', $servicePost->location_latitudes) }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="location_longitudes" class="col-md-4">Location Longitudes</label>
                                    <input type="text" class="form-control col-md-8" id="location_longitudes" name="location_longitudes" value="{{ old('location_longitudes', $servicePost->location_longitudes) }}">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="type" class="col-md-4">Type</label>
                                    <select class="form-control col-md-8" id="type" name="type">
                                        <option value="">Choose</option>
                                        <option value="عرض" {{ old('type', $servicePost->type) == 'عرض' ? 'selected' : '' }}>Offer</option>
                                        <option value="طلب" {{ old('type', $servicePost->type) == 'طلب' ? 'selected' : '' }}>Demand</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="price_currency" class="col-md-4">{{ __('Currency') }}</label>
                                    <select id="price_currency" class="form-control col-md-8 @error('price_currency') is-invalid @enderror" name="price_currency" required>
                                        <option value="دولار امريكي" {{ old('price_currency', $servicePost->price_currency) == 'دولار امريكي' ? 'selected' : '' }}>&#36; (USD)</option>
                                        <option value="دينار اردني" {{ old('price_currency', $servicePost->price_currency) == 'دينار اردني' ? 'selected' : '' }}>&#x4A;&#x44;&#x20; (JOD)</option>
                                        <option value="دينار تونسي" {{ old('price_currency', $servicePost->price_currency) == 'دينار تونسي' ? 'selected' : '' }}>&#x62;&#x2E;&#x6E;&#x64; (TND)</option>
                                        <option value="ريال سعودي" {{ old('price_currency', $servicePost->price_currency) == 'ريال سعودي' ? 'selected' : '' }}>&#65020; (SAR)</option>
                                        <option value="جنيه مصري" {{ old('price_currency', $servicePost->price_currency) == 'جنيه مصري' ? 'selected' : '' }}>&#x62;&#x2E;&#x6C;&#x20; (EGP)</option>
                                        <option value="شيكل فلسطيني" {{ old('price_currency', $servicePost->price_currency) == 'شيكل فلسطيني' ? 'selected' : '' }}>&#x20AA; (ILS)</option>
                                        <option value="ليرة لبنانية" {{ old('price_currency', $servicePost->price_currency) == 'ليرة لبنانية' ? 'selected' : '' }}>&#x622;&#x2E;&#x641; (LBP)</option>
                                        <option value="درهم إماراتي" {{ old('price_currency', $servicePost->price_currency) == 'درهم إماراتي' ? 'selected' : '' }}>&#65020; (AED)</option>
                                        <option value="درهم مغربي" {{ old('price_currency', $servicePost->price_currency) == 'درهم مغربي' ? 'selected' : '' }}>&#65020; (MAD)</option>
                                        <option value="دينار كويتي" {{ old('price_currency', $servicePost->price_currency) == 'دينار كويتي' ? 'selected' : '' }}>&#x62D;&#x2E;&#x6C;&#x20; (KWD)</option>
                                        <option value="ريال قطري" {{ old('price_currency', $servicePost->price_currency) == 'ريال قطري' ? 'selected' : '' }}>&#65020; (QAR)</option>
                                        <option value="دينار بحريني" {{ old('price_currency', $servicePost->price_currency) == 'دينار بحريني' ? 'selected' : '' }}>&#x62D;&#x2E;&#x62E; (BHD)</option>
                                        <option value="دينار ليبي" {{ old('price_currency', $servicePost->price_currency) == 'دينار ليبي' ? 'selected' : '' }}>&#65020; (LYD)</option>
                                        <option value="ريال عماني" {{ old('price_currency', $servicePost->price_currency) == 'ريال عماني' ? 'selected' : '' }}>&#x631;&#x2E;&#x631; (OMR)</option>
                                    </select>
                                    @error('price_currency')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="price" class="col-md-4">Price</label>
                                    <input type="number" class="form-control col-md-8" id="price" name="price" value="{{ old('price', $servicePost->price) }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="state" class="col-md-4">State</label>
                                    <select class="form-control col-md-8" id="state" name="state">
                                        <option value="">Select state</option>
                                        <option value="published" {{ old('state', $servicePost->state) == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="archive" {{ old('state', $servicePost->state) == 'archive' ? 'selected' : '' }}>Archive</option>
                                        <option value="not published" {{ old('state', $servicePost->state) == 'not published' ? 'selected' : '' }}>Not Published</option>
                                        <option value="rejected" {{ old('state', $servicePost->state) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <input type="hidden" class="form-control" id="view_num" value="{{ old('view_num', $servicePost->view_num) }}" name="view_num">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="photos">Photos</label>
                                        <input type="file" name="photos[]" id="photos" accept="image/*" class="form-control-file" multiple>
                                        <small class="form-text text-muted">Maximum file size: 2.5 MB</small>
                                        @error('photos')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                        @if ($servicePost->photos->count() > 0)
    <div class="mt-4">
        <label for="current_photos">Current Photos:</label>
        <div class="row">
            @foreach ($servicePost->photos as $photo)
                <div class="col-md-3 d-flex flex-column align-items-center">
                    <img src="{{ asset('storage/'.$photo->src) }}" class="img-thumbnail mb-2">
                    <label for="delete_photo_{{ $photo->id }}" class="form-check-label">
                        <input type="checkbox" name="delete_photos[]" id="delete_photo_{{ $photo->id }}" value="{{ $photo->id }}" class="form-check-input"> Delete
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endif

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('#category').change(function(){
                var category_id = $(this).val();
                $.ajax({
                    url: '{{ route('subcategories.index') }}',
                    type: 'GET',
                    data: { category_id: category_id },
                    success: function(response){
                        var subcategories = response.subcategories;
                        var subcategory_select = $('#sub_category');
                        subcategory_select.empty();
                        subcategory_select.append('<option value="">Select subcategory</option>');
                        $.each(subcategories, function(index, subcategory){
                            subcategory_select.append('<option value="'+subcategory.id+'">'+subcategory.name+'</option>');
                        });
                    }
                });
            });
        });
    </script>
@endsection
