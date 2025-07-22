@extends('adminlte::page')
@section('title', 'Cash Flow Projections')
@section('content_header')
    <h1><i class="fas fa-chart-area text-info mr-2"></i> Cash Flow Projections</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\financial\cash_flow_projections.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\financial\cash_flow_projections.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\cash_flow_projections.projected_inflow') }}</h5>
                    <h3>{{('admin\financial\cash_flow_projections._15_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\cash_flow_projections.projected_outflow') }}</h5>
                    <h3>{{('admin\financial\cash_flow_projections._10_200') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\cash_flow_projections.net_cash_flow') }}</h5>
                    <h3>{{('admin\financial\cash_flow_projections._4_800') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\cash_flow_projections.projection_period') }}</h5>
                    <h3>{{('admin\financial\cash_flow_projections.q3_2024') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\financial\cash_flow_projections.projection_period_') }}</label>
                <select class="form-control mr-2" name="period">
                    <option>{{('admin\financial\cash_flow_projections.q3_2024') }}</option>
                    <option>{{('admin\financial\cash_flow_projections.q2_2024') }}</option>
                    <option>{{('admin\financial\cash_flow_projections.q1_2024') }}</option>
                </select>
                <button class="btn btn-primary mr-2">{{('admin\financial\cash_flow_projections.filter') }}</button>
                <button class="btn btn-secondary">{{('admin\financial\cash_flow_projections.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Cash Flow Projection Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{('admin\financial\cash_flow_projections.period') }}</th>
                        <th>{{('admin\financial\cash_flow_projections.inflow') }}</th>
                        <th>{{('admin\financial\cash_flow_projections.outflow') }}</th>
                        <th>{{('admin\financial\cash_flow_projections.net_flow') }}</th>
                        <th>{{('admin\financial\cash_flow_projections.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\financial\cash_flow_projections.q3_2024') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._15_000') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._10_200') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._4_800') }}</td>
                        <td>{{('admin\financial\cash_flow_projections.expected_growth_in_premium_sales') }}</td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\cash_flow_projections.q2_2024') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._13_500') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._9_800') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._3_700') }}</td>
                        <td>{{('admin\financial\cash_flow_projections.stable_ad_revenue') }}</td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\cash_flow_projections.q1_2024') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._12_000') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._8_900') }}</td>
                        <td>{{('admin\financial\cash_flow_projections._3_100') }}</td>
                        <td>{{('admin\financial\cash_flow_projections.initial_marketing_push') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






