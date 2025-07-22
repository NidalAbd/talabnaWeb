@extends('adminlte::auth.login')

@section('auth_footer')
    @parent
    <div class="text-center mt-3">
        <a href="{{ route('login.google') }}" class="btn btn-danger btn-block">
            <i class="fab fa-google mr-2"></i> Login with Google
        </a>
    </div>
@endsection






