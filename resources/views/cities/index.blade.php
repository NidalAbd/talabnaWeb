@extends('adminlte::page')
@section('title', "Cities Management")
@section('content_header')
    @include('partials.breadcrumbs')
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
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-city text-primary mr-2"></i>
                            Cities
                        </h5>
                        <div>
                            <a href="{{ route('cities.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i> Add New City
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 15%">Image</th>
                                    <th style="width: 25%">City Name</th>
                                    <th style="width: 20%">Country</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 20%">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(is_countable($cities) && count($cities) > 0)
                                    @foreach($cities as $city)
                                        <tr>
                                            <td>{{ $city->id }}</td>
                                            <td class="text-center">
                                                @if($city->photos->count() > 0)
                                                    <img src="{{ asset($city->photos->first()->src) }}"
                                                         class="img-thumbnail" alt="{{ $city->getTranslatedName() }}"
                                                         style="max-height: 50px;">
                                                @else
                                                    <span class="badge badge-secondary">No Image</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-weight-bold">{{ $city->getTranslatedName() }}</div>
                                                <small class="text-muted">
                                                    @if(app()->getLocale() != 'en' && isset($city->name['en']))
                                                        ({{ $city->name['en'] }})
                                                    @elseif(app()->getLocale() != 'ar' && isset($city->name['ar']))
                                                        ({{ $city->name['ar'] }})
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($city->country)
                                                    <a href="{{ route('countries.show', $city->country->id) }}">
                                                        {{ $city->country->getTranslatedName() }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No country assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-success">Active</span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('cities.show', $city->id) }}"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('cities.edit', $city->id) }}"
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('cities.destroy', $city->id) }}"
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
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-city fa-3x text-muted mb-3"></i>
                                                <h5 class="font-weight-normal text-muted">No cities found</h5>
                                                <a href="{{ route('cities.create') }}" class="btn btn-primary mt-3">
                                                    <i class="fas fa-plus mr-1"></i> Create First City
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-center">
                            {{ $cities->links() }}
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
