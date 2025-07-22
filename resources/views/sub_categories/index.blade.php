@extends('adminlte::page')

@section('title', 'Sub Categories Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-tags text-primary mr-2"></i> Sub Categories Management</h1>
        <div>
            <a href="{{ route('subcategories.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Sub Category
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Sub Categories Table -->
    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="card-title mb-2 mb-md-0">
                <i class="fas fa-list mr-2"></i> Sub Categories List
            </div>
            <div class="card-tools d-flex align-items-center flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)"><i class="fas fa-file-export mr-1"></i> Export</button>
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)"><i class="fas fa-print mr-1"></i> Print</button>
                <form method="GET" class="d-flex align-items-center mb-2 mb-md-0" style="gap: 0.5rem;">
                    <select name="category" class="form-control form-control-sm">
                        <option value="">{{('sub_categories\index.all_categories') }}</option>
                        @foreach($categories as $cat)
                            @php
                                $catName = is_array($cat->name) ? ($cat->name['en'] ?? reset($cat->name)) : $cat->name;
                            @endphp
                            <option value="{{ $catName }}" @if(request('category') == $catName) selected @endif>{{ $catName }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">{{('sub_categories\index.all_status') }}</option>
                        <option value="active" @if(request('status') == 'active') selected @endif>{{('sub_categories\index.active') }}</option>
                        <option value="suspended" @if(request('status') == 'suspended') selected @endif>{{('sub_categories\index.suspended') }}</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="?" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-sync-alt"></i></a>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-bordered align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>{{('sub_categories\index.id') }}</th>
                        <th>{{('sub_categories\index.name') }}</th>
                        <th>{{('sub_categories\index.category') }}</th>
                        <th>{{('sub_categories\index.status') }}</th>
                        <th>{{('sub_categories\index.posts') }}</th>
                        <th>{{('sub_categories\index.created') }}</th>
                        <th>{{('sub_categories\index.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subCategories as $subCategory)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $subCategory->id ?? 'FIXME' }}</span></td>
                            <td>
                                @php
                                    $name = is_array($subCategory->name) ? $subCategory->name['en'] : $subCategory->name;
                                @endphp
                                <strong>{{ $name }}</strong>
                            </td>
                            <td>
                                @php
                                    $categoryName = isset($subCategory->category) && $subCategory->category ? (is_array($subCategory->category->name) ? $subCategory->category->name['en'] : $subCategory->category->name) : 'N/A';
                                @endphp
                                <span class="badge badge-info">{{ $categoryName }}</span>
                            </td>
                            <td>
                                @if($subCategory->isSuspended)
                                    <span class="badge badge-danger"><i class="fas fa-ban"></i> Suspended</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>
                                @endif
                            </td>
                            <td><span class="badge badge-primary">{{ $subCategory->id }}</span></td>
                            <td><span class="text-muted">{{ $subCategory->id }}</span></td>
                            <td>
                                <a href="{{ route('subcategories.show', $subCategory->id) }}"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('subcategories.edit', $subCategory->id) }}"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('subcategories.destroy', $subCategory->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info m-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No sub categories found.
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
                    Showing <strong>{{ $subCategories->firstItem() }}</strong> to <strong>{{ $subCategories->lastItem() }}</strong> of <strong>{{ $subCategories->total() }}</strong> sub categories
                </div>
                <div>
                    {{ $subCategories->links('pagination::bootstrap-4') }}
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
        w.document.write('<html><head><title>{{('sub_categories\index.print_table') }}</title>{{('sub_categories\index._w_document_write_') }}<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">{{('sub_categories\index._w_document_write_') }}</head><body>{{('sub_categories\index._w_document_write_table_outer') }}</body></html>');
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
        a.download = 'subcategories-export.csv';
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







