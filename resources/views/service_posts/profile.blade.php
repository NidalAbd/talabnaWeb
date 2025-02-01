@extends('dashboard')
@section('title', "User Posts")
@section('content')
    <div class="container">
        @if(count($servicePosts) > 0)
                @foreach($servicePosts as $servicePost)
                    <div class="row justify-content-center">
                        <div class="col-md-8 mb-3">
                            <div class="card ">
                                <div class="card-header
                            @if($servicePost->have_badge == 'N')
                                bg-primary
                            @elseif($servicePost->have_badge == 'G')
                               bg-warning
                            @elseif($servicePost->have_badge == 'D')
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
                                                <h4>{{ $servicePost->user->user_name }}</h4>
                                            </div>

                                        </div>
                                        <div class="d-flex align-items-center">
                                            <h5>{{ $servicePost->title }}</h5>


                                        </div>
                                    </div>


                                </div>
                                <div class="card-body text-right">
                                    <p>{{ $servicePost->description }}</p>
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
                                            <a href="#"><i class="fas fa-heart p-3">&nbsp;</i>fav</a>
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
                                <h5 class="modal-title" id="reportModalLabel">Report Service Post</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('reports.store', ['reported' => 'service_post', 'reportedId' => $servicePost->id]) }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <input type="hidden" name="service_post_id" value="{{ $servicePost->id }}">
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="reason">Reason for report</label>
                                        <select class="form-control" name="reason" id="reason">
                                            <option value="spam">Spam</option>
                                            <option value="inappropriate_content">Inappropriate Content</option>
                                            <option value="harassment">Harassment</option>
                                            <option value="false_information">False information</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Submit Report</button>
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

                                        <h4>{{ $user->user_name }}</h4>
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
                                    <a href="#"><i class="fas fa-heart p-3">&nbsp;</i>fav</a>
                                </div>
                                <div>
                                    <i class="fas  p-3">&nbsp; </i>
                                </div>
                                <div>
                                    <a href="#"><i class="fas fa-flag p-3">&nbsp; </i>report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>


@endsection
