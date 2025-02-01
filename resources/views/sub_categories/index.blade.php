@extends('dashboard')
@section('title', 'Sub Categories')
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <ul class="nav nav-pills ml-auto">
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">
                                <li class="nav-item">
                                    <a href="{{ route('subcategories.create') }}" class="btn btn-success btn-md">
                                        <i class="fas fa-plus"></i> Add New Sub Category
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            Sub Categories
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped table-dark table-sm text-center">
                            <thead>
                                <tr class="btn-dark">
                                    <th>#</th>
                                    <th>Sub Category Name</th>
                                    <th>Category Name</th>
                                    <th>Photos</th>
                                    <th>Service Post Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subCategories as $subCategory)
                                    <tr>
                                        <td>{{ $subCategory->id }}</td>
                                        <td>{{ $subCategory->name }}</td>
                                        <td>{{ $subCategory->category->name }}</td>
                                        <td>
                                            @foreach($subCategory->photos as $photo)
                                                <img src="{{ $photo->src }}" style="height: 30px; width: 30px;">
                                            @endforeach
                                        </td>
                                        <td>{{ $subCategory->service_posts_count }}</td>
                                        <td>
                                            <a href="{{ route('subcategories.edit', $subCategory->id) }}" class="btn btn-success btn-sm">Edit</a>
                                            <form action="{{ route('subcategories.destroy', $subCategory->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit"  onclick="return confirm('Are you sure you want to delete this sub categories?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                     
                                @empty
                                    <tr>
                                        <td colspan="6">{{ __('No sub categories found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tr class="btn-dark">
                                <th>#</th>
                                <th>Sub Category Name</th>
                                <th>Category Name</th>
                                <th>Photos</th>
                                <th>Service Post Count</th>
                                <th>Action</th>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                        <div class="m-0" style="display: flex; justify-content: center;">
                            {{ $subCategories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

