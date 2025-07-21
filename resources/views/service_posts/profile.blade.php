@extends('adminlte::page')
@section('title', "User Posts")
@section('content')
    <div class="container">
        @if(count($servicePosts) > 0)
                @foreach($servicePosts as $servicePost)
                    <div class="row justify-content-center">
                        <div class="col-md-8 mb-3">
                            <div class="card ">
                                <div class="card-header
                            @if($servicePost->level && $servicePost->level->name['ar'] == 'عادي')
                                bg-primary
                            @elseif($servicePost->level && $servicePost->level->name['ar'] == 'ذهبي')
                               bg-warning
                            @elseif($servicePost->level && $servicePost->level->name['ar'] == 'ماسي')
                               bg-info
                            @endif
                            ">
                                    <div class="row justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                @if ($servicePost->photos->count() > 0)
                                                    <img src="{{ asset('storage/' . $servicePost->user->photos->first()->src) }}" class="img-circle" alt="Profile Picture" width="50">
                                                @else
                                                    <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle" alt="Profile Picture" width="50">
                                                @endif
                                            </div>
                                            <div>
                                                <h4>{{ $servicePost->field</h4>
                                            </div>

                                        </div>
                                        <div class="d-flex align-items-center">
                                            <h5>{{ $servicePost->field</h5>


                                        </div>
                                    </div>


                                </div>
                                <div class="card-body text-right">
                                    <p>{{ $servicePost->field</p>
                                    <div>
                                        @if ($servicePost->photos->count() > 0)
                                            <div class="mt-4">
                                                <div class="row justify-content-center">
                                                    @foreach ($servicePost->photos as $photo)
                                                        <div class="col-md-3">
                                                            <img src="{{ asset('storage/'.$photo->src) }}" class="img-thumbnail" >
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            {{'no post photos'}}
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row justify-content-between">
                                        <div>
                                            <a><i class="fas fa-eye p-3"></i>&nbsp;{{ $servicePost->view_num }}</a>
                                        </div>
                                        <div>
                                            <a href="#"><i class="fas fa-heart p-3">{{ __('service_posts\profile._nbsp_') }}</i>{{ __('service_posts\profile.fav') }}</a>
                                        </div>
                                        <div>
                                            <i class="fas  p-3">&nbsp; {{ $servicePost->price }} &nbsp;{{ $servicePost->price_currency }}</i>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reportModal">
                                                Report Post
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reportModalLabel">{{ __('service_posts\profile.report_service_post') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('reports.store', ['reported' => 'service_post', 'reportedId' => $servicePost->id]) }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <input type="hidden" name="service_post_id" value="{{ $servicePost->id }}">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="reason">{{ __('service_posts\profile.reason_for_report') }}</label>
                                        <select class="form-control" name="reason" id="reason">
                                            <option value="spam">{{ __('service_posts\profile.spam') }}</option>
                                            <option value="inappropriate_content">{{ __('service_posts\profile.inappropriate_content') }}</option>
                                            <option value="harassment">{{ __('service_posts\profile.harassment') }}</option>
                                            <option value="false_information">{{ __('service_posts\profile.false_information') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('service_posts\profile.cancel') }}</button>
                                    <button type="submit" class="btn btn-danger">{{ __('service_posts\profile.submit_report') }}</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                @endforeach
        @else
            <div class="row justify-content-center">
                <div class="col-md-8 mb-3">
                    <div class="card ">
                        <div class="card-header">
                            <div class="row justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div>
                                        @if ($user->photos->count() > 0)
                                            <img src="{{ asset('storage/' . $user->photos->first()->src) }}" class="img-circle" alt="Profile Picture" width="50">
                                        @else
                                            <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle" alt="Profile Picture" width="50">
                                        @endif
                                    </div>
                                    <div>

                                        <h4>{{ $user->field</h4>
                                    </div>

                                </div>
                                <div class="d-flex align-items-center">
                                </div>
                            </div>


                        </div>
                        <div class="card-body text-right">
                            <p>{{ 'هذا المستخدم ليس لديه اي منشورات' }}</p>
                            <div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row justify-content-between">
                                <div>
                                    <a><i class="fas fa-eye p-3"></i>&nbsp;{{ 0 }}</a>
                                </div>
                                <div>
                                    <a href="#"><i class="fas fa-heart p-3">{{ __('service_posts\profile._nbsp_') }}</i>{{ __('service_posts\profile.fav') }}</a>
                                </div>
                                <div>
                                    <i class="fas  p-3">&nbsp; </i>
                                </div>
                                <div>
                                    <a href="#"><i class="fas fa-flag p-3">&nbsp; </i>{{ __('service_posts\profile.report') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>


@endsection
