@extends('dashboard')
@section('title', "Service Posts")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                            <div class="input-group mt-0 input-group-md">
                                <a href="{{ route('service_posts.create') }}" class="btn btn-success ">
                                    <i class="fa fa-plus fa-1x"></i>
                                    {{ __(' Add') }}
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-atom mr-1"></i>
                            Service Posts I Do
                        </h3>
                        
                    </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-bordered table-striped table-dark table-sm text-center">
                                <thead>
                                <tr class="btn-dark">
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>id</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>User</th>
                                    <th>favorites</th>
                                    <th>reports</th>
                                    <th>views</th>
                                    <th>type</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($servicePosts as $post)
                                    <tr>
                                        <td><input type="checkbox" name="selected[]" value="{{ $post->id }}"></td>
                                        <th>{{ $post->id }}</th>
                                        <td>{{ $post->title }}</td>
                                        <td>{{ $post->category }}</td>
                                        <td>{{ $post->user->user_name }}</td>
                                        <td>{{ $post->favorites_count }}</td>
                                        <td>{{ $post->report_count }}</td>
                                        <td>{{ $post->view_count }}</td>
                                        <td>{{ $post->have_badge }}</td>
                                        <td>
                                            <a href="{{ route('service_posts.show', $post->id) }}" class="btn btn-sm btn-primary">View</a>
                                            <a href="{{ route('service_posts.edit', $post->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('service_posts.destroy', $post->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" >Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <thead>
                                <tr class="btn-dark">
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>id</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>User</th>
                                    <th>favorites</th>
                                    <th>reports</th>
                                    <th>views</th>
                                    <th>type</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="card-footer ">
                            <div class="" style="display:flex; justify-content:center;align-items:center;">
                                 {{$servicePosts->links()}}
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
@endsection
