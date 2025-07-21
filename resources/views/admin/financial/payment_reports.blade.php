@extends('adminlte::page')
@section('title', 'Payment Reports')
@section('content_header')
    <h1><i class="fas fa-credit-card text-info mr-2"></i> Payment Reports</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{ __('admin\financial\payment_reports.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{ __('admin\financial\payment_reports.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\payment_reports.total_payments') }}</h5>
                    <h3>{{ __('admin\financial\payment_reports._15_000') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\payment_reports.completed') }}</h5>
                    <h3>{{ __('admin\financial\payment_reports._13_500') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\payment_reports.pending') }}</h5>
                    <h3>{{ __('admin\financial\payment_reports._1_200') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{ __('admin\financial\payment_reports.failed') }}</h5>
                    <h3>{{ __('admin\financial\payment_reports._300') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{ __('admin\financial\payment_reports.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{ __('admin\financial\payment_reports.filter') }}</button>
                <button class="btn btn-secondary">{{ __('admin\financial\payment_reports.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Payment Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ __('admin\financial\payment_reports.date') }}</th>
                        <th>{{ __('admin\financial\payment_reports.user') }}</th>
                        <th>{{ __('admin\financial\payment_reports.amount') }}</th>
                        <th>{{ __('admin\financial\payment_reports.method') }}</th>
                        <th>{{ __('admin\financial\payment_reports.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('admin\financial\payment_reports.2024_06_01') }}</td>
                        <td>{{ __('admin\financial\payment_reports.john_doe') }}</td>
                        <td>{{ __('admin\financial\payment_reports._120') }}</td>
                        <td>{{ __('admin\financial\payment_reports.credit_card') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\financial\payment_reports.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\payment_reports.2024_06_02') }}</td>
                        <td>{{ __('admin\financial\payment_reports.jane_smith') }}</td>
                        <td>{{ __('admin\financial\payment_reports._250') }}</td>
                        <td>{{ __('admin\financial\payment_reports.paypal') }}</td>
                        <td><span class="badge badge-success">{{ __('admin\financial\payment_reports.completed') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\payment_reports.2024_06_03') }}</td>
                        <td>{{ __('admin\financial\payment_reports.ali_hassan') }}</td>
                        <td>{{ __('admin\financial\payment_reports._75') }}</td>
                        <td>{{ __('admin\financial\payment_reports.bank_transfer') }}</td>
                        <td><span class="badge badge-warning">{{ __('admin\financial\payment_reports.pending') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('admin\financial\payment_reports.2024_06_04') }}</td>
                        <td>{{ __('admin\financial\payment_reports.maria_garcia') }}</td>
                        <td>{{ __('admin\financial\payment_reports._300') }}</td>
                        <td>{{ __('admin\financial\payment_reports.credit_card') }}</td>
                        <td><span class="badge badge-danger">{{ __('admin\financial\payment_reports.failed') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 