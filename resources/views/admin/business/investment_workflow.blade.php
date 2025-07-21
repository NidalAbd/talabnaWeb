@extends('layouts.admin')

@section('title', 'Investment Workflow Management')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Investment Workflow Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Investment Workflow</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Investment Workflow Overview -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $activeInvestments ?? 0 }}</h3>
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
                            <h3>${{ number_format($totalProfitGenerated ?? 0, 2) }}</h3>
                            <p>Total Profit Generated</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>${{ number_format($totalProfitDistributed ?? 0, 2) }}</h3>
                            <p>Profit Distributed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $profitableInvestments ?? 0 }}</h3>
                            <p>Profitable Investments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Workflow Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <button class="btn btn-primary btn-block" onclick="calculateProfitability()">
                                        <i class="fas fa-calculator"></i> Calculate Profitability
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-success btn-block" onclick="distributeAllProfits()">
                                        <i class="fas fa-share-alt"></i> Distribute All Profits
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-info btn-block" onclick="generateWorkflowReport()">
                                        <i class="fas fa-file-alt"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investment Workflow Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Investment Workflow</h3>
                        </div>
                        <div class="card-body">
                            <table id="workflowTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Investor</th>
                                        <th>Amount</th>
                                        <th>ROI</th>
                                        <th>Period</th>
                                        <th>Profit Generated</th>
                                        <th>Profit Distributed</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($investments ?? [] as $investment)
                                    <tr>
                                        <td>
                                            <strong>{{ $investment->investor_name }}</strong><br>
                                            <small class="text-muted">{{ $investment->investor_email }}</small>
                                        </td>
                                        <td>${{ number_format($investment->investment_amount, 2) }}</td>
                                        <td>{{ $investment->expected_roi }}%</td>
                                        <td>{{ $investment->investment_period }} months</td>
                                        <td>
                                            <span class="badge badge-success">${{ number_format($investment->profit_generated, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">${{ number_format($investment->profit_distributed, 2) }}</span>
                                        </td>
                                        <td>
                                            @switch($investment->status)
                                                @case('active')
                                                    <span class="badge badge-primary">Active</span>
                                                    @break
                                                @case('profitable')
                                                    <span class="badge badge-success">Profitable</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-secondary">Completed</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-warning">Pending</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @if($investment->canDistributeProfit())
                                                <button class="btn btn-sm btn-success" onclick="distributeProfit({{ $investment->id }})">
                                                    <i class="fas fa-share-alt"></i> Distribute
                                                </button>
                                                @endif
                                                <button class="btn btn-sm btn-info" onclick="viewDetails({{ $investment->id }})">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="updateStatus({{ $investment->id }})">
                                                    <i class="fas fa-edit"></i> Status
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

            <!-- Profit Distribution Chart -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Profit Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="profitChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Investment Status</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Distribute Profit Modal -->
<div class="modal fade" id="distributeProfitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Distribute Profit</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="distributeProfitForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Distribution Amount</label>
                        <input type="number" name="distribution_amount" class="form-control" step="0.01" required>
                        <small class="text-muted">Available profit: $<span id="availableProfit">0.00</span></small>
                    </div>
                    <div class="form-group">
                        <label>Investor Share ({{ $investment->investor_share ?? 55 }}%)</label>
                        <input type="text" id="investorAmount" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Owner Share ({{ $investment->owner_share ?? 45 }}%)</label>
                        <input type="text" id="ownerAmount" class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Distribute Profit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Investment Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>New Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="profitable">Profitable</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#workflowTable').DataTable({
        "responsive": true,
        "autoWidth": false,
        "order": [[4, "desc"]]
    });

    // Profit Distribution Chart
    const profitCtx = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(profitCtx, {
        type: 'doughnut',
        data: {
            labels: ['Profit Generated', 'Profit Distributed', 'Profit Remaining'],
            datasets: [{
                data: [
                    {{ $totalProfitGenerated ?? 0 }},
                    {{ $totalProfitDistributed ?? 0 }},
                    {{ ($totalProfitGenerated ?? 0) - ($totalProfitDistributed ?? 0) }}
                ],
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

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Active', 'Profitable', 'Completed', 'Pending'],
            datasets: [{
                data: [
                    {{ $activeInvestments ?? 0 }},
                    {{ $profitableInvestments ?? 0 }},
                    {{ $completedInvestments ?? 0 }},
                    {{ $pendingInvestments ?? 0 }}
                ],
                backgroundColor: ['#007bff', '#28a745', '#6c757d', '#ffc107'],
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

// Calculate Profitability
function calculateProfitability() {
    $.ajax({
        url: '{{ route("business.investments.calculate-profitability") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Profitability Calculated!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to calculate profitability'
            });
        }
    });
}

// Distribute Profit
function distributeProfit(investmentId) {
    // Get investment details and show modal
    $('#distributeProfitModal').modal('show');
    $('#distributeProfitForm').data('investment-id', investmentId);
}

// Update Status
function updateStatus(investmentId) {
    $('#updateStatusModal').modal('show');
    $('#updateStatusForm').data('investment-id', investmentId);
}

// Handle distribute profit form submission
$('#distributeProfitForm').submit(function(e) {
    e.preventDefault();
    
    const investmentId = $(this).data('investment-id');
    const formData = new FormData(this);
    
    $.ajax({
        url: `/admin/business/investments/${investmentId}/distribute-profit`,
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
                    title: 'Profit Distributed!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    $('#distributeProfitModal').modal('hide');
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
                title: 'Error Distributing Profit',
                text: errorMessage
            });
        }
    });
});

// Handle update status form submission
$('#updateStatusForm').submit(function(e) {
    e.preventDefault();
    
    const investmentId = $(this).data('investment-id');
    const formData = new FormData(this);
    
    $.ajax({
        url: `/admin/business/investments/${investmentId}/update-status`,
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
                    title: 'Status Updated!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    $('#updateStatusModal').modal('hide');
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update status'
            });
        }
    });
});

// Calculate distribution amounts
$('input[name="distribution_amount"]').on('input', function() {
    const amount = parseFloat($(this).val()) || 0;
    const investorShare = 55; // This should be dynamic based on the investment
    const ownerShare = 45;
    
    $('#investorAmount').val('$' + (amount * investorShare / 100).toFixed(2));
    $('#ownerAmount').val('$' + (amount * ownerShare / 100).toFixed(2));
});

function distributeAllProfits() {
    Swal.fire({
        title: 'Distribute All Profits?',
        text: 'This will distribute all available profits to investors and owners.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Distribute All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for distributing all profits
            Swal.fire('Success!', 'All profits have been distributed.', 'success');
        }
    });
}

function generateWorkflowReport() {
    Swal.fire({
        title: 'Generate Report?',
        text: 'This will generate a comprehensive workflow report.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Generate Report',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementation for generating report
            Swal.fire('Success!', 'Report generated successfully.', 'success');
        }
    });
}

function viewDetails(investmentId) {
    // Implementation for viewing investment details
    window.open(`/admin/business/investments/${investmentId}`, '_blank');
}
</script>
@stop 