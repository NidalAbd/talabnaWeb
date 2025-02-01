@extends('dashboard')
@section('title', "Create Service Post")
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            {{ __('Create Service Post') }}
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('service_posts.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="title" class="col-md-4">{{ __('Title') }}</label>
                                    <input id="title" type="text" class="form-control col-md-8 @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autocomplete="title" autofocus>
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
                                            <option value="{{ $category->id }}" data-id="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="description" class="col-md-4">{{ __('Description') }}</label>
                                    <textarea id="description" class="form-control col-md-8 @error('description') is-invalid @enderror" name="description" required>{{ old('description') }}</textarea>
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
                                            <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}">{{ $subcategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="location_latitudes" class="col-md-4">Location Latitudes</label>
                                    <input type="text" class="form-control col-md-8" id="location_latitudes" name="location_latitudes">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="location_longitudes" class="col-md-4">Location Longitudes</label>
                                    <input type="text" class="form-control col-md-8" id="location_longitudes" name="location_longitudes">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="type" class="col-md-4">Type</label>
                                    <select class="form-control col-md-8" id="type" name="type">
                                        <option value="">Choose</option>
                                        <option value="عرض">Offer</option>
                                        <option value="طلب">Demand</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="have_badge" class="col-md-4">Badge</label>
                                    <select class="form-control col-md-8" id="have_badge" name="have_badge">
                                        <option value="">Choose</option>
                                        <option value="عادي">Normal</option>
                                        <option value="ذهبي">Golden</option>
                                        <option value="ماسي">Diamond</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="badge_duration" class="col-md-4">Badge Duration</label>
                                    <select class="form-control col-md-8" id="badge_duration" name="badge_duration">
                                        <option value="0" {{ old('badge_duration') == '0' ? 'selected' : '' }}>غير محدد</option>
                                        <option value="1" {{ old('badge_duration') == '1' ? 'selected' : '' }}>One day</option>
                                        <option value="2" {{ old('badge_duration') == '2' ? 'selected' : '' }}>Two days</option>
                                        <option value="3" {{ old('badge_duration') == '3' ? 'selected' : '' }}>Three days</option>
                                        <option value="4" {{ old('badge_duration') == '4' ? 'selected' : '' }}>Four days</option>
                                        <option value="5" {{ old('badge_duration') == '5' ? 'selected' : '' }}>Five days</option>
                                        <option value="6" {{ old('badge_duration') == '6' ? 'selected' : '' }}>Six days</option>
                                        <option value="7" {{ old('badge_duration') == '7' ? 'selected' : '' }}>One week</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="price_currency" class="col-md-4">{{ __('Currency') }}</label>
                                    <select id="price_currency" class="form-control col-md-8 @error('price_currency') is-invalid @enderror" name="price_currency" required>
                                        <option value="دولار امريكي" {{ old('currency') == 'دولار امريكي' ? 'selected' : '' }}>&#36; (USD)</option>
                                        <option value="دينار اردني" {{ old('currency') == 'دينار اردني' ? 'selected' : '' }}>&#x4A;&#x44;&#x20; (JOD)</option>
                                        <option value="دينار تونسي" {{ old('currency') == 'دينار تونسي' ? 'selected' : '' }}>&#x62;&#x2E;&#x6E;&#x64; (TND)</option>
                                        <option value="ريال سعودي" {{ old('currency') == 'ريال سعودي' ? 'selected' : '' }}>&#65020; (SAR)</option>
                                        <option value="جنيه مصري" {{ old('currency') == 'جنيه مصري' ? 'selected' : '' }}>&#x62;&#x2E;&#x6C;&#x20; (EGP)</option>
                                        <option value="شيكل فلسطيني" {{ old('currency') == 'شيكل فلسطيني' ? 'selected' : '' }}>&#x20AA; (ILS)</option>
                                        <option value="ليرة لبنانية" {{ old('currency') == 'ليرة لبنانية' ? 'selected' : '' }}>&#x622;&#x2E;&#x641; (LBP)</option>
                                        <option value="درهم إماراتي" {{ old('currency') == 'درهم إماراتي' ? 'selected' : '' }}>&#65020; (AED)</option>
                                        <option value="درهم مغربي" {{ old('currency') == 'درهم مغربي' ? 'selected' : '' }}>&#65020; (MAD)</option>
                                        <option value="دينار كويتي" {{ old('currency') == 'دينار كويتي' ? 'selected' : '' }}>&#x62D;&#x2E;&#x6C;&#x20; (KWD)</option>
                                        <option value="ريال قطري" {{ old('currency') == 'ريال قطري' ? 'selected' : '' }}>&#65020; (QAR)</option>
                                        <option value="دينار بحريني" {{ old('currency') == 'دينار بحريني' ? 'selected' : '' }}>&#x62D;&#x2E;&#x62E; (BHD)</option>
                                        <option value="دينار ليبي" {{ old('currency') == 'دينار ليبي' ? 'selected' : '' }}>&#65020; (LYD)</option>
                                        <option value="ريال عماني" {{ old('currency') == 'ريال عماني' ? 'selected' : '' }}>&#x631;&#x2E;&#x631; (OMR)</option>
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
                                    <input type="number" class="form-control col-md-8" id="price" name="price">
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <label for="state" class="col-md-4">State</label>
                                    <select class="form-control col-md-8" id="state" name="state">
                                        <option value="">Select state</option>
                                        <option value="published">Published</option>
                                        <option value="archive">Archive</option>
                                        <option value="not published">Not Published</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-center">
                                    <input type="hidden" class="form-control" id="view_num" value="0" name="view_num">
                                </div>
                            </div>

                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="photos">Photos</label>
                                        <input type="file" name="photos[]" id="photos" accept="image/*" class="form-control-file" multiple required>
                                        <small class="form-text text-muted">Maximum file size: 2.5 MB</small>
                                        @error('photos')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Create</button>
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
