@extends('dashboard')
@section('title', 'Edit Sub Category')
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            Edit Sub Category
                        </h3>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('subcategories.update', ['subcategory' => $subcategories->id])}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="category_id">Select Category</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $subcategories->category_id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Sub Category Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $subcategories->name }}">
                            </div>

                            <div class="form-group">
                                <label for="image">Update Image for Sub Category</label>
                                <input type="file" name="image" id="image" class="form-control-file">
                            </div>

                            <button type="submit" class="btn btn-primary">Update Sub Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
