@extends('adminlte::page')

@section('title', 'Investment Tracking')

@section('content_header')
    <h1>Investment Tracking</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- ROI Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $investments->count() }}</h3>
                    <p>Active Investments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $pendingPayments->count() }}</h3>
                    <p>Pending Payments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $upcomingPayments->count() }}</h3>
                    <p>Upcoming Payments (30 days)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($investments->avg('roi'), 1) }}%</h3>
                    <p>Average ROI</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ROI Performance Chart -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ROI Performance by Investment</h3>
                </div>
                <div class="card-body">
                    <canvas id="roiChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Investment Status Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Payments -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upcoming Payments (Next 30 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Investor</th>
                                    <th>Investment Amount</th>
                                    <th>Next Payment Date</th>
                                    <th>Payment Amount</th>
                                    <th>Payment Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingPayments as $payment)
                                <tr>
                                    <td>{{ $payment->investor_name }}</td>
                                    <td>${{ number_format($payment->investment_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->next_payment_date->diffInDays(now()) <= 7 ? 'danger' : 'warning' }}">
                                            {{ $payment->next_payment_date->format('M d, Y') }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($payment->expected_return, 2) }}</td>
                                    <td>{{ ucfirst($payment->investment_type) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#makePaymentModal{{ $payment->id }}">
                                            <i class="fas fa-dollar-sign"></i> Make Payment
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No upcoming payments</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Investments with ROI -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Investment Portfolio with ROI</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="investmentsTable">
                            <thead>
                                <tr>
                                    <th>Investor</th>
                                    <th>Investment</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Total Paid</th>
                                    <th>ROI</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investments as $investment)
                                <tr>
                                    <td>
                                        <strong>{{ $investment->investor_name }}</strong><br>
                                        <small class="text-muted">{{ $investment->investor_email }}</small>
                                    </td>
                                    <td>${{ number_format($investment->investment_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $investment->investment_type == 'equity' ? 'primary' : ($investment->investment_type == 'loan' ? 'warning' : 'info') }}">
                                            {{ ucfirst($investment->investment_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $investment->investment_date->format('M d, Y') }}</td>
                                    <td>${{ number_format($investment->payments->sum('payment_amount'), 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $investment->roi >= 0 ? 'success' : 'danger' }}">
                                            {{ $investment->roi }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $investment->status == 'active' ? 'success' : ($investment->status == 'completed' ? 'info' : 'warning') }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewInvestmentModal{{ $investment->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addPaymentModal{{ $investment->id }}">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editInvestmentModal{{ $investment->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No investments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Payment History</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Investor</th>
                                    <th>Payment Amount</th>
                                    <th>Payment Type</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPayments as $payment)
                                <tr>
                                    <td>{{ $payment->investment->investor_name }}</td>
                                    <td>${{ number_format($payment->payment_amount, 2) }}</td>
                                    <td>{{ ucfirst($payment->payment_type) }}</td>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No recent payments</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('business.payments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Investment</label>
                        <select name="investment_id" class="form-control" required>
                            @foreach($investments as $investment)
                            <option value="{{ $investment->id }}">{{ $investment->investor_name }} - ${{ number_format($investment->investment_amount, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Amount</label>
                        <input type="number" name="payment_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Type</label>
                        <select name="payment_type" class="form-control" required>
                            <option value="return">Return</option>
                            <option value="dividend">Dividend</option>
                            <option value="interest">Interest</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
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
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#investmentsTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[5, "desc"]]
    });

    // ROI Performance Chart
    const roiCtx = document.getElementById('roiChart').getContext('2d');
    const roiChart = new Chart(roiCtx, {
        type: 'bar',
        data: {
            labels: @json($investments->pluck('investor_name')),
            datasets: [{
                label: 'ROI (%)',
                data: @json($investments->pluck('roi')),
                backgroundColor: @json($investments->map(function($inv) { return $inv->roi >= 0 ? '#28a745' : '#dc3545'; })),
                borderColor: @json($investments->map(function($inv) { return $inv->roi >= 0 ? '#28a745' : '#dc3545'; })),
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
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Investment Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = {
        active: {{ $investments->where('status', 'active')->count() }},
        completed: {{ $investments->where('status', 'completed')->count() }},
        pending: {{ $investments->where('status', 'pending')->count() }}
    };
    
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Completed', 'Pending'],
            datasets: [{
                data: [statusData.active, statusData.completed, statusData.pending],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
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
});
</script>
@stop 