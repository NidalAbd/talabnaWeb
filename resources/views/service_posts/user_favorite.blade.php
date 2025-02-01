@extends('dashboard')
@section('title', "Favorite Service Posts")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-atom mr-1"></i>
                            Favorite Service Posts
                        </h3>
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">
                                <li class="nav-item">
                                    <div class="input-group mt-0 input-group-sm" >
                                        {{--                                        @if ($user->can('create_service')) {--}}
                                        <a href="{{route('service_posts.create')}}" style="float: right" >
                                            <button class="btn btn-primary" onclick="">
                                                <i class="fa fa-user fa-1x"></i>
                                                {{ __('+  خدمة') }}
                                            </button>&nbsp;
                                        </a>
                                        {{--                                        @endif--}}
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-sm text-center">
                            <thead>
                            <tr class="btn-secondary">
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
                            <tr class="btn-secondary">
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
  <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $servicePosts->links() }}
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
