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
                                <th>{{ __('admin\favorite.id') }}</th>
                                <th>{{ __('admin\favorite.post') }}</th>
                                <th>{{ __('admin\favorite.number_of_favorite') }}</th>
                                <th>{{ __('admin\favorite.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($favorites->count() > 0)
                                @foreach($favorites as $favorite)
                                    <tr>
                                        <td>{{ $favorite->field</td>
                                        <td>{{ $favorite->field</td>
                                        <td>{{ $favorite->field</td>
                                        <td>
                                            <a href="{{ route('admin.posts.show', $favorite->field</a>
                                            <a href="{{ route('admin.posts.edit', $favorite->field</a>
                                            <form action="{{ route('admin.posts.destroy', $favorite->favoritable_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('admin\favorite.delete') }}</button>
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
                                <th>{{ __('admin\favorite.id') }}</th>
                                <th>{{ __('admin\favorite.type') }}</th>
                                <th>{{ __('admin\favorite.number_of_reports') }}</th>
                                <th>{{ __('admin\favorite.action') }}</th>
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

