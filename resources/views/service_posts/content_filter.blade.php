@extends('welcome')
@section('title', "User Posts")
@section('contentFilter')
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Search Filters</h5>
            </div>
            <div class="card-body">
                <!-- Add your search filters or data management content here -->
                <form>
                    <div class="mb-3">
                        <label for="filterCategory" class="form-label">Category</label>
                        <select class="form-select" id="filterCategory" name="filterCategory">
                            <option value="">Select Category</option>
                            <option value="category1">Category 1</option>
                            <option value="category2">Category 2</option>
                            <option value="category3">Category 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filterDate" class="form-label">Date Range</label>
                        <input type="text" class="form-control" id="filterDate" name="filterDate">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
