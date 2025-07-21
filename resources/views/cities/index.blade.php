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
                return '<span class="badge badge-secondary">{{ __('cities\index._city_id_') }}</span>{{ __('cities\index._label_') }}<strong>{{ __('cities\index._name_') }}</strong>{{ __('cities\index._label_') }}<span class="badge badge-info">{{ __('cities\index._countryname_') }}</span>{{ __('cities\index._label_') }}<span class="badge badge-danger"><i class="fas fa-ban"></i> Suspended</span>{{ __('cities\index._else_') }}<span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>{{ __('cities\index._') }}<span class="badge badge-primary">{{ __('cities\index._count_') }}</span>{{ __('cities\index._label_') }}<span class="badge badge-success">{{ __('cities\index._count_') }}</span>{{ __('cities\index._label_') }}<span class="text-muted">{{ __('cities\index._city_created_at_city_created_') }}</span>';
            }],
        ]"
        :filters="[
            ['type' => 'select', 'name' => 'country', 'options' => $countries->pluck('name')->toArray()],
            ['type' => 'select', 'name' => 'status', 'options' => ['active', 'suspended']],
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by name...'],
        ]"
        :actions="function($city) {
            return '
                <a href="' . route('cities.show', $city->field<i class="fas fa-eye"></i></a>
                <a href="' . route('cities.edit', $city->field<i class="fas fa-edit"></i></a>
                <form action="' . route('cities.destroy', $city->id) . '" method="POST" class="d-inline-block" onsubmit="return confirm(\'Are you sure?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-xs btn-outline-danger" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            ';
        }"
    />
</div>
@stop
