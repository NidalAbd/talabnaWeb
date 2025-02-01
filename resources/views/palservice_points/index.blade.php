@extends('dashboard')
@section('title', "User")
@section('content')
<div class="p-0">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        <h3 class="float-left">Points</h3>
                    </h3>
                    <div class="card-tools">
                        <ul class="nav nav-pills ml-auto">
                            <li class="nav-item">
                                <div class="input-group mt-0 input-group-sm">
{{--                                    @permission('create-users')--}}
{{--                                    @endpermission--}}
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-striped table-dark table-sm text-center">
                        <thead>
                            <tr class="btn-dark">
                                <th>No</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(is_countable($palservicePoints) && count($palservicePoints) > 0)
                            @foreach($palservicePoints as $palservicePoint)
                                <tr>
                                    <td>{{ $palservicePoint->id }}</td>
                                    <td>{{ $palservicePoint->user?->name ?? 'Passed User' }}</td>
                                    <td>
                                        @if($palservicePoint->user && count($palservicePoint->user->roles) > 0)
                                            @foreach($palservicePoint->user->roles as $role)
                                                {{ $role->name ?? 'Passed User' }}
                                            @endforeach
                                        @else
                                            Passed User
                                        @endif
                                    </td>
                                    <td>{{ $palservicePoint->point }}</td>
                                    <td>
{{--                                        <a href="{{ route('palservice_points.edit', $palservicePoint->id) }}" class="btn btn-success"><i class="fas fa-edit"></i> Edit</a>--}}
{{--                                        <form action="{{ route('palservice_points.destroy', $palservicePoint->id) }}" method="POST" class="d-inline-block">--}}
{{--                                            @csrf--}}
{{--                                            @method('DELETE')--}}
{{--                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>--}}
{{--                                        </form>--}}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">{{ __('No Records') }}</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                            <tr class="btn-dark">
                                <th>No</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                    <div class="m-0" style="display: flex; justify-content: center;">
                        {{ $palservicePoints->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
