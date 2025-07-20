@extends('adminlte::page')

@section('title', 'Countries Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-globe text-primary mr-2"></i> Countries Management</h1>
        <div>
            <a href="{{ route('countries.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Country
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Countries Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-list mr-2"></i> Countries List
            </div>
            <div class="card-tools d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)"><i class="fas fa-file-export mr-1"></i> Export</button>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)"><i class="fas fa-print mr-1"></i> Print</button>
                <form method="GET" class="d-flex align-items-center mb-2 mb-md-0" style="gap: 0.5rem;">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name or code..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="?" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-sync-alt"></i></a>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Flag</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Currency</th>
                        <th>Cities</th>
                        <th>Users</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $country)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $country->id }}</span></td>
                            <td>
                                @php
                                    $photo = $country->photos->first();
                                @endphp
                                @if($photo)
                                    <img src="{{ asset($photo->src) }}" alt="Flag" class="img-thumbnail" style="max-height: 30px;">
                                @else
                                    <span class="badge badge-secondary">No Flag</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $name = is_array($country->name) ? $country->name['en'] : $country->name;
                                @endphp
                                <strong>{{ $name }}</strong>
                            </td>
                            <td>{{ $country->country_code }}</td>
                            <td><span class="badge badge-info">{{ $country->currency_code }}</span></td>
                            <td><span class="badge badge-primary">{{ $country->cities->count() }}</span></td>
                            <td><span class="badge badge-success">{{ $country->users->count() }}</span></td>
                            <td><span class="text-muted">{{ $country->created_at ? $country->created_at->format('Y-m-d') : '-' }}</span></td>
                            <td>
                                <a href="{{ route('countries.show', $country->id) }}" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('countries.edit', $country->id) }}" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('countries.destroy', $country->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No countries found.
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
                    Showing <strong>{{ $countries->firstItem() }}</strong> to <strong>{{ $countries->lastItem() }}</strong> of <strong>{{ $countries->total() }}</strong> countries
                </div>
                <div>
                    {{ $countries->links('pagination::bootstrap-4') }}
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
        a.download = 'countries-export.csv';
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
</style>
@endpush
@stop
