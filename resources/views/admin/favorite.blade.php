@extends('dashboard')
@section('title', "Favorite")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Favorite
                        </h3>
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">

                            </ul>
                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-sm text-center">
                            <thead>
                            <tr class="btn-secondary">
                                <th>ID</th>
                                <th>Post</th>
                                <th>Number of Favorite</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($favorites->count() > 0)
                                @foreach($favorites as $favorite)
                                    <tr>
                                        <td>{{ $favorite->favoritable_id }}</td>
                                        <td>{{ $favorite->favoritable->title }}</td>
                                        <td>{{ $favorite->favorite_count }}</td>
                                        <td>
                                            <a href="{{ route('admin.posts.show', $favorite->favoritable_id) }}" class="btn btn-info btn-sm">View</a>
                                            <a href="{{ route('admin.posts.edit', $favorite->favoritable_id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <form action="{{ route('admin.posts.destroy', $favorite->favoritable_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4">{{ __('No Record') }}</td>
                                </tr>
                            @endif
                            </tbody>
                            <thead>
                            <tr class="btn-secondary">
                                <th>ID</th>
                                <th>Type</th>
                                <th>Number of Reports</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $favorites->links() }}
                            </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
@endsection

