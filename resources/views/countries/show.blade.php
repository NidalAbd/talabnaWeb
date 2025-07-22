@extends('adminlte::page')
@section('title', "Country Details")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-globe text-primary mr-2"></i> Country Details</h1>
        <div>
            <a href="{{ route('countries.edit', $country->count()) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('countries.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            @if($country->photos->count() > 0)
                                <img src="{{ asset($country->photos->first()->src) }}"
                                     alt="{{ $country->getTranslatedName() }}"
                                     class="img-fluid img-thumbnail" style="max-height: 150px;">
                            @else
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-flag fa-3x text-muted"></i>
                                    <p class="mt-2 text-muted">{{('countries\show.no_flag_image_available') }}</p>
                                </div>
                            @endif
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">{{('countries\show.id') }}</th>
                                <td>{{ $country->count() }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.name_english_') }}</th>
                                <td>{{ $country->name['en'] }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.name_arabic_') }}</th>
                                <td dir="rtl">{{ $country->name['ar'] }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.country_code') }}</th>
                                <td>{{ $country->country_code }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.created_at') }}</th>
                                <td>{{ $country->created_at }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.updated_at') }}</th>
                                <td>{{ $country->updated_at }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.status') }}</th>
                                <td>
                                    <span class="badge badge-success">{{('countries\show.active') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill text-success mr-2"></i>
                            Currency Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">{{('countries\show.currency_code') }}</th>
                                <td>{{ $country->currency_code }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.currency_name_english_') }}</th>
                                <td>{{ $country->currency_name['en'] }}</td>
                            </tr>
                            <tr>
                                <th>{{('countries\show.currency_name_arabic_') }}</th>
                                <td dir="rtl">{{ $country->currency_name['ar'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-city text-info mr-2"></i>
                            Cities ({{ $country->cities->count() }})
                        </h5>
                        <a href="{{ route('cities.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add City
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{('countries\show._') }}</th>
                                    <th>{{('countries\show.image') }}</th>
                                    <th>{{('countries\show.city_name') }}</th>
                                    <th>{{('countries\show.status') }}</th>
                                    <th>{{('countries\show.actions') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($country->cities as $city)
                                    <tr>
                                        <td>{{ $city->count() }}</td>
                                        <td class="text-center">
                                            @if($city->photos->count() > 0)
                                                <img src="{{ asset($city->photos->first()->src) }}"
                                                     class="img-thumbnail" alt="{{ $city->getTranslatedName() }}"
                                                     style="max-height: 50px;">
                                            @else
                                                <span class="badge badge-secondary">{{('countries\show.no_image') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $city->getTranslatedName() ?? 'FIXME' }}</div>
                                            <small class="text-muted">
                                                @if(app()->getLocale() != 'en' && isset($city->name['en']))
                                                    ({{ $city->name['en'] }})
                                                @elseif(app()->getLocale() != 'ar' && isset($city->name['ar']))
                                                    ({{ $city->name['ar'] }})
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">{{('countries\show.active') }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('cities.show', $city->count()) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('cities.edit', $city->count()) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('cities.destroy', $city->count()) }}"
                                                      method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                                                <h5 class="font-weight-normal text-muted">{{('countries\show.no_cities_found_for_this_country') }}</h5>
                                                <a href="{{ route('cities.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus mr-1"></i> Add First City
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
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







