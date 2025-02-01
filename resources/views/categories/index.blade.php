@extends('dashboard')
@section('title', "Categories")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>
                            <h3 class="float-left">Categories</h3>
                        </h3>
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">
                                <li class="nav-item">
                                    <div class="input-group mt-0 input-group-sm" >
                                        {{--                      //  @permission('create-users')--}}
                                        {{--                        @endpermission--}}
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped table-dark table-sm text-center" >
                            <thead>
                            <tr class="btn-dark " >
                                <th>id</th>
                                <th>image</th>
                                <th>Category Name</th>
                                <th>Subcategories Count</th>
                                <th>Service Posts Count</th>

                            </tr>
                            </thead>
                            <tbody>
                            @if(is_countable($categories) && count($categories) > 0)
                                @foreach($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>
                                            @foreach($category->photos as $photo)
                                                <img src="{{ $photo->src }}" width="50" height="50">
                                            @endforeach
                                        </td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->sub_categories_with_service_posts_count }}</td>
                                        <td>{{ $category->service_posts_count }}</td>

                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3">{{ __('No data available') }}</td>
                                </tr>
                            </tbody>

                            @endif
                            <tr class="btn-dark " >
                                <th>id</th>
                                <th>image</th>
                                <th>Category Name</th>
                                <th>Subcategories Count</th>
                                <th>Service Posts Count</th>
                            </tr>
                        </table>
                    </div>
                     <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $categories->links() }}
                            </div>
                     </div>
                   
                </div>
            </div>
        </div>
    </div>
@endsection
