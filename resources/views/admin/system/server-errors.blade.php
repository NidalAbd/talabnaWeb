@extends('adminlte::page')

@section('title', 'Server errors')

@section('content_header')
    <h1><i class="fas fa-bug text-danger mr-2"></i> Server errors</h1>
@stop

@section('content')
    <form method="get" class="form-inline mb-3">
        <label class="mr-2">Last</label>
        <select name="hours" class="form-control mr-2" onchange="this.form.submit()">
            @foreach([6, 24, 48, 168, 336] as $h)
                <option value="{{ $h }}" @selected($hours == $h)>{{ $h < 48 ? $h.' hours' : ($h / 24).' days' }}</option>
            @endforeach
        </select>
        <select name="level" class="form-control" onchange="this.form.submit()">
            <option value="">All levels</option>
            @foreach($levels as $l)
                <option value="{{ $l }}" @selected($level === $l)>{{ $l }}</option>
            @endforeach
        </select>
    </form>

    @if(empty($groups))
        <div class="alert alert-success">No errors or warnings in this period.</div>
    @else
        <p class="text-muted">{{ count($groups) }} different problems. Same message with other ids or numbers is counted together.</p>
        @foreach($groups as $i => $g)
            @php($badge = match($g['level']) { 'WARNING' => 'warning', 'ERROR' => 'danger', default => 'dark' })
            <div class="card mb-2">
                <div class="card-header d-flex align-items-center" data-toggle="collapse" data-target="#e{{ $i }}" style="cursor:pointer">
                    <span class="badge badge-{{ $badge }} mr-2">{{ $g['level'] }}</span>
                    <span class="badge badge-secondary mr-2">× {{ $g['count'] }}</span>
                    <code class="text-wrap flex-grow-1">{{ $g['message'] }}</code>
                    <small class="text-muted ml-2 text-nowrap">last {{ $g['last']->diffForHumans() }}</small>
                </div>
                <div id="e{{ $i }}" class="collapse">
                    <div class="card-body">
                        <small class="text-muted">First {{ $g['first']->toDateTimeString() }} · last {{ $g['last']->toDateTimeString() }} (server time)</small>
                        <pre class="mt-2 mb-0" style="white-space:pre-wrap;max-height:360px;overflow:auto">{{ $g['sample'] }}</pre>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@stop
