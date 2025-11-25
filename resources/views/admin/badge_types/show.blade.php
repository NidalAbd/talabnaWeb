@extends('adminlte::page')

@section('title', 'Badge Type Details - Talabna Admin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="{{ $badgeType->icon }} text-primary mr-2" style="color: {{ $badgeType->color }} !important;"></i>
                {{ $badgeType->name_en }}
            </h1>
            <p class="text-muted mb-0">Badge type details and statistics</p>
        </div>
        <div>
            <a href="{{ route('badge_types.edit', $badgeType->id) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <a href="{{ route('badge_types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Badge Details Card -->
        <div class="col-lg-4">
            <div class="card card-outline shadow-sm" style="border-top-color: {{ $badgeType->color }};">
                <div class="card-body text-center">
                    <div class="badge-preview p-4 rounded mb-3" style="background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);">
                        <i class="{{ $badgeType->icon }}" style="font-size: 80px; color: {{ $badgeType->color }};"></i>
                    </div>

                    <h3>{{ $badgeType->name_en }}</h3>
                    <h4 class="text-muted">{{ $badgeType->name_ar }}</h4>

                    <div class="mt-3">
                        @if($badgeType->is_default)
                            <span class="badge badge-info badge-lg px-3 py-2">Default Badge</span>
                        @endif
                        <span class="badge badge-{{ $badgeType->is_active ? 'success' : 'danger' }} badge-lg px-3 py-2">
                            {{ $badgeType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i> Quick Stats
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-coins text-warning mr-2"></i> Points per Day</span>
                            <span class="badge badge-warning badge-pill">{{ $badgeType->points_per_day }} pts</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-eye text-info mr-2"></i> View Boost</span>
                            <span class="badge badge-info badge-pill">+{{ $badgeType->view_boost_percent }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-sort-numeric-up text-primary mr-2"></i> Priority</span>
                            <span class="badge badge-primary badge-pill">{{ $badgeType->priority }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clipboard-list text-success mr-2"></i> Active Posts</span>
                            <span class="badge badge-success badge-pill">{{ $badgeType->service_posts_count ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Details Panel -->
        <div class="col-lg-8">
            <!-- Badge Information -->
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i> Badge Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">ID</th>
                                <td>{{ $badgeType->id }}</td>
                            </tr>
                            <tr>
                                <th>Slug</th>
                                <td><code>{{ $badgeType->slug }}</code></td>
                            </tr>
                            <tr>
                                <th>Name (English)</th>
                                <td>{{ $badgeType->name_en }}</td>
                            </tr>
                            <tr>
                                <th>Name (Arabic)</th>
                                <td>{{ $badgeType->name_ar }}</td>
                            </tr>
                            <tr>
                                <th>Icon</th>
                                <td>
                                    <i class="{{ $badgeType->icon }}" style="color: {{ $badgeType->color }}; font-size: 24px;"></i>
                                    <code class="ml-2">{{ $badgeType->icon }}</code>
                                </td>
                            </tr>
                            <tr>
                                <th>Color</th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded mr-2" style="background-color: {{ $badgeType->color }}; width: 30px; height: 30px;"></div>
                                        <code>{{ $badgeType->color }}</code>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Points per Day</th>
                                <td>{{ $badgeType->points_per_day }} points</td>
                            </tr>
                            <tr>
                                <th>View Boost</th>
                                <td>+{{ $badgeType->view_boost_percent }}%</td>
                            </tr>
                            <tr>
                                <th>Priority</th>
                                <td>{{ $badgeType->priority }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge badge-{{ $badgeType->is_active ? 'success' : 'danger' }}">
                                        {{ $badgeType->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Default Badge</th>
                                <td>
                                    <span class="badge badge-{{ $badgeType->is_default ? 'info' : 'secondary' }}">
                                        {{ $badgeType->is_default ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $badgeType->created_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $badgeType->updated_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cost Calculator -->
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2"></i> Cost Calculator
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="days">Number of Days</label>
                            <input type="number" class="form-control" id="days" value="7" min="1" max="365">
                        </div>
                        <div class="col-md-4">
                            <label>Total Cost</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-weight-bold" id="total_cost"
                                       value="{{ $badgeType->points_per_day * 7 }}" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">points</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                {{ $badgeType->points_per_day }} points/day x <span id="days_display">7</span> days
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i> Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('badge_types.edit', $badgeType->id) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-edit mr-1"></i> Edit Badge
                            </a>
                        </div>
                        @if(!$badgeType->is_default)
                            <div class="col-md-3 mb-2">
                                <form action="{{ route('badge_types.set_default', $badgeType->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fas fa-star mr-1"></i> Set as Default
                                    </button>
                                </form>
                            </div>
                        @endif
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('badge_types.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-list mr-1"></i> View All Badges
                            </a>
                        </div>
                        @if(!$badgeType->is_default && ($badgeType->service_posts_count ?? 0) == 0)
                            <div class="col-md-3 mb-2">
                                <form action="{{ route('badge_types.destroy', $badgeType->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this badge type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash mr-1"></i> Delete Badge
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    const pointsPerDay = {{ $badgeType->points_per_day }};

    $('#days').on('input change', function() {
        const days = parseInt($(this).val()) || 0;
        const total = pointsPerDay * days;
        $('#total_cost').val(total);
        $('#days_display').text(days);
    });
});
</script>
@endpush
