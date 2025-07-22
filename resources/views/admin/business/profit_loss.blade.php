@extends('adminlte::page')

@section('title', 'Profit & Loss Analysis')

@section('content_header')
    <h1>{{('admin\business\profit_loss.profit_loss_analysis') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- P&L Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($yearlyRevenue, 2) }}</h3>
                    <p>Total Revenue ({{ $currentYear }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($yearlyExpenses, 2) }}</h3>
                    <p>Total Expenses ({{ $currentYear }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $yearlyProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner">
                    <h3>${{ number_format($yearlyProfit, 2) }}</h3>
                    <p>Net Profit ({{ $currentYear }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($roi, 1) }}%</h3>
                    <p>{{('admin\business\profit_loss.roi_vs_investments') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly P&L Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Profit & Loss ({{ $currentYear }})</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyPLChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- P&L Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.monthly_p_l_breakdown') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="plTable">
                            <thead>
                                <tr>
                                    <th>{{('admin\business\profit_loss.month') }}</th>
                                    <th>{{('admin\business\profit_loss.revenue') }}</th>
                                    <th>{{('admin\business\profit_loss.expenses') }}</th>
                                    <th>{{('admin\business\profit_loss.profit_loss') }}</th>
                                    <th>{{('admin\business\profit_loss.profit_margin') }}</th>
                                    <th>{{('admin\business\profit_loss.trend') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyPL as $pl)
                                <tr>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $pl->id</strong> }}
                                    </td>
                                    <td class="text-success">${{ number_format($pl->revenue, 2) }}</td>
                                    <td class="text-warning">${{ number_format($pl->expenses, 2) }}</td>
                                    <td class="{{ $pl->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($pl->profit, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $pl->revenue > 0 ? ($pl->profit / $pl->revenue * 100 >= 0 ? 'success' : 'danger') : 'secondary' }}">
                                            {{ $pl->revenue > 0 ? number_format($pl->profit / $pl->revenue * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if($pl->profit > 0)
                                            <i class="fas fa-arrow-up text-success"></i>
                                        @elseif($pl->profit < 0)
                                            <i class="fas fa-arrow-down text-danger"></i>
                                        @else
                                            <i class="fas fa-minus text-muted"></i>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{('admin\business\profit_loss.no_p_l_data_available') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.financial_summary') }}</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\profit_loss.best_month') }}</span>
                            <span class="info-box-number">
                                @php
                                    $bestMonth = collect($monthlyPL)->sortByDesc('profit')->first();
                                @endphp
                                {{ $bestMonth ? \Carbon\Carbon::createFromFormat('Y-m', $bestMonth->month)->format('M Y') : 'N/A' }}
                            </span>
                            <span class="info-box-text">
                                ${{ $bestMonth ? number_format($bestMonth->profit, 2) : '0.00' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\profit_loss.worst_month') }}</span>
                            <span class="info-box-number">
                                @php
                                    $worstMonth = collect($monthlyPL)->sortBy('profit')->first();
                                @endphp
                                {{ $worstMonth ? \Carbon\Carbon::createFromFormat('Y-m', $worstMonth->month)->format('M Y') : 'N/A' }}
                            </span>
                            <span class="info-box-text">
                                ${{ $worstMonth ? number_format($worstMonth->profit, 2) : '0.00' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\profit_loss.average_profit_margin') }}</span>
                            <span class="info-box-number">
                                @php
                                    $avgMargin = collect($monthlyPL)->filter(function($pl) { return $pl->revenue > 0; })->avg(function($pl) { return $pl->profit / $pl->revenue * 100; });
                                @endphp
                                {{ number_format($avgMargin ?? 0, 1) }}%
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\profit_loss.total_investment') }}</span>
                            <span class="info-box-number">
                                ${{ number_format($totalInvestments, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue vs Investment Analysis -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.revenue_vs_investment_performance') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueVsInvestmentChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.profitability_trends') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="profitabilityChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.key_performance_indicators') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($yearlyRevenue > 0 ? ($yearlyProfit / $yearlyRevenue * 100) : 0, 1) }}%</h4>
                                <p class="text-muted">{{('admin\business\profit_loss.profit_margin') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ number_format($totalInvestments > 0 ? ($yearlyProfit / $totalInvestments * 100) : 0, 1) }}%</h4>
                                <p class="text-muted">{{('admin\business\profit_loss.return_on_investment') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">${{ number_format($yearlyRevenue > 0 ? ($yearlyRevenue / 12) : 0, 2) }}</h4>
                                <p class="text-muted">{{('admin\business\profit_loss.average_monthly_revenue') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">${{ number_format($yearlyExpenses > 0 ? ($yearlyExpenses / 12) : 0, 2) }}</h4>
                                <p class="text-muted">{{('admin\business\profit_loss.average_monthly_expenses') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\profit_loss.financial_insights_recommendations') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb text-warning"></i> Key Insights</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Total Revenue: ${{ number_format($yearlyRevenue, 2) }}</li>
                                <li><i class="fas fa-check text-success"></i> Total Expenses: ${{ number_format($yearlyExpenses, 2) }}</li>
                                <li><i class="fas fa-check {{ $yearlyProfit >{{('admin\business\profit_loss._0_text_success_text_danger_') }}</i> Net Profit: ${{ number_format($yearlyProfit, 2) }}</li>
                                <li><i class="fas fa-check text-info"></i> ROI: {{ number_format($roi, 1) }}%</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-chart-line text-primary"></i> Recommendations</h5>
                            <ul class="list-unstyled">
                                @if($yearlyProfit < 0)
                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Focus on cost reduction strategies</li>
                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Review pricing strategies</li>
                                @else
                                    <li><i class="fas fa-thumbs-up text-success"></i> Maintain current profitability levels</li>
                                    <li><i class="fas fa-thumbs-up text-success"></i> Consider expansion opportunities</li>
                                @endif
                                @if($roi < 10)
                                    <li><i class="fas fa-exclamation-triangle text-warning"></i> Improve return on investment</li>
                                @else
                                    <li><i class="fas fa-thumbs-up text-success"></i> Strong investment performance</li>
                                @endif
                                <li><i class="fas fa-chart-bar text-info"></i> Monitor monthly trends closely</li>
                            </ul>
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
    .small-box {
        margin-bottom: 20px;
    }
    .card {
        margin-bottom: 20px;
    }
    .info-box {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#plTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[0, "asc"]]
    });

    // Monthly P&L Chart
    const plCtx = document.getElementById('monthlyPLChart').getContext('2d');
    const plChart = new Chart(plCtx, {
        type: 'bar',
        data: {
            labels: @json(collect($monthlyPL)->map(function($pl) { return \Carbon\Carbon::createFromFormat('Y-m', $pl->month)->format('M Y'); })),
            datasets: [{
                label: 'Revenue',
                data: @json(collect($monthlyPL)->pluck('revenue')),
                backgroundColor: '#28a745',
                borderColor: '#28a745',
                borderWidth: 1
            }, {
                label: 'Expenses',
                data: @json(collect($monthlyPL)->pluck('expenses')),
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 1
            }, {
                label: 'Profit/Loss',
                data: @json(collect($monthlyPL)->pluck('profit')),
                backgroundColor: @json(collect($monthlyPL)->map(function($pl) { return $pl->profit >= 0 ? '#28a745' : '#dc3545'; })),
                borderColor: @json(collect($monthlyPL)->map(function($pl) { return $pl->profit >= 0 ? '#28a745' : '#dc3545'; })),
                borderWidth: 1,
                type: 'line',
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });

    // Revenue vs Investment Chart
    const revenueVsInvestmentCtx = document.getElementById('revenueVsInvestmentChart').getContext('2d');
    const revenueVsInvestmentChart = new Chart(revenueVsInvestmentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Revenue', 'Investment'],
            datasets: [{
                data: [{{ $yearlyRevenue }}, {{ $totalInvestments }}],
                backgroundColor: ['#28a745', '#17a2b8'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Profitability Chart
    const profitabilityCtx = document.getElementById('profitabilityChart').getContext('2d');
    const profitabilityChart = new Chart(profitabilityCtx, {
        type: 'line',
        data: {
            labels: @json(collect($monthlyPL)->map(function($pl) { return \Carbon\Carbon::createFromFormat('Y-m', $pl->month)->format('M Y'); })),
            datasets: [{
                label: 'Profit Margin (%)',
                data: @json(collect($monthlyPL)->map(function($pl) { return $pl->revenue > 0 ? ($pl->profit / $pl->revenue * 100) : 0; })),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>
@stop 






