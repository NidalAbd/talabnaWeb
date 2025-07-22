@props([
    'columns' => [], // [['label' => 'Name', 'field' => 'name'], ...]
    'data', // Paginator or Collection
    'filters' => [], // [['type' => 'select', 'name' => 'status', ...], ...]
    'actions' => null, // Optional slot for custom actions
    'searchable' => true,
    'exportable' => true,
    'printable' => true,
    'pagination' => true,
])

<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div class="card-title mb-2 mb-md-0">
            <i class="fas fa-list mr-2"></i> {{ $title ?? 'List' }}
        </div>
        <div class="card-tools d-flex align-items-center flex-wrap">
            @if($exportable)
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="exportTable(this)"><i class="fas fa-file-export mr-1"></i> Export</button>
            @endif
            @if($printable)
                <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-2 mb-md-0" onclick="printTable(this)"><i class="fas fa-print mr-1"></i> Print</button>
            @endif
            @if($searchable)
                <form method="GET" class="d-flex align-items-center mb-2 mb-md-0" style="gap: 0.5rem;">
                    @foreach($filters as $filter)
                        @if($filter['type'] === 'select')
                            <select name="{{ $filter['name'] }}" class="form-control form-control-sm">
                                <option value="">All {{ ucfirst($filter['name']) }}</option>
                                @foreach($filter['options'] as $option)
                                    <option value="{{ $option }}" @if(request($filter['name']) == $option) selected @endif>{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'text')
                            <input type="text" name="{{ $filter['name'] }}" class="form-control form-control-sm" placeholder="{{ $filter['placeholder'] ?? 'Search...' }}" value="{{ request($filter['name']) }}">
                        @endif
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                    <a href="?" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-sync-alt"></i></a>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped table-bordered align-middle" id="admin-table-{{ uniqid() }}">
            <thead class="thead-light">
                <tr>
                    @foreach($columns as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach
                    @if($actions)
                        <th>{{('components\admin\table.actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        @foreach($columns as $col)
                            <td>
                                @if(isset($col['render']) && is_callable($col['render']))
                                    {!! $col['render']($row) !!}
                                @else
                                    {{ data_get($row, $col['field']) }}
                                @endif
                            </td>
                        @endforeach
                        @if($actions)
                            <td>{!! $actions($row) !!}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="text-center py-4">
                            <div class="alert alert-info m-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                No records found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pagination && method_exists($data, 'links'))
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <strong>{{ $data->firstItem() }}</strong> to <strong>{{ $data->lastItem() }}</strong> of <strong>{{ $data->total() }}</strong> records
                </div>
                <div>
                    {{ $data->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    @endif
</div>

@push('js')
<script>
    function printTable(btn) {
        let table = btn.closest('.card').querySelector('table');
        let w = window.open();
        w.document.write('<html><head><title>{{('components\admin\table.print_table') }}</title>{{('components\admin\table._w_document_write_') }}<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">{{('components\admin\table._w_document_write_') }}</head><body>{{('components\admin\table._w_document_write_table_oute') }}</body></html>');
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
        a.download = 'export.csv';
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
    .btn-sm { font-size: 0.85rem; }
</style>
@endpush 






