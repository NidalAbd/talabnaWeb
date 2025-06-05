@extends('adminlte::page')
@section('title', "Create PalserAdd Points")
@section('content_header')
    @include('partials.breadcrumbs')
    @include('partials.alert')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-coins text-primary mr-2"></i> Add Points</h1>
        <div>
            <a href="{{ route('palservice_points.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Points
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <!-- User Balance Card -->
                <div class="card card-outline card-info mb-4">
                    <div class="card-header">
                        <h3 class="card-title">User Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Username:</strong> {{ $user->user_name }}</p>
                                <p><strong>Name:</strong> {{ $user->name }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                @php
                                    $userBalance = App\Models\palservice_points::where('user_id', $user->id)->value('point') ?? 0;
                                @endphp
                                <h4>Current Balance</h4>
                                <div class="h2">
                                    <span class="badge bg-success p-2">{{ $userBalance }} points</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Form Card -->
                <div class="card">
                    <form method="POST" action="{{ route('palservice_points.store') }}">
                        @csrf
                        <div class="card-header">{{ __('Transfer Palservice Points') }}</div>
                        <div class="card-body table-responsive p-0">
                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="user_id">{{ __('User ID') }}</label>
                                    <span class="form-control col-md-12">{{ $user->user_name }}</span>
                                    <input type="hidden" name="to_user_id" value="{{$user->id}}">
                                    <input type="hidden" name="from_user_id" value="{{auth()->user()->id}}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="points">{{ __('Points') }}</label>
                                    <input id="points" type="number"
                                           class="form-control col-md-12 @error('points') is-invalid @enderror"
                                           name="points" value="{{ old('points') }}" required autocomplete="points">
                                    @error('points')
                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="type">type</label>
                                    <select class="form-control col-md-12" id="type" name="type" required>
                                        @if(auth()->user()->hasPermission('grant_points'))
                                            <option value="admin_grant">Admin Grant</option>
                                            <option value="admin_deduct">Admin Deduct</option>
                                            <option value="transfer">Transfer</option>
                                        @else
                                            <option value="transfer">Transfer</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer col-md-12">
                            <div class="row justify-content-around">
                                <div class="form-group col-md-4">
                                    <a href="{{ url()->previous() }}" class="btn btn-primary ">Back</a>
                                </div>
                                <div class="form-group col-md-4">

                                </div>
                                <div class="form-group col-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Create') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
