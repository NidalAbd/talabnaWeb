@extends('adminlte::page')
@section('title', "City Details")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-city text-primary mr-2"></i> City Details</h1>
        <div>
            <a href="{{ route('cities.edit', $city->id) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('cities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            @if($city->photos && $city->photos->count() > 0)
                                <img src="{{ asset($city->photos->first()->src) }}"
                                     alt="{{ $city->getTranslatedName() }}"
                                     class="img-fluid img-thumbnail" style="max-height: 150px;">
                            @else
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-city fa-3x text-muted"></i>
                                    <p class="mt-2 text-muted">{{('cities\show.no_city_image_available') }}</p>
                                </div>
                            @endif
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">{{('cities\show.id') }}</th>
                                <td>{{ $city->id }}</td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.name_english_') }}</th>
                                <td>{{ $city->name['en'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.name_arabic_') }}</th>
                                <td dir="rtl">{{ $city->name['ar'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.country') }}</th>
                                <td>
                                    @if($city->country)
                                        <a href="{{ route('countries.show', $city->country->id) }}">
                                            {{ $city->country->getTranslatedName() }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{('cities\show.no_country_assigned') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.created_at') }}</th>
                                <td>{{ $city->id }}</td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.updated_at') }}</th>
                                <td>{{ $city->id }}</td>
                            </tr>
                            <tr>
                                <th>{{('cities\show.status') }}</th>
                                <td>
                                    <span class="badge badge-success">{{('cities\show.active') }}</span>
                                </td>
                            </tr>
                        </table>

                        <div class="mt-4">
                            <form action="{{ route('cities.destroy', $city->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash mr-1"></i> Delete City
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-globe text-info mr-2"></i>
                            Country Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($city->country)
                            <div class="text-center mb-4">
                                @if($city->country->photos->count() > 0)
                                    <img src="{{ asset($city->country->photos->first()->src) }}"
                                         alt="{{ $city->country->getTranslatedName() }}"
                                         class="img-fluid img-thumbnail" style="max-height: 150px;">
                                @else
                                    <div class="text-center p-4 bg-light rounded">
                                        <i class="fas fa-flag fa-3x text-muted"></i>
                                        <p class="mt-2 text-muted">{{('cities\show.no_country_flag_available') }}</p>
                                    </div>
                                @endif
                            </div>

                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 40%;">{{('cities\show.country_name') }}</th>
                                    <td>{{ $city->country->name['en'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>{{('cities\show.country_code') }}</th>
                                    <td>{{ $city->country->country_code ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>{{('cities\show.currency') }}</th>
                                    <td>
                                        @if($city->country->currency_code)
                                            {{ $city->country->currency_code }}
                                            ({{ $city->country->getTranslatedCurrencyName() }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{('cities\show.total_cities') }}</th>
                                    <td>{{ $city->country->cities->count() ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>{{('cities\show.status') }}</th>
                                    <td>
                                        <span class="badge badge-success">{{('cities\show.active') }}</span>
                                    </td>
                                </tr>
                            </table>

                            <div class="mt-3">
                                <a href="{{ route('countries.show', $city->country->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye mr-1"></i> View Country Details
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                This city is not assigned to any country.
                            </div>
                            <div class="text-center py-4">
                                <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                                <h5 class="font-weight-normal text-muted">{{('cities\show.no_country_information_available') }}</h5>
                                <p class="text-muted">{{('cities\show.please_edit_this_city_to_assign_it_to_a_') }}</p>
                                <a href="{{ route('cities.edit', $city->count()) }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-edit mr-1"></i> Edit City
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Confirmation dialog for delete
                $('.delete-form').on('submit', function(e) {
                    e.preventDefault();

                    const form = this;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the city permanently!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection







