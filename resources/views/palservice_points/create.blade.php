@extends('dashboard')
@section('title', "Create Palservice Points")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                                        <option value="admin_grant">admin grant</option>
                                            <option value="transfer">transfer</option>
                                    @else
                                        <option value="transfer">transfer</option>
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

