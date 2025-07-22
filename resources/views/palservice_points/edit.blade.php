@extends('adminlte::page')
@section('title', "Create Palservice Points")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{('Create Palservice Points') }}</div>
                    <div class="card-body table-responsive p-0">
                        <form method="POST" action="{{ route('palservice_points.update', $palservicePoint->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="user_id" class="col-md-4 col-form-label text-md-right">{{('User ID') }}</label>
                                    <div class="col-md-6">
                                        <input id="user_id" type="text" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ $palservicePoint->user_id }}" required autocomplete="user_id" autofocus>

                                        @error('user_id')
                                        <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="points" class="col-md-4 col-form-label text-md-right">{{('Points') }}</label>

                                    <div class="col-md-6">
                                        <input id="points" type="text" class="form-control @error('points') is-invalid @enderror" name="points" value="{{ $palservicePoint->points }}" required autocomplete="points">

                                        @error('points')
                                            <span class="invalid-feedback" role="alert">
                                                 <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <button type="submit" class="btn btn-primary col-md-8">
                                        {{('Update') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="panel-heading" style="display:flex; justify-content:center;align-items:center;">
                        </div>
                    </div>

                    <div class="card-footer ">
                        <a href="{{ url()-> __('palservice_points\edit.previous_class_btn_btn_primary_') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection








