@extends('dashboard')
@section('title', "Reports")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Reports
                        </h3>
                        <div class="card-tools">
                            <ul class="nav nav-pills ml-auto">

                            </ul>
                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-sm text-center">
                            <thead>
                            <tr class="btn-dark">
                                <th>ID</th>
                                <th>Type</th>
                                <th>Number of Reports</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if ($reports->count() > 0)
                                @foreach ($reports as $group)
                                    @if ($group->reportable)
                                        @php
                                            $reportable = $group->reportable;
                                            $routeName = $group->reportable_type == \App\Models\User::class ? 'user' : 'service_posts';
                                        @endphp

                                        <tr>
                                            <td>{{ $reportable->id }}</td>
                                            <td>
                                                @if ($group->reportable_type == \App\Models\User::class)
                                                    User: {{ $reportable->name }}
                                                @else
                                                    Service Post: {{ $reportable->title }}
                                                @endif
                                            </td>
                                            <td>{{ $group->total }}</td>
                                            <td>
                                                <a href="{{ route($routeName.'.show', $reportable->id) }}" class="btn btn-sm btn-primary">View</a>
                                                <form action="{{ route($routeName.'.destroy', $reportable->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this {{ $group->reportable_type == \App\Models\User::class ? 'user' : 'service_post' }}?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4">{{ __('No Record') }}</td>
                                </tr>
                            @endif
                            </tbody>
                            <tr class="btn-dark">
                                <th>ID</th>
                                <th>Type</th>
                                <th>Number of Reports</th>
                                <th>Action</th>
                            </tr>
                        </table>

                    </div>
                                          <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $reports->links() }}
                            </div>
                     </div>

                   
                </div>
            </div>
        </div>
    </div>
@endsection

