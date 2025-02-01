@extends('dashboard')
@section('title', "Statistics")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="container-fluid">
                    <h2><strong>Statistics</strong></h2>
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>{{$purchaseRequests}}</h3>
                                    <p>Purchase Requests</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="{{route('purchase_points.index')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">

                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>{{$user}}</h3>
                                    <p>{{ __('User') }}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="{{route('users.index')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">

                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>65</h3>
                                    <p>Unique Visitors</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>{{$allDiamond}}</h3>
                                    <p>Diamond</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="{{route('purchase_points.index')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">

                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>{{$allGolden}}</h3>
                                    <p>{{ __('Golden') }}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="{{route('users.index')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">

                            <div class="small-box bg-gray-dark">
                                <div class="inner">
                                    <h3>{{$allNormal}}</h3>
                                    <p>normal</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Active') }}</span>
                                    <span class="info-box-number">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-ghost"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Banned') }}</span>
                                    <span class="info-box-number">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gray-dark" >
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-check-circle"></i>
</span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Not Active') }}</span>
                                    <span class="info-box-number">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-ban"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Rejected') }}</span>
                                    <span class="info-box-number">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-gray-dark mb-3">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-tools"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('All') }}</span>
                                    <span class="info-box-number">{{$allService}}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-ghost"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('General Service') }}</span>
                                    <span class="info-box-number">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Real Estate') }}</span>
                                    <span class="info-box-number">{{$allRealState}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-joint"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Jobs') }}</span>
                                    <span class="info-box-number">{{$allJobs}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-car"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Cars') }}</span>
                                    <span class="info-box-number">{{$allCar}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box mb-3 bg-gray-dark">
                                <span class="info-box-icon bg-gray-light elevation-1"><i class="fas fa-phone"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Devices') }}</span>
                                    <span class="info-box-number">{{$allPhone}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix hidden-md-up"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
