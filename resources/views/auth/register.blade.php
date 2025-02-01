@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row d-flex justify-content-center ">
        <div class="col-md-4 mt-4">
            <div class="card">
                <div class="card-header">
                    <div class="row d-flex justify-content-center text-center">
                        <h2>
                            <b>
                                {{ __('Register Please') }}
                            </b>
                        </h2>
                    </div>
                    <br>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row mb-1">
                            <label for="name" class="">{{ __('Name') }}</label>
                            <div class="input-group">
                                <input id="name" type="text"
                                       class="form-control @error('name') is-invalid @enderror" name="name"
                                       value="{{ old('name') }}" required autocomplete="name" autofocus>
                                <span class="input-group-text">
                                       <i class="bi bi-person-fill"></i>
                                    </span>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                          <strong>{{ $message }}</strong>
                                        </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="email" class="">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <input id="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror" name="email"
                                       value="{{ old('email') }}" required autocomplete="email" autofocus>
                                <span class="input-group-text">
                                       <i class="bi bi-envelope"></i>
                                    </span>

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                          <strong>{{ $message }}</strong>
                                        </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-1">
                            <label for="password" class=" col-form-label ">{{ __('Password') }}</label>
                            <div class="input-group">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password"
                                       required autocomplete="current-password">
                                <span class="input-group-text">
                                       <i class="bi bi-eye-fill"></i>
                                    </span>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label for="password-confirm" class=" col-form-label ">{{ __('Confirm Password') }}</label>
                            <div class="input-group">
                                <input id="password-confirm" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password_confirmation"
                                       required autocomplete="new-password">
                                <span class="input-group-text">
                                       <i class="bi bi-eye-fill"></i>
                                    </span>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                            <!-- Register Login Button -->
                            <div class="row mb-3">
                                <!-- Normal Login Button -->
                                <div class="input-group">
                                    <input id="submit" type="submit"
                                           class="btn btn-secondary col-md-10 mb-2" value="Register with email">
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
                        <h2>
                            <b>
                                {{ __('Register Other Options') }}
                            </b>
                        </h2>
                    </div>
                    <br>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row mb-3">
                            <!-- Login with Google Button -->
                            <div class="input-group">
                                <input id="" type="button"
                                       class="btn btn-secondary col-md-10 mb-2" value="Login with Google">
                                <span class="btn btn-primary col-md-2 mb-2">
                                       <i class="bi bi-google red p-2"></i>
                                    </span>
                            </div>
                            <!-- Login with Facebook Button -->
                            <div class="input-group">
                                <input id="" type="button"
                                       class="btn btn-secondary col-md-10 mb-2" value="Login with Facebook">
                                <span class="btn btn-primary col-md-2 mb-2">
                                       <i class="bi bi-facebook p-2"></i>
                                    </span>
                            </div>
                            <!-- Login with Twitter Button -->
                            <div class="input-group ">
                                <input id="" type="button"
                                       class="btn btn-secondary col-md-10 mb-2" value="Login with Twitter">
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
