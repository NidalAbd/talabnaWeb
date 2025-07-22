@extends('adminlte::page')
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
                                <th>{{('admin\favorite.id') }}</th>
                                <th>{{('admin\favorite.post') }}</th>
                                <th>{{('admin\favorite.number_of_favorite') }}</th>
                                <th>{{('admin\favorite.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($favorites->count() > 0)
                                @foreach($favorites as $favorite)
                                    <tr>
                                        <td>{{ $favorite->id }}</td>
                                        <td>{{ $favorite->post_title ?? '' }}</td>
                                        <td>{{ $favorite->number_of_favorite ?? '' }}</td>
                                        <td>
                                            <a href="{{ route('admin.posts.show', $favorite->id) }}" class="btn btn-info btn-sm">Show</a>
                                            <a href="{{ route('admin.posts.edit', $favorite->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <form action="{{ route('admin.posts.destroy', $favorite->favoritable_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">{{('admin\favorite.delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4">{{('No Record') }}</td>
                                </tr>
                            @endif
                            </tbody>
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








