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
                return '<span class="badge badge-secondary">' . $city->id . '</span>';
            }],
            ['label' => 'Name', 'field' => 'name', 'render' => function($city) {
                $name = is_array($city->name) ? $city->name['en'] : $city->name;
                return '<strong>' . $name . '</strong>';
            }],
            ['label' => 'Country', 'field' => 'countries_id', 'render' => function($city) {
                $countryName = is_array($city->country->name) ? $city->country->name['en'] : $city->country->name;
                return '<span class="badge badge-info">' . $countryName . '</span>';
            }],
            ['label' => 'Status', 'field' => 'isSuspended', 'render' => function($city) {
                if($city->isSuspended) {
                    return '<span class="badge badge-danger"><i class="fas fa-ban"></i> Suspended</span>';
                } else {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Active</span>';
                }
            }],
            ['label' => 'Users', 'field' => 'id', 'render' => function($city) {
                $count = $city->users->count();
                return '<span class="badge badge-primary">' . $count . '</span>';
            }],
            ['label' => 'Posts', 'field' => 'id', 'render' => function($city) {
                $count = $city->servicePosts->count();
                return '<span class="badge badge-success">' . $count . '</span>';
            }],
            ['label' => 'Created', 'field' => 'created_at', 'render' => function($city) {
                return '<span class="text-muted">' . ($city->created_at ? $city->created_at->format('Y-m-d') : '-') . '</span>';
            }],
        ]"
        :filters="[
            ['type' => 'select', 'name' => 'country', 'options' => $countries->pluck('name')->toArray()],
            ['type' => 'select', 'name' => 'status', 'options' => ['active', 'suspended']],
            ['type' => 'text', 'name' => 'search', 'placeholder' => 'Search by name...'],
        ]"
        :actions="function($city) {
            return '
                <a href="' . route('cities.show', $city->id) . '" class="btn btn-xs btn-outline-info" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                <a href="' . route('cities.edit', $city->id) . '" class="btn btn-xs btn-outline-primary" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
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
