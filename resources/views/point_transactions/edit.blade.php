@extends('dashboard')
@section('title', "edit User")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            {{ __('Edit Point Transaction') }}
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('point_transactions.update', $pointTransaction->id) }}">
                        @csrf
                        @method('PUT')
                    <div class="card-body table-responsive p-0">
                            <div class="form-inline col-md-12">
                                    <div class="form-group col-md-4">
                                        <label for="user_id" class="col-md-4 col-form-label text-md-right">{{ __('User ID') }}</label>
                                        <div class="col-md-6">
                                            <input id="user_id" type="text" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ old('user_id', $pointTransaction->user_id) }}" required autocomplete="user_id" autofocus>
                                            @error('user_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="amount" class="col-md-4 col-form-label text-md-right">{{ __('Amount') }}</label>
                                        <div class="col-md-6">
                                            <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $pointTransaction->amount) }}" required autocomplete="amount">
                                            @error('amount')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                <div class="form-group col-md-4">
                                    <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('Description') }}</label>

                                    <div class="col-md-6">
                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" required autocomplete="description">{{ old('description', $pointTransaction->description) }}</textarea>

                                        @error('description')
                                        <span class="invalid-feedback" role="alert">
                                               <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer  ">
                            <div class="row justify-content-center col-md-12">
                                <div class="form-group col-md-6">
                                    <a href="{{ url()->previous() }}" class="btn btn-primary col-md-4">cancel</a>
                                </div>
                                <div class="form-group col-md-6">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Update') }}
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

