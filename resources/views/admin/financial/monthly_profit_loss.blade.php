@extends('adminlte::page')
@section('title', 'Monthly Profit & Loss')
@section('content_header')
    <h1><i class="fas fa-chart-pie text-success mr-2"></i> Monthly Profit & Loss</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{ __('admin\financial\monthly_profit_loss.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{ __('admin\financial\monthly_profit_loss.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\monthly_profit_loss.total_profit') }}</h5>
                    <h3>{{ __('admin\financial\monthly_profit_loss._4_200') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\monthly_profit_loss.total_loss') }}</h5>
                    <h3>{{ __('admin\financial\monthly_profit_loss._1_100') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\monthly_profit_loss.net_profit') }}</h5>
                    <h3>{{ __('admin\financial\monthly_profit_loss._3_100') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\monthly_profit_loss.profit_margin') }}</h5>
                    <h3>{{ __('admin\financial\monthly_profit_loss.28_2_') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{ __('admin\financial\monthly_profit_loss.month_') }}</label>
                <select class="form-control mr-2" name="month">
                    <option>{{ __('admin\financial\monthly_profit_loss.june_2024') }}</option>
                    <option>{{ __('admin\financial\monthly_profit_loss.may_2024') }}</option>
                    <option>{{ __('admin\financial\monthly_profit_loss.april_2024') }}</option>
                </select>
                <button class="btn btn-primary mr-2">{{ __('admin\financial\monthly_profit_loss.filter') }}</button>
                <button class="btn btn-secondary">{{ __('admin\financial\monthly_profit_loss.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Monthly Profit & Loss Records</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('admin\financial\monthly_profit_loss.month') }}</th>
                        <th>{{ __('admin\financial\monthly_profit_loss.revenue') }}</th>
                        <th>{{ __('admin\financial\monthly_profit_loss.expenses') }}</th>
                        <th>{{ __('admin\financial\monthly_profit_loss.profit') }}</th>
                        <th>{{ __('admin\financial\monthly_profit_loss.loss') }}</th>
                        <th>{{ __('admin\financial\monthly_profit_loss.net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('admin\financial\monthly_profit_loss.june_2024') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._12_500') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._9_400') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._4_200') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._1_100') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._3_100') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\monthly_profit_loss.may_2024') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._11_800') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._8_900') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._3_900') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._1_000') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._2_900') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\monthly_profit_loss.april_2024') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._10_900') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._8_200') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._3_200') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._900') }}</td>
                        <td>{{ __('admin\financial\monthly_profit_loss._2_300') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 