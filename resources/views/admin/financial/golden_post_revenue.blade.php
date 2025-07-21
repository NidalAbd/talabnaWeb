@extends('adminlte::page')
@section('title', 'Golden Post Revenue')
@section('content_header')
    <h1><i class="fas fa-star text-warning mr-2"></i> Golden Post Revenue</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{ __('admin\financial\golden_post_revenue.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{ __('admin\financial\golden_post_revenue.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\golden_post_revenue.total_golden_revenue') }}</h5>
                    <h3>{{ __('admin\financial\golden_post_revenue._2_800') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\golden_post_revenue.golden_posts') }}</h5>
                    <h3>{{ __('admin\financial\golden_post_revenue.70') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\golden_post_revenue.avg_revenue_post') }}</h5>
                    <h3>{{ __('admin\financial\golden_post_revenue._40') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\golden_post_revenue.growth_rate') }}</h5>
                    <h3>{{ __('admin\financial\golden_post_revenue._5_1_') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{ __('admin\financial\golden_post_revenue.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{ __('admin\financial\golden_post_revenue.filter') }}</button>
                <button class="btn btn-secondary">{{ __('admin\financial\golden_post_revenue.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Golden Post Revenue Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('admin\financial\golden_post_revenue.date') }}</th>
                        <th>{{ __('admin\financial\golden_post_revenue.user') }}</th>
                        <th>{{ __('admin\financial\golden_post_revenue.post_title') }}</th>
                        <th>{{ __('admin\financial\golden_post_revenue.amount') }}</th>
                        <th>{{ __('admin\financial\golden_post_revenue.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('admin\financial\golden_post_revenue.2024_06_01') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.john_doe') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.golden_car_sale') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue._40') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\financial\golden_post_revenue.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\golden_post_revenue.2024_06_02') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.jane_smith') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.golden_house_listing') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue._60') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\financial\golden_post_revenue.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\golden_post_revenue.2024_06_03') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.ali_hassan') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.golden_job_offer') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue._30') }}</td>
                        <td><span class="badge badge-warning">{{ __('admin\financial\golden_post_revenue.pending') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\golden_post_revenue.2024_06_04') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.maria_garcia') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue.golden_device_sale') }}</td>
                        <td>{{ __('admin\financial\golden_post_revenue._50') }}</td>
                        <td><span class="badge badge-danger">{{ __('admin\financial\golden_post_revenue.failed') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 