@extends('layouts.app')

@section('content')
    <div class="container wrapper">
        <div class="row d-flex justify-content-center">
            <div class="col-md-4 mt-5">
                <div class="card">
                    <div class="card-header">
                        <div class="row d-flex justify-content-center text-center">
                            <h2><b>{{ __('Login Please') }}</b></h2>
                        </div>
                        <br>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="row mb-3">
                                <label for="email" class="">{{ __('Email Address') }}</label>
                                <div class="input-group">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="password" class="col-form-label">{{ __('Password') }}</label>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    <span class="input-group-text"><i class="bi bi-eye-fill"></i></span>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                                    </div>
                                    <label>
                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link" style="text-decoration: none; color: #00aa44; font-weight: bold;" href="{{ route('password.request') }}">
                                                {{ __('Forgot Password') }}
                                            </a>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="input-group">
                                    <button id="submit" type="submit" class="btn btn-secondary col-md-10 mb-2">
                                        {{ __('Login with email') }}
                                    </button>
                                    <span class="btn btn-primary col-md-2 mb-2">
                                        <i class="bi bi-key-fill p-2"></i>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="card">
                    <div class="card-header">
                        <div class="row d-flex justify-content-center text-center">
                            <h2><b>{{ __('Login Other Options') }}</b></h2>
                        </div>
                        <br>
                        <form>
                            <div class="row mb-3">
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary col-md-10 mb-2">
                                        {{ __('Login with Google') }}
                                    </button>
                                    <span class="btn btn-primary col-md-2 mb-2">
                                        <i class="bi bi-google red p-2"></i>
                                    </span>
                                </div>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary col-md-10 mb-2">
                                        {{ __('Login with Facebook') }}
                                    </button>
                                    <span class="btn btn-primary col-md-2 mb-2">
                                        <i class="bi bi-facebook p-2"></i>
                                    </span>
                                </div>
                                <div class="input-group">
                                    <button type="button" class="btn btn-secondary col-md-10 mb-2">
                                        {{ __('Login with Twitter') }}
                                    </button>
                                    <span class="btn btn-primary col-md-2 mb-2">
                                        <i class="bi bi-twitter p-2"></i>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
