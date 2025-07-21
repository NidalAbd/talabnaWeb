@extends('adminlte::page')

@section('title', 'Users Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-users text-primary mr-2"></i> Users Management</h1>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fas fa-user-plus mr-1"></i> Add User
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-primary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('users\index.total_users') }}</span>
                    <span class="info-box-number">{{ number_format($users->field</span>
                    <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                    <span class="progress-description">
                        <i class="fas fa-chart-line text-light"></i> All registered users
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('users\index.active_users') }}</span>
                    <span class="info-box-number">{{ number_format($activeUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->field</div></div>
                    <span class="progress-description">
                        <i class="fas fa-check-circle text-light"></i> {{ $users->total() > 0 ? number_format(($activeUsersCount / $users->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-danger shadow-sm">
                <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('users\index.banned_users') }}</span>
                    <span class="info-box-number">{{ number_format($bannedUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->field</div></div>
                    <span class="progress-description">
                        <i class="fas fa-exclamation-triangle text-light"></i> {{ $users->total() > 0 ? number_format(($bannedUsersCount / $users->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-user-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('users\index.inactive_users') }}</span>
                    <span class="info-box-number">{{ number_format($inactiveUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->field</div></div>
                    <span class="progress-description">
                        <i class="fas fa-pause-circle text-light"></i> {{ $users->total() > 0 ? number_format(($inactiveUsersCount / $users->total()) * 100, 1) : 0 }}% of total
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-list mr-2"></i> Users List
            </div>
            <div class="card-tools d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)"><i class="fas fa-file-export mr-1"></i> Export</button>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)"><i class="fas fa-print mr-1"></i> Print</button>
                <form method="GET" class="d-flex align-items-center mb-2 mb-md-0" style="gap: 0.5rem;">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{ __('users\index.all_status') }}</option>
                        <option value="active" @if(request('status') == 'active') selected @endif>{{ __('users\index.active') }}</option>
                        <option value="inactive" @if(request('status') == 'inactive') selected @endif>{{ __('users\index.inactive') }}</option>
                        <option value="banned" @if(request('status') == 'banned') selected @endif>{{ __('users\index.banned') }}</option>
                    </select>
                    <select name="role" class="form-control form-control-sm">
                        <option value="">{{ __('users\index.all_roles') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @if(request('role') == $role->name) selected @endif>{{ ucfirst($role->field</option>
                        @endforeach
                    </select>
                    <select name="gender" class="form-control form-control-sm">
                        <option value="">{{ __('users\index.all_genders') }}</option>
                        <option value="ذكر" @if(request('gender') == 'ذكر') selected @endif>{{ __('users\index.male') }}</option>
                        <option value="انثى" @if(request('gender') == 'انثى') selected @endif>{{ __('users\index.female') }}</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, user ID..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="?" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-sync-alt"></i></a>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('users\index.avatar') }}</th>
                        <th>{{ __('users\index.user_id') }}</th>
                        <th>{{ __('users\index.name') }}</th>
                        <th>{{ __('users\index.email') }}</th>
                        <th>{{ __('users\index.status') }}</th>
                        <th>{{ __('users\index.roles') }}</th>
                        <th>{{ __('users\index.posts') }}</th>
                        <th>{{ __('users\index.reports') }}</th>
                        <th>{{ __('users\index.registered') }}</th>
                        <th>{{ __('users\index.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                @php
                                    $photo = $user->photos->first();
                                    $src = $photo ? $photo->src : asset('img/avatar1.png');
                                @endphp
                                <img src="{{ $src }}" alt="Avatar" class="img-circle elevation-2" width="40" height="40" style="object-fit:cover;">
                            </td>
                            <td><span class="badge badge-secondary">{{ $user->field</span></td>
                            <td>
                                <strong>{{ $user->field</strong><br>
                                <small class="text-muted">{{ $user->field</small>
                            </td>
                            <td><a href="mailto:{{ $user->email }}" class="text-primary">{{ $user->email }}</a></td>
                            <td>
                                @if($user->is_active == 'active')
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                @elseif($user->is_active == 'banned')
                                    <span class="badge badge-danger"><i class="fas fa-ban"></i> Banned</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-pause-circle"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-primary">{{ ucfirst($role->field</span>
                                @endforeach
                                @if($user->roles->count() == 0)
                                    <span class="badge badge-secondary">{{ __('users\index.no_role') }}</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $user->field</span></td>
                            <td><span class="badge badge-danger">{{ $user->field</span></td>
                            <td><span class="text-muted">{{ $user->field</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('users.show', $user->field<i class="fas fa-eye"></i></a>
                                    <a href="{{ route('users.edit', $user->field<i class="fas fa-edit"></i></a>
                                    
                                    <!-- Balance Button -->
                                    <button type="button" class="btn btn-outline-success" data-toggle="tooltip" title="Balance: {{ $user->pointsBalance ?? 0 }} points" onclick="showBalanceModal({{ $user->id }}, '{{ $user->name }}', {{ $user->pointsBalance ?? 0 }})">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                    
                                    <!-- Role Assignment Link -->
                                    <a href="{{ route('role-assignments.edit', $user->id) }}" class="btn btn-outline-warning" data-toggle="tooltip" title="Assign Roles & Permissions">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                    
                                    <!-- Ban/Unban Button -->
                                    @if($user->is_active == 'banned')
                                        <form action="{{ route('users.unban', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Unban this user?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" data-toggle="tooltip" title="Unban"><i class="fas fa-unlock"></i></button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.ban', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Ban this user?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" data-toggle="tooltip" title="Ban"><i class="fas fa-user-slash"></i></button>
                                        </form>
                                    @endif
                                    
                                    <!-- Delete Button -->
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    
                                    <!-- History Button -->
                                    <a href="{{ route('users.login_history', $user->field<i class="fas fa-history"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No users found.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <strong>{{ $users->field</strong> to <strong>{{ $users->field</strong> of <strong>{{ $users->field</strong> users
                </div>
                <div>
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Modal -->
<div class="modal fade" id="balanceModal" tabindex="-1" role="dialog" aria-labelledby="balanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balanceModalLabel">{{ __('users\index.user_balance') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">{{ __('users\index._times_') }}</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h4 id="balanceUserName"></h4>
                    <div class="balance-display">
                        <i class="fas fa-coins text-warning" style="font-size: 3rem;"></i>
                        <h2 id="balanceAmount" class="text-success mt-2"></h2>
                        <p class="text-muted">{{ __('users\index.total_points_balance') }}</p>
                    </div>
                    <div class="balance-actions mt-3">
                        <button type="button" class="btn btn-success" onclick="addPoints()">
                            <i class="fas fa-plus"></i> Add Points
                        </button>
                        <button type="button" class="btn btn-warning" onclick="deductPoints()">
                            <i class="fas fa-minus"></i> Deduct Points
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('users\index.close') }}</button>
            </div>
        </div>
    </div>
</div>



@push('js')
<script>
    function printTable(btn) {
        let table = btn.closest('.card').querySelector('table');
        let w = window.open();
        w.document.write('<html><head><title>{{ __('users\index.print_table') }}</title>{{ __('users\index._w_document_write_') }}<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">{{ __('users\index._w_document_write_') }}</head><body>{{ __('users\index._w_document_write_table_outer') }}</body></html>');
        w.print();
        w.close();
    }
    function exportTable(btn) {
        // Simple CSV export
        let table = btn.closest('.card').querySelector('table');
        let rows = Array.from(table.rows);
        let csv = rows.map(row => Array.from(row.cells).map(cell => '"' + cell.innerText.replace(/"/g, '""') + '"').join(',')).join('\n');
        let blob = new Blob([csv], { type: 'text/csv' });
        let a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'users-export.csv';
        a.click();
    }
    
    // Global variables for modals
    let currentUserId = null;
    let currentUserName = null;
    
    // Balance Modal Functions
    function showBalanceModal(userId, userName, balance) {
        currentUserId = userId;
        currentUserName = userName;
        
        document.getElementById('balanceUserName').textContent = userName;
        document.getElementById('balanceAmount').textContent = balance + ' points';
        
        $('#balanceModal').modal('show');
    }
    
    function addPoints() {
        const points = prompt('Enter points to add:');
        if (points && !isNaN(points) && points > 0) {
            // AJAX call to add points
            fetch(`/users/${currentUserId}/add-points`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ points: parseInt(points) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Points added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding points');
            });
        }
    }
    
    function deductPoints() {
        const points = prompt('Enter points to deduct:');
        if (points && !isNaN(points) && points > 0) {
            // AJAX call to deduct points
            fetch(`/users/${currentUserId}/deduct-points`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ points: parseInt(points) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Points deducted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deducting points');
            });
        }
    }
    

    
    // Debug form submissions
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Users index page loaded');
        
        // Add event listeners to all action forms
        const actionForms = document.querySelectorAll('form[action*="/users/"]');
        actionForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted:', this.action);
            });
        });
    });
</script>
@endpush

@push('css')
<style>
    .table thead th { background: #f8f9fa; }
    .table td, .table th { vertical-align: middle !important; }
    .card { transition: box-shadow 0.2s; }
    .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
    .badge-pink { background: #e83e8c; color: #fff; }
    .img-circle { border-radius: 50%; }
    
    /* Button group styling - improved for better visibility */
    .btn-group .btn {
        margin-right: 1px;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.2;
        border-radius: 0.2rem;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    /* Ensure action buttons are always visible */
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    /* Make sure forms inside btn-group don't break layout */
    .btn-group form {
        display: inline-block;
        margin: 0;
    }
    
    /* Modal styling */
    .balance-display {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        margin: 20px 0;
    }
    
    .balance-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .custom-control {
        margin-bottom: 8px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    /* Responsive improvements for action buttons */
    @media (max-width: 768px) {
        .btn-group .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
    }
</style>
@endpush
@stop
