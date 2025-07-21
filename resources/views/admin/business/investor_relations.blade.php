@extends('adminlte::page')

@section('title', 'Investor Relations')

@section('content_header')
    <h1>Investor Relations</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>${{ number_format($totalInvested, 2) }}</h3>
                    <p>Total Invested</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($totalPaid, 2) }}</h3>
                    <p>Total Paid Back</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($totalRemaining, 2) }}</h3>
                    <p>Remaining to Pay</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $investments->count() }}</h3>
                    <p>Total Investments</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Investor Statistics -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top Investors</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Investor Name</th>
                                    <th>Investments</th>
                                    <th>Total Invested</th>
                                    <th>Average Investment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($investorStats as $stat)
                                <tr>
                                    <td>{{ $stat->investor_name }}</td>
                                    <td>{{ $stat->investment_count }}</td>
                                    <td>${{ number_format($stat->total_invested, 2) }}</td>
                                    <td>${{ number_format($stat->avg_investment, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No investor data available</td>
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
                    <h3 class="card-title">Investment Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="investmentChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- All Investments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Investments</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addInvestmentModal">
                            <i class="fas fa-plus"></i> Add Investment
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="investmentsTable">
                            <thead>
                                <tr>
                                    <th>Investor</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Purpose</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Progress</th>
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
                                    <td>{{ $investment->purpose }}</td>
                                    <td>{{ $investment->investment_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $investment->status == 'active' ? 'success' : ($investment->status == 'completed' ? 'info' : 'warning') }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" style="width: {{ $investment->progress_percentage }}%"></div>
                                        </div>
                                        <small>{{ $investment->progress_percentage }}%</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewInvestmentModal{{ $investment->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editInvestmentModal{{ $investment->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addPaymentModal{{ $investment->id }}">
                                                <i class="fas fa-dollar-sign"></i>
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
</div>

<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Investment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addInvestmentForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investor Name *</label>
                                <input type="text" name="investor_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investor Email *</label>
                                <input type="email" name="investor_email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investment Amount *</label>
                                <input type="number" name="investment_amount" class="form-control" step="0.01" required>
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
                                <label>Investment Type *</label>
                                <select name="investment_type" class="form-control" required>
                                    <option value="equity">Equity</option>
                                    <option value="loan">Loan</option>
                                    <option value="grant">Grant</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investment Date *</label>
                                <input type="date" name="investment_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Expected ROI (%) *</label>
                                <input type="number" name="expected_roi" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investment Period (Months) *</label>
                                <input type="number" name="investment_period" class="form-control" min="1" max="60" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profit Sharing - Investor (%) *</label>
                                <input type="number" name="investor_share" class="form-control" min="0" max="100" value="55" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profit Sharing - Owner (%) *</label>
                                <input type="number" name="owner_share" class="form-control" min="0" max="100" value="45" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Purpose</label>
                                <select name="purpose" class="form-control">
                                    <option value="license">License</option>
                                    <option value="advertising">Advertising</option>
                                    <option value="salary">Salary</option>
                                    <option value="development">Development</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="operations">Operations</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Agreement Terms</label>
                                <select name="agreement_terms" class="form-control">
                                    <option value="standard">Standard (55% Investor, 45% Owner)</option>
                                    <option value="custom">Custom Agreement</option>
                                    <option value="equity">Equity Based</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Investment</button>
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
    .progress-sm {
        height: 5px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Set default date to today
    $('input[name="investment_date"]').val(new Date().toISOString().split('T')[0]);

    // Initialize DataTable
    $('#investmentsTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[4, "desc"]]
    });

    // Investment Distribution Chart
    const investmentCtx = document.getElementById('investmentChart').getContext('2d');
    const investmentChart = new Chart(investmentCtx, {
        type: 'doughnut',
        data: {
            labels: @json($investorStats->pluck('investor_name')),
            datasets: [{
                data: @json($investorStats->pluck('total_invested')),
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d',
                    '#17a2b8', '#fd7e14', '#6f42c1', '#e83e8c', '#20c997'
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

    // Handle investment form submission
    $('#addInvestmentForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("business.investments.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Investment Added Successfully!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        $('#addInvestmentModal').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'Please fix the following errors:\n';
                for (let field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error Adding Investment',
                    text: errorMessage
                });
            }
        });
    });

    // Auto-calculate owner share when investor share changes
    $('input[name="investor_share"]').on('input', function() {
        const investorShare = parseInt($(this).val()) || 0;
        const ownerShare = 100 - investorShare;
        $('input[name="owner_share"]').val(ownerShare);
    });

    // Auto-calculate investor share when owner share changes
    $('input[name="owner_share"]').on('input', function() {
        const ownerShare = parseInt($(this).val()) || 0;
        const investorShare = 100 - ownerShare;
        $('input[name="investor_share"]').val(investorShare);
    });

    // Handle agreement terms change
    $('select[name="agreement_terms"]').change(function() {
        const terms = $(this).val();
        if (terms === 'standard') {
            $('input[name="investor_share"]').val(55);
            $('input[name="owner_share"]').val(45);
        }
    });
});
</script>
@stop 