@extends('dashboard')
@section('title', 'Sub Categories')
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            Create Sub Category
                        </h3>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('subcategories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="category_id">Select Category</label>
                                <select name="categories_id" id="categories_id" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="name">Sub Category Name</label>
                                <input type="text" name="name" id="name" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="image">Select Image for Sub Category</label>
                                <input type="file" name="image" id="image" class="form-control-file">
                            </div>

                            <button type="submit" class="btn btn-primary">Create Sub Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
