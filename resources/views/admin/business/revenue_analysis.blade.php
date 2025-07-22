@extends('adminlte::page')

@section('title', 'Revenue Analysis')

@section('content_header')
    <h1>{{('admin\business\revenue_analysis.revenue_analysis') }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Revenue Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($revenueBySource->sum('total'), 2) }}</h3>
                    <p>{{('admin\business\revenue_analysis.total_revenue') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $pointSales->id }}</h3>
                    <p>{{('admin\business\revenue_analysis.point_sales') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pointPackages->id</h3> }}
                    <p>{{('admin\business\revenue_analysis.active_packages') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $pointSales->id</h3> }}
                    <p>{{('admin\business\revenue_analysis.unique_customers') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.monthly_revenue_trend') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.revenue_by_source') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueSourceChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Point Sales Analysis -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.top_selling_point_packages') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{('admin\business\revenue_analysis.package') }}</th>
                                    <th>{{('admin\business\revenue_analysis.points') }}</th>
                                    <th>{{('admin\business\revenue_analysis.price') }}</th>
                                    <th>{{('admin\business\revenue_analysis.sales_count') }}</th>
                                    <th>{{('admin\business\revenue_analysis.total_revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointPackages as $package)
                                <tr>
                                    <td>
                                        <strong>{{ $package->id }}</strong><br>
                                        <small class="text-muted">{{ $package->id }}</small>
                                    </td>
                                    <td>{{ number_format($package->id) }}</td>
                                    <td>${{ number_format($package->price, 2) }}</td>
                                    <td>{{ $package->id</td> }}
                                    <td>${{ number_format($package->sales->count() * $package->price, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{('admin\business\revenue_analysis.no_packages_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.recent_point_sales') }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{('admin\business\revenue_analysis.customer') }}</th>
                                    <th>{{('admin\business\revenue_analysis.package') }}</th>
                                    <th>{{('admin\business\revenue_analysis.points') }}</th>
                                    <th>{{('admin\business\revenue_analysis.amount') }}</th>
                                    <th>{{('admin\business\revenue_analysis.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointSales->take(10) as $sale)
                                <tr>
                                    <td>
                                        <strong>{{ $sale->id }}</strong><br>
                                        <small class="text-muted">{{ $sale->id }}</small>
                                    </td>
                                    <td>{{ $sale->id</td> }}
                                    <td>{{ number_format($sale->id</td> ) }}
                                    <td>${{ number_format($sale->package->price ?? 0, 2) }}</td>
                                    <td>{{ $sale->id</td> }}
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{('admin\business\revenue_analysis.no_recent_sales') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Details -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.all_revenue_sources') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addRevenueModal">
                            <i class="fas fa-plus"></i> Add Revenue
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="revenueTable">
                            <thead>
                                <tr>
                                    <th>{{('admin\business\revenue_analysis.source') }}</th>
                                    <th>{{('admin\business\revenue_analysis.description') }}</th>
                                    <th>{{('admin\business\revenue_analysis.amount') }}</th>
                                    <th>{{('admin\business\revenue_analysis.type') }}</th>
                                    <th>{{('admin\business\revenue_analysis.date') }}</th>
                                    <th>{{('admin\business\revenue_analysis.status') }}</th>
                                    <th>{{('admin\business\revenue_analysis.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueBySource as $revenue)
                                <tr>
                                    <td>{{ $revenue->id</td> }}
                                    <td>{{ Str::limit($revenue->id</td> }}
                                    <td>${{ number_format($revenue->total, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $revenue->revenue_type == 'point_sales' ? 'success' : ($revenue->revenue_type == 'advertising' ? 'info' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $revenue->revenue_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $revenue->id</td> }}
                                    <td>
                                        <span class="badge badge-{{ $revenue->status == 'received' ? 'success' : ($revenue->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($revenue->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewRevenueModal{{ $revenue->count() }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editRevenueModal{{ $revenue->count() }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{('admin\business\revenue_analysis.no_revenue_data_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Insights -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.revenue_insights') }}</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\revenue_analysis.best_performing_source') }}</span>
                            <span class="info-box-number">
                                {{ $revenueBySource->sortByDesc('total')->first()->revenue_type ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\revenue_analysis.this_month_revenue') }}</span>
                            <span class="info-box-number">
                                ${{ number_format($monthlyRevenueTrend->where('month', now()->format('Y-m'))->first()->total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\business\revenue_analysis.average_revenue_per_customer') }}</span>
                            <span class="info-box-number">
                                ${{ number_format($pointSales->unique('from_user_id')->count() > 0 ? ($revenueBySource->sum('total') / $pointSales->unique('from_user_id')->count()) : 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{('admin\business\revenue_analysis.revenue_growth_analysis') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="growthChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Revenue Modal -->
<div class="modal fade" id="addRevenueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{('admin\business\revenue_analysis.add_revenue_entry') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>{{('admin\business\revenue_analysis._times_') }}</span>
                </button>
            </div>
            <form action="{{ route('business.revenue.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.revenue_title') }}</label>
                                <input type="text" name="revenue_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.revenue_type') }}</label>
                                <select name="revenue_type" class="form-control" required>
                                    <option value="point_sales">{{('admin\business\revenue_analysis.point_sales') }}</option>
                                    <option value="advertising">{{('admin\business\revenue_analysis.advertising') }}</option>
                                    <option value="premium_features">{{('admin\business\revenue_analysis.premium_features') }}</option>
                                    <option value="other">{{('admin\business\revenue_analysis.other') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.amount') }}</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.currency') }}</label>
                                <select name="currency" class="form-control">
                                    <option value="USD">{{('admin\business\revenue_analysis.usd') }}</option>
                                    <option value="EUR">{{('admin\business\revenue_analysis.eur') }}</option>
                                    <option value="GBP">{{('admin\business\revenue_analysis.gbp') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.revenue_date') }}</label>
                                <input type="date" name="revenue_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\business\revenue_analysis.payment_method') }}</label>
                                <select name="payment_method" class="form-control">
                                    <option value="credit_card">{{('admin\business\revenue_analysis.credit_card') }}</option>
                                    <option value="bank_transfer">{{('admin\business\revenue_analysis.bank_transfer') }}</option>
                                    <option value="paypal">{{('admin\business\revenue_analysis.paypal') }}</option>
                                    <option value="cash">{{('admin\business\revenue_analysis.cash') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{('admin\business\revenue_analysis.description') }}</label>
                        <textarea name="revenue_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{('admin\business\revenue_analysis.customer_name') }}</label>
                        <input type="text" name="customer_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{('admin\business\revenue_analysis.invoice_number') }}</label>
                        <input type="text" name="invoice_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{('admin\business\revenue_analysis.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{('admin\business\revenue_analysis.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{('admin\business\revenue_analysis.add_revenue') }}</button>
                </div>
            </form>
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
    $('#revenueTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[4, "desc"]]
    });

    // Revenue Trend Chart
    const trendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyRevenueTrend->pluck('month')),
            datasets: [{
                label: 'Revenue',
                data: @json($monthlyRevenueTrend->pluck('total')),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
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

    // Revenue Source Chart
    const sourceCtx = document.getElementById('revenueSourceChart').getContext('2d');
    const sourceChart = new Chart(sourceCtx, {
        type: 'doughnut',
        data: {
            labels: @json($revenueBySource->pluck('revenue_type')),
            datasets: [{
                data: @json($revenueBySource->pluck('total')),
                backgroundColor: [
                    '#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6c757d',
                    '#fd7e14', '#6f42c1', '#e83e8c', '#20c997', '#6610f2'
                ],
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

    // Growth Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: @json($monthlyRevenueTrend->pluck('month')),
            datasets: [{
                label: 'Revenue Growth',
                data: @json($monthlyRevenueTrend->pluck('total')),
                backgroundColor: '#007bff',
                borderColor: '#007bff',
                borderWidth: 1
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
});
</script>
@stop 






