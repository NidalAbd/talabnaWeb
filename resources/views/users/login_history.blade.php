@extends('layouts.admin')

@section('content')
<div class="container">
    <h3>Login History for {{ $user->name }}</h3>
    @if(count($logins) === 0)
        <div class="alert alert-info mt-4">{{('users\login_history.no_login_history_found_for_this_user_') }}</div>
    @else
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>{{('users\login_history.date') }}</th>
                    <th>{{('users\login_history.ip_address') }}</th>
                    <th>{{('users\login_history.user_agent') }}</th>
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
    <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">{{('users\login_history.back_to_users') }}</a>
</div>
@endsection 






