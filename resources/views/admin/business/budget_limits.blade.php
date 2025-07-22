@extends('adminlte::page')

@section('title', 'Budget Limits')

@section('content_header')
    <h1>{{('admin\business\budget_limits.budget_limits_monitoring') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Over Budget Alerts -->
    @if($overBudgetBudgets->count() > 0)
    <div class="alert alert-danger">
        <h5><i class="fas fa-exclamation-triangle"></i> Over Budget Alerts</h5>
        <p>{{('admin\business\budget_limits.the_following_budgets_have_exceeded_thei') }}</p>
    </div>
    @endif

    <!-- Near Limit Warnings -->
    @if($nearLimitBudgets->count() > 0)
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-circle"></i> Near Limit Warnings</h5>
        <p>{{('admin\business\budget_limits.the_following_budgets_are_approaching_th') }}</p>
    </div>
    @endif

    <!-- Budget Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\budget_limits.budget_utilization_overview') }}</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{('admin\business\budget_limits.budget_title') }}</th>
                                    <th>{{('admin\business\budget_limits.category') }}</th>
                                    <th>{{('admin\business\budget_limits.total_budget') }}</th>
                                    <th>{{('admin\business\budget_limits.spent_amount') }}</th>
                                    <th>{{('admin\business\budget_limits.remaining') }}</th>
                                    <th>{{('admin\business\budget_limits.utilization') }}</th>
                                    <th>{{('admin\business\budget_limits.status') }}</th>
                                    <th>{{('admin\business\budget_limits.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                <tr class="{{ $budget->spent_amount > $budget->total_budget ? 'table-danger' : ($budget->utilization_percentage >= 80 ? 'table-warning' : '') }}">
                                    <td>{{ $budget->title }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($budget->category) }}</span>
                                    </td>
                                    <td>${{ number_format($budget->total_budget, 2) }}</td>
                                    <td>${{ number_format($budget->spent_amount, 2) }}</td>
                                    <td>${{ number_format($budget->remaining_amount, 2) }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar {{ $budget->utilization_percentage >= 100 ? 'bg-danger' : ($budget->utilization_percentage >= 80 ? 'bg-warning' : 'bg-success') }}" 
                                                 style="width: {{ min($budget->utilization_percentage, 100) }}%">
                                                {{ number_format($budget->utilization_percentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($budget->spent_amount > $budget->total_budget)
                                            <span class="badge badge-danger">{{('admin\business\budget_limits.over_budget') }}</span>
                                        @elseif($budget->utilization_percentage >= 80)
                                            <span class="badge badge-warning">{{('admin\business\budget_limits.near_limit') }}</span>
                                        @else
                                            <span class="badge badge-success">{{('admin\business\budget_limits.healthy') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info view-budget" data-id="{{ $budget->id }}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @if($budget->spent_amount > $budget->total_budget)
                                            <button class="btn btn-sm btn-warning increase-budget" data-id="{{ $budget->id }}">
                                                <i class="fas fa-plus"></i> Increase
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Recommendations -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\budget_limits.budget_recommendations') }}</h3>
                </div>
                <div class="card-body">
                    @if($overBudgetBudgets->count() > 0)
                        <div class="alert alert-danger">
                            <h6>{{('admin\business\budget_limits.immediate_actions_required_') }}</h6>
                            <ul>
                                @foreach($overBudgetBudgets as $budget)
                                    <li><strong>{{ $budget->title }}</strong> is over budget by ${{ number_format($budget->spent_amount - $budget->total_budget, 2) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($nearLimitBudgets->count() > 0)
                        <div class="alert alert-warning">
                            <h6>{{('admin\business\budget_limits.monitor_closely_') }}</h6>
                            <ul>
                                @foreach($nearLimitBudgets as $budget)
                                    <li><strong>{{ $budget->title }}</strong> is at {{ number_format($budget->utilization_percentage, 1) }}% utilization</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($overBudgetBudgets->count() == 0 && $nearLimitBudgets->count() == 0)
                        <div class="alert alert-success">
                            <h6>{{('admin\business\budget_limits.all_budgets_are_within_healthy_limits_') }}</h6>
                            <p>{{('admin\business\budget_limits.no_immediate_action_required_') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\budget_limits.budget_statistics') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{('admin\business\budget_limits.over_budget') }}</span>
                                    <span class="info-box-number">{{ $overBudgetBudgets->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{('admin\business\budget_limits.near_limit') }}</span>
                                    <span class="info-box-number">{{ $nearLimitBudgets->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{('admin\business\budget_limits.healthy') }}</span>
                                    <span class="info-box-number">{{ $budgets->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-pie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{('admin\business\budget_limits.total_budgets') }}</span>
                                    <span class="info-box-number">{{ $budgets->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .progress {
        height: 20px;
    }
    .progress-bar {
        line-height: 20px;
        font-size: 12px;
    }
    .info-box {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // View budget details
    $('.view-budget').click(function() {
        const budgetId = $(this).data('id');
        // Implement view budget functionality
        alert('View budget details for ID: ' + budgetId);
    });

    // Increase budget
    $('.increase-budget').click(function() {
        const budgetId = $(this).data('id');
        const newAmount = prompt('Enter new budget amount:');
        if (newAmount && !isNaN(newAmount)) {
            // Implement increase budget functionality
            alert('Budget increased for ID: ' + budgetId + ' to $' + newAmount);
        }
    });
});
</script>
@stop 






