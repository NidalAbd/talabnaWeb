@extends('adminlte::page')

@section('title', 'Revenue Overview')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-dollar-sign text-success mr-2"></i> Revenue Overview</h1>
        <div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Export Report
            </button>
            <button class="btn btn-outline-success ml-2" onclick="refreshData()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Revenue Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\financial\revenue.total_revenue') }}</span>
                    <span class="info-box-number">${{ number_format($totalRevenue, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-{{ $growthRate >{{('admin\financial\revenue._0_arrow_up_text_success_arrow_d') }}</i>
                        {{ number_format(abs($growthRate), 1) }}% from last month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\financial\revenue.total_transactions') }}</span>
                    <span class="info-box-number">{{ number_format($totalTransactions) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 85%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-check-circle text-success"></i>
                        Successful transactions
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\financial\revenue.avg_revenue_user') }}</span>
                    <span class="info-box-number">${{ number_format($avgRevenuePerUser, 2) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 70%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-primary"></i>
                        Per user average
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{('admin\financial\revenue.growth_rate') }}</span>
                    <span class="info-box-number">{{ number_format($growthRate, 1) }}%</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ min(abs($growthRate), 100) }}%"></div>
                    </div>
                    <span class="progress-description">
                        <i class="fas fa-{{ $growthRate >{{('admin\financial\revenue._0_arrow_up_text_success_arrow_d') }}</i>
                        Monthly growth
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-2"></i>
                        Revenue Trend
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Recent Transactions
                    </h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-sm btn-primary">{{('admin\financial\revenue.view_all') }}</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{('admin\financial\revenue.date') }}</th>
                                    <th>{{('admin\financial\revenue.user') }}</th>
                                    <th>{{('admin\financial\revenue.amount') }}</th>
                                    <th>{{('admin\financial\revenue.source') }}</th>
                                    <th>{{('admin\financial\revenue.status') }}</th>
                                    <th>{{('admin\financial\revenue.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                            {{ $transaction->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $transaction->user->photos->first()?->src ?? 'img/default-avatar.png' }}" 
                                                     class="img-circle mr-2" width="30" height="30" alt="User">
                                                <span>{{ $transaction->id</span> }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-success font-weight-bold">
                                                ${{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst($transaction->payment_method ?? 'Online') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($transaction->status === 'approved')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>Completed
                                                </span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i>Pending
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times mr-1"></i>Failed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" title="Export">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="alert alert-info m-0">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                No recent transactions found
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        <a href="#" class="btn btn-primary">{{('admin\financial\revenue.view_all_transactions') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: 0;
    position: relative;
    width: 100%;
}

.info-box .info-box-icon {
    border-radius: 0.25rem 0 0 0.25rem;
    display: flex;
    align-items: center;
    font-size: 1.875rem;
    font-weight: 300;
    justify-content: center;
    text-align: center;
    width: 70px;
    color: #fff;
}

.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box .info-box-text {
    display: block;
    font-size: 1rem;
    font-weight: 400;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box .info-box-number {
    display: block;
    font-weight: 700;
    font-size: 1.25rem;
}

.progress-description {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.table-responsive {
    overflow-x: auto;
}

.img-circle {
    border-radius: 50%;
    object-fit: cover;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.card-tools {
    float: right;
    margin-top: -0.5rem;
}

.card-tools .btn {
    margin-left: 0.25rem;
}
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 13500, 14200, 15800, 16500, {{ $totalRevenue }}],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

function refreshData() {
    location.reload();
}
</script>
@endsection 






