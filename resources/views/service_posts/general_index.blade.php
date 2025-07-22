@extends('dashboard')
@section('title', "General Service Posts")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                  <div class="d-flex justify-content-end mb-3">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                            <div class="input-group mt-0 input-group-sm">
                               <a href="{{route('service_posts.create')}}" style="float: right">
                                            <button class="btn btn-primary" onclick="">
                                                <i class="fa fa-user fa-1x"></i>
                                                {{ __('+ خدمة') }}
                                            </button>
                                        </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-atom mr-1"></i>
                           General Service Posts
                        </h3>
                        
                    </div>
                    <div class="card-body table-responsive  p-0">
                        <table class="table table-bordered table-striped table-dark table-sm text-center">
                            <thead>
                            <tr class="btn-dark">
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
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->category }}</td>
                                    <td>{{ $post->user->user_name }}</td>
                                    <td>{{ $post->favorites_count }}</td>
                                    <td>{{ $post->report_count }}</td>
                                    <td>{{ $post->view_count }}</td>
                                    <td>{{ $post->have_badge }}</td>
                                    <td>
                                        <a href="{{ route('service_posts.show', $post->id) }}" class="btn btn-sm btn-primary">View</a>
                                        {{--                                            @can('update_service', $post)--}}
                                        <a href="{{ route('service_posts.edit', $post->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        {{--                                            @endcan--}}
                                        {{--                                            @can('destroy_service', $post)--}}
                                        <form action="{{ route('service_posts.destroy', $post->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                                        </form>
                                        {{--                                            @endcan--}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <thead>
                            <tr class="btn-dark">
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
                        <div class="panel-heading" style="display:flex; justify-content:center;align-items:center;">
                            <div class="panel-heading" style="display:flex; justify-content:center;align-items:center;">
                                {{$servicePosts->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
