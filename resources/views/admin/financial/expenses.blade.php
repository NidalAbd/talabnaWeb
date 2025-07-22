@extends('adminlte::page')
@section('title', 'Expense Tracking')
@section('content_header')
    <h1><i class="fas fa-receipt text-danger mr-2"></i> Expense Tracking</h1>
@stop
@section('content')
<div class="container-fluid">
    @if(config('app.demo_data', true))
        <div class="alert alert-warning">
            <strong>{{('admin\financial\expenses.demo_data_') }}</strong> This page displays mock data for demonstration purposes. <a href="#" onclick="document.getElementById('demo-data').style.display='none';return false;">{{('admin\financial\expenses.remove_demo_data') }}</a>
        </div>
    @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\expenses.total_expenses') }}</h5>
                    <h3>{{('admin\financial\expenses._8_400') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\expenses.transactions') }}</h5>
                    <h3>{{('admin\financial\expenses.95') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\expenses.pending') }}</h5>
                    <h3>{{('admin\financial\expenses._1_100') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>{{('admin\financial\expenses.approved') }}</h5>
                    <h3>{{('admin\financial\expenses._7_300') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline">
                <label class="mr-2">{{('admin\financial\expenses.date_range_') }}</label>
                <input type="date" class="form-control mr-2" name="from" value="2024-06-01">
                <input type="date" class="form-control mr-2" name="to" value="2024-06-30">
                <button class="btn btn-primary mr-2">{{('admin\financial\expenses.filter') }}</button>
                <button class="btn btn-secondary">{{('admin\financial\expenses.export') }}</button>
            </form>
        </div>
    </div>
    <div class="card" id="demo-data">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i> Expense Transactions</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>{{('admin\financial\expenses.date') }}</th>
                        <th>{{('admin\financial\expenses.category') }}</th>
                        <th>{{('admin\financial\expenses.description') }}</th>
                        <th>{{('admin\financial\expenses.amount') }}</th>
                        <th>{{('admin\financial\expenses.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{('admin\financial\expenses.2024_06_01') }}</td>
                        <td>{{('admin\financial\expenses.marketing') }}</td>
                        <td>{{('admin\financial\expenses.facebook_ads') }}</td>
                        <td>{{('admin\financial\expenses._500') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\expenses.approved') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\expenses.2024_06_02') }}</td>
                        <td>{{('admin\financial\expenses.operations') }}</td>
                        <td>{{('admin\financial\expenses.office_supplies') }}</td>
                        <td>{{('admin\financial\expenses._120') }}</td>
                        <td><span class="badge badge-warning">{{('admin\financial\expenses.pending') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\expenses.2024_06_03') }}</td>
                        <td>{{('admin\financial\expenses.infrastructure') }}</td>
                        <td>{{('admin\financial\expenses.server_hosting') }}</td>
                        <td>{{('admin\financial\expenses._1_200') }}</td>
                        <td><span class="badge badge-success">{{('admin\financial\expenses.approved') }}</span></td>
                    </tr>
                    <tr>
                        <td>{{('admin\financial\expenses.2024_06_04') }}</td>
                        <td>{{('admin\financial\expenses.hr') }}</td>
                        <td>{{('admin\financial\expenses.staff_training') }}</td>
                        <td>{{('admin\financial\expenses._300') }}</td>
                        <td><span class="badge badge-danger">{{('admin\financial\expenses.rejected') }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 






