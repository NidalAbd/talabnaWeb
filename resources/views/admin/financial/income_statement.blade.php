@extends('adminlte::page')
@section('title', 'Income Statement')
@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Income Statement</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{ __('admin\financial\income_statement.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{ __('admin\financial\income_statement.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\income_statement.total_revenue') }}</h5>
                    <h3>{{ __('admin\financial\income_statement._45_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\income_statement.total_expenses') }}</h5>
                    <h3>{{ __('admin\financial\income_statement._32_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\income_statement.net_income') }}</h5>
                    <h3>{{ __('admin\financial\income_statement._13_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\income_statement.reporting_period') }}</h5>
                    <h3>{{ __('admin\financial\income_statement.2024_ytd') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{ __('admin\financial\income_statement.period_') }}</label>
                <select class="form-control mr-2" name="period">
                    <option>{{ __('admin\financial\income_statement.2024_ytd') }}</option>
                    <option>{{ __('admin\financial\income_statement.2023') }}</option>
                    <option>{{ __('admin\financial\income_statement.2022') }}</option>
                </select>
                <button class="btn btn-primary mr-2">{{ __('admin\financial\income_statement.filter') }}</button>
                <button class="btn btn-secondary">{{ __('admin\financial\income_statement.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Income Statement Table</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('admin\financial\income_statement.period') }}</th>
                        <th>{{ __('admin\financial\income_statement.revenue') }}</th>
                        <th>{{ __('admin\financial\income_statement.cogs') }}</th>
                        <th>{{ __('admin\financial\income_statement.gross_profit') }}</th>
                        <th>{{ __('admin\financial\income_statement.expenses') }}</th>
                        <th>{{ __('admin\financial\income_statement.net_income') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('admin\financial\income_statement.2024_ytd') }}</td>
                        <td>{{ __('admin\financial\income_statement._45_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._12_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._33_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._20_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._13_000') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\income_statement.2023') }}</td>
                        <td>{{ __('admin\financial\income_statement._38_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._10_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._28_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._17_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._11_000') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\income_statement.2022') }}</td>
                        <td>{{ __('admin\financial\income_statement._32_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._8_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._24_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._15_000') }}</td>
                        <td>{{ __('admin\financial\income_statement._9_000') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 