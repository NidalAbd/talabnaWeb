@extends('dashboard')
@section('title', "Users")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end mb-3">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                            <div class="input-group mt-0 input-group-sm">
                                <a href="{{ route('users.create') }}" class="btn btn-success me-2">
                                    <i class="fa fa-user fa-1x"></i>
                                    {{ __('+ User') }}
                                </a>
                                <a href="/app" class="btn btn-info">
                                    <i class="fa fa-user fa-1x"></i>
                                    {{ __('Roles & Permissions Assignment') }}
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-users mr-1"></i>
                            Users
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped table-dark text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>State</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Report</th>
                                    <th>Posts</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(is_countable($Users) && count($Users) > 0)
                                @foreach($Users as $user)
                                    <tr>
                                        <td>{{$user->id}}</td>
                                        <td>{{$user->user_name}}</td>
                                        <td>{{$user->is_active ? 'Active' : 'Inactive'}}</td>
                                        <td>
                                            @if(count($user->roles) > 0)
                                                @foreach($user->roles as $role)
                                                    {{$role->name}}
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>{{$user->email}}</td>
                                        <td>{{ $user->reports_count }}</td>
                                        <td>{{ $user->service_posts_count }}</td>
                                        <td>
                                            <a href="{{ route('user.profile', ['user' => $user->id]) }}" class="btn btn-sm btn-info">Profile</a>
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-success">View</a>
                                            <a href="{{ route('palservice_points.create', ['user_id' => $user->id]) }}" class="btn btn-sm btn-primary">
                                                Add Points
                                            </a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit"  onclick="return confirm('Are you sure you want to delete this sub user?')">Delete</button>

                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8">{{ __('No Record') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-center" style="height: 50px;">
                        {{ $Users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
