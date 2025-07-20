@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Login History for {{ $user->name }}</h3>
    @if(count($logins) === 0)
        <div class="alert alert-info mt-4">No login history found for this user.</div>
    @else
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logins as $login)
                    <tr>
                        <td>{{ $login['date'] }}</td>
                        <td>{{ $login['ip'] }}</td>
                        <td>{{ $login['user_agent'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">Back to Users</a>
</div>
@endsection 