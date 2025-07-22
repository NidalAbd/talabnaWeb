@extends('welcome')
@section('title', "User Posts")
@section('contentFilter')
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{('service_posts\content_filter.search_filters') }}</h5>
            </div>
            <div class="card-body">
                <!-- Add your search filters or data management content here -->
                <form>
                    <div class="mb-3">
                        <label for="filterCategory" class="form-label">{{('service_posts\content_filter.category') }}</label>
                        <select class="form-select" id="filterCategory" name="filterCategory">
                            <option value="">{{('service_posts\content_filter.select_category') }}</option>
                            <option value="category1">{{('service_posts\content_filter.category_1') }}</option>
                            <option value="category2">{{('service_posts\content_filter.category_2') }}</option>
                            <option value="category3">{{('service_posts\content_filter.category_3') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterDate" class="form-label">{{('service_posts\content_filter.date_range') }}</label>
                        <input type="text" class="form-control" id="filterDate" name="filterDate">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">{{('service_posts\content_filter.apply_filters') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection







