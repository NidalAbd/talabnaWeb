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
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number">{{ number_format($users->total()) }}</span>
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
                    <span class="info-box-text">Active Users</span>
                    <span class="info-box-number">{{ number_format($activeUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->total() > 0 ? ($activeUsersCount / $users->total()) * 100 : 0 }}%"></div></div>
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
                    <span class="info-box-text">Banned Users</span>
                    <span class="info-box-number">{{ number_format($bannedUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->total() > 0 ? ($bannedUsersCount / $users->total()) * 100 : 0 }}%"></div></div>
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
                    <span class="info-box-text">Inactive Users</span>
                    <span class="info-box-number">{{ number_format($inactiveUsersCount) }}</span>
                    <div class="progress"><div class="progress-bar bg-light" style="width: {{ $users->total() > 0 ? ($inactiveUsersCount / $users->total()) * 100 : 0 }}%"></div></div>
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
                        <option value="">All Status</option>
                        <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                        <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                        <option value="banned" @if(request('status') == 'banned') selected @endif>Banned</option>
                    </select>
                    <select name="role" class="form-control form-control-sm">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @if(request('role') == $role->name) selected @endif>{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    <select name="gender" class="form-control form-control-sm">
                        <option value="">All Genders</option>
                        <option value="ذكر" @if(request('gender') == 'ذكر') selected @endif>Male</option>
                        <option value="انثى" @if(request('gender') == 'انثى') selected @endif>Female</option>
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
                        <th>Avatar</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Roles</th>
                        <th>Posts</th>
                        <th>Reports</th>
                        <th>Registered</th>
                        <th>Actions</th>
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
                            <td><span class="badge badge-secondary">{{ $user->id }}</span></td>
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->user_name }}</small>
                            </td>
                            <td><a href="mailto:{{ $user->email }}" class="text-primary">{{ $user->email }}</a></td>
                            <td>{{ $user->phones }}</td>
                            <td>
                                @if($user->gender == 'ذكر')
                                    <span class="badge badge-info"><i class="fas fa-mars"></i> Male</span>
                                @else
                                    <span class="badge badge-pink"><i class="fas fa-venus"></i> Female</span>
                                @endif
                            </td>
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
                                    <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                                @endforeach
                                @if($user->roles->count() == 0)
                                    <span class="badge badge-secondary">No Role</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $user->service_posts_count ?? 0 }}</span></td>
                            <td><span class="badge badge-danger">{{ $user->reports_count ?? 0 }}</span></td>
                            <td><span class="text-muted">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</span></td>
                            <td>
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                                @if($user->is_active == 'banned')
                                    <form action="{{ route('users.unban', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Unban this user?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-success" data-toggle="tooltip" title="Unban"><i class="fas fa-unlock"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('users.ban', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Ban this user?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-warning" data-toggle="tooltip" title="Ban"><i class="fas fa-user-slash"></i></button>
                                    </form>
                                @endif
                                <form action="{{ route('users.reset_password', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Reset password for this user?');">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-secondary" data-toggle="tooltip" title="Reset Password"><i class="fas fa-key"></i></button>
                                </form>
                                <form action="{{ route('users.send_notification', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Send notification to this user?');">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="Send Notification"><i class="fas fa-bell"></i></button>
                                </form>
                                <form action="{{ route('users.impersonate', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Impersonate this user?');">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-dark" data-toggle="tooltip" title="Impersonate"><i class="fas fa-user-secret"></i></button>
                                </form>
                                <a href="{{ route('users.login_history', $user->id) }}" class="btn btn-xs btn-outline-secondary" data-toggle="tooltip" title="Login History"><i class="fas fa-history"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4">
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
                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                </div>
                <div>
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function printTable(btn) {
        let table = btn.closest('.card').querySelector('table');
        let w = window.open();
        w.document.write('<html><head><title>Print Table</title>');
        w.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">');
        w.document.write('</head><body>');
        w.document.write(table.outerHTML);
        w.document.write('</body></html>');
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
</script>
@endpush

@push('css')
<style>
    .table thead th { background: #f8f9fa; }
    .table td, .table th { vertical-align: middle !important; }
    .card { transition: box-shadow 0.2s; }
    .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
    .badge-pink { background: #e83e8c; color: #fff; }
    .img-circle { border-radius: 50%; }
</style>
@endpush
@stop
