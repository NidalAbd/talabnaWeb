@extends('adminlte::page')

@section('title', 'Cities Management')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-city text-primary mr-2"></i> Cities Management</h1>
        <div>
            <a href="{{ route('cities.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add City
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Cities Table -->
    <x-admin.table
        title="Cities List"
        :data="$cities"
        :columns="[
            ['label' => 'ID', 'field' => 'id', 'render' => function($city) {
                return '<span class="badge badge-secondary">{{('cities\index._city_id_') }}</span>{{('cities\index._label_') }}<strong>{{('cities\index._name_') }}</strong>{{('cities\index._label_') }}<span class="badge badge-info">{{('cities\index._countryname_') }}</span>{{('cities\index._label_') }}<span class="badge badge-danger"><i class="fas fa-ban"></i> Suspended</span>{{('cities\index._else_') }}<span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>{{('cities\index._') }}<span class="badge badge-primary">{{('cities\index._count_') }}</span>{{('cities\index._label_') }}<span class="badge badge-success">{{('cities\index._count_') }}</span>{{('cities\index._label_') }}<span class="text-muted">{{('cities\index._city_created_at_city_created_') }}</span>';
            }],
        ]"
        :filters="[
            ['type' => 'select', 'name' => 'country', 'options' => $countries->pluck('name')->toArray()],
            ['type' => 'select', 'name' => 'status', 'options' => ['active', 'suspended']],
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by name...'],
        ]"
        :actions="function($city) {
            return '
                <a href="' . route('cities.show', $city->id<i class="fas fa-eye"></i></a>
                <a href="' . route('cities.edit', $city->id<i class="fas fa-edit"></i></a>
                <form action="' . route('cities.destroy', $city->count()) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            ';
        }"
    />
</div>
@stop







