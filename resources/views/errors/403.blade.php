@extends('adminlte::page')

@section('title', '403 - Access Denied')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-danger">
                <i class="fas fa-ban mr-2"></i> 
                Access Denied
            </h1>
            <p class="text-muted mb-0">{{ __('errors3.you_do_not_have_permission_to_access_thi') }}</p>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-danger mb-3">{{ __('errors3.403_access_denied') }}</h2>
                    <p class="lead mb-4">{{ __('errors3.you_do_not_have_the_required_permissions') }}</p>
                    <p class="text-muted mb-4">{{ __('errors3.please_contact_your_administrator_if_you') }}</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mr-2">
                            <i class="fas fa-home mr-1"></i> Go to Dashboard
                        </a>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
