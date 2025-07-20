@extends('adminlte::page')

@section('title', 'Reports Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-flag text-primary mr-2"></i> Reports Management</h1>
        <div>
            <a href="{{ route('reports.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Report
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Reports Table -->
    <x-admin.table
        title="Reports List"
        :data="$reports"
        :columns="[
            ['label' => 'ID', 'field' => 'id', 'render' => function($report) {
                return '<span class="badge badge-secondary">' . $report->id . '</span>';
            }],
            ['label' => 'Reporter', 'field' => 'users_id', 'render' => function($report) {
                return '<strong>' . $report->user->name . '</strong><br><small class="text-muted">' . $report->user->email . '</small>';
            }],
            ['label' => 'Type', 'field' => 'reportable_type', 'render' => function($report) {
                $type = class_basename($report->reportable_type);
                return '<span class="badge badge-info">' . $type . '</span>';
            }],
            ['label' => 'Reason', 'field' => 'reason', 'render' => function($report) {
                return '<span class="text-muted">' . Str::limit($report->reason, 50) . '</span>';
            }],
            ['label' => 'Status', 'field' => 'status', 'render' => function($report) {
                if($report->status == 'pending') {
                    return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
                } elseif($report->status == 'resolved') {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Resolved</span>';
                } elseif($report->status == 'rejected') {
                    return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>';
                } else {
                    return '<span class="badge badge-secondary">Unknown</span>';
                }
            }],
            ['label' => 'Created', 'field' => 'created_at', 'render' => function($report) {
                return '<span class="text-muted">' . ($report->created_at ? $report->created_at->format('Y-m-d') : '-') . '</span>';
            }],
        ]"
        :filters="[
            ['type' => 'select', 'name' => 'status', 'options' => ['pending', 'resolved', 'rejected']],
            ['type' => 'select', 'name' => 'type', 'options' => ['User', 'ServicePost', 'Comment']],
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by reason...'],
        ]"
        :actions="function($report) {
            return '
                <a href="' . route('reports.show', $report->id) . '" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                <a href="' . route('reports.edit', $report->id) . '" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="' . route('reports.destroy', $report->id) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            ';
        }"
    />
</div>
@stop
