@extends('adminlte::page')

@section('title', 'Revenue Analysis')

@section('content_header')
    <h1>Revenue Analysis</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Revenue Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($revenueBySource->sum('total'), 2) }}</h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $pointSales->count() }}</h3>
                    <p>Point Sales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pointPackages->count() }}</h3>
                    <p>Active Packages</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $pointSales->unique('from_user_id')->count() }}</h3>
                    <p>Unique Customers</p>
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
                    <h3 class="card-title">Monthly Revenue Trend</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue by Source</h3>
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
                    <h3 class="card-title">Top Selling Point Packages</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Points</th>
                                    <th>Price</th>
                                    <th>Sales Count</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointPackages as $package)
                                <tr>
                                    <td>
                                        <strong>{{ $package->name['en'] ?? $package->name }}</strong><br>
                                        <small class="text-muted">{{ $package->description['en'] ?? $package->description }}</small>
                                    </td>
                                    <td>{{ number_format($package->points_amount) }}</td>
                                    <td>${{ number_format($package->price, 2) }}</td>
                                    <td>{{ $package->sales->count() }}</td>
                                    <td>${{ number_format($package->sales->count() * $package->price, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No packages found</td>
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
                    <h3 class="card-title">Recent Point Sales</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Package</th>
                                    <th>Points</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pointSales->take(10) as $sale)
                                <tr>
                                    <td>
                                        <strong>{{ $sale->fromUser->name ?? 'Unknown' }}</strong><br>
                                        <small class="text-muted">{{ $sale->fromUser->email ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $sale->package->name['en'] ?? $sale->package->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($sale->point) }}</td>
                                    <td>${{ number_format($sale->package->price ?? 0, 2) }}</td>
                                    <td>{{ $sale->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent sales</td>
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
                    <h3 class="card-title">All Revenue Sources</h3>
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
                                    <th>Source</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueBySource as $revenue)
                                <tr>
                                    <td>{{ $revenue->revenue_title }}</td>
                                    <td>{{ Str::limit($revenue->revenue_description, 50) }}</td>
                                    <td>${{ number_format($revenue->total, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $revenue->revenue_type == 'point_sales' ? 'success' : ($revenue->revenue_type == 'advertising' ? 'info' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $revenue->revenue_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $revenue->revenue_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $revenue->status == 'received' ? 'success' : ($revenue->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($revenue->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewRevenueModal{{ $revenue->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editRevenueModal{{ $revenue->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No revenue data found</td>
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
                    <h3 class="card-title">Revenue Insights</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Best Performing Source</span>
                            <span class="info-box-number">
                                {{ $revenueBySource->sortByDesc('total')->first()->revenue_type ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Month Revenue</span>
                            <span class="info-box-number">
                                ${{ number_format($monthlyRevenueTrend->where('month', now()->format('Y-m'))->first()->total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Revenue per Customer</span>
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
                    <h3 class="card-title">Revenue Growth Analysis</h3>
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
                <h5 class="modal-title">Add Revenue Entry</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('business.revenue.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Revenue Title</label>
                                <input type="text" name="revenue_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Revenue Type</label>
                                <select name="revenue_type" class="form-control" required>
                                    <option value="point_sales">Point Sales</option>
                                    <option value="advertising">Advertising</option>
                                    <option value="premium_features">Premium Features</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" name="amount" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Revenue Date</label>
                                <input type="date" name="revenue_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="revenue_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Revenue</button>
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