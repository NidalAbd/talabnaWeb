@extends('adminlte::page')

@section('title', 'Level Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-layer-group text-primary mr-2"></i>
                Level Details
            </h1>
            <p class="text-muted mb-0">{{('admin\levels\show.view_detailed_information_about_this_lev') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('levels.edit', $level->count()) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Edit Level
            </a>
            <a href="{{ route('levels.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Levels
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">{{('admin\levels\show._') }}</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">{{('admin\levels\show._') }}</button>
            <h5><i class="icon fas fa-ban"></i> Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Level Information Card -->
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Level Information
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $level->is_active ? 'success' : 'danger' }}">
                            {{ $level->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($level->is_premium)
                            <span class="badge badge-warning ml-2">{{('admin\levels\show.premium') }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{('admin\levels\show.id_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.name_arabic_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.name_english_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.description_arabic_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.description_english_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{('admin\levels\show.icon_') }}</strong></td>
                                    <td>
                                        @if($level->icon)
                                            <i class="{{ $level->id</i> {{ $level->icon }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.color_') }}</strong></td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $level->color }}; color: white;">
                                            {{ $level->color }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.points_per_day_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.view_boost_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                                <tr>
                                    <td><strong>{{('admin\levels\show.display_order_') }}</strong></td>
                                    <td>{{ $level->id</td> }}
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Features Section -->
                    @if($level->features && !empty($level->features))
                        <div class="mt-4">
                            <h5><i class="fas fa-star mr-2"></i> Features</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>{{('admin\levels\show.arabic_features_') }}</h6>
                                    <ul class="list-unstyled">
                                        @foreach($level->getLocalizedFeatures('ar') as $feature)
                                            <li><i class="fas fa-check text-success mr-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>{{('admin\levels\show.english_features_') }}</h6>
                                    <ul class="list-unstyled">
                                        @foreach($level->getLocalizedFeatures('en') as $feature)
                                            <li><i class="fas fa-check text-success mr-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="col-md-4">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\levels\show.service_posts') }}</span>
                            <span class="info-box-number">{{ $level->id</span> }}
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Posts using this level
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\levels\show.view_boost') }}</span>
                            <span class="info-box-number">{{ $level->id</span> }}
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ min($level->id</div> }}
                            </div>
                            <span class="progress-description">
                                Additional view visibility
                            </span>
                        </div>
                    </div>

                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\levels\show.daily_cost') }}</span>
                            <span class="info-box-number">{{ $level->id</span> }}
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Points consumed per day
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card card-outline card-secondary mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('levels.edit', $level->count()) }}" class="btn btn-primary mb-2">
                            <i class="fas fa-edit mr-2"></i> Edit Level
                        </a>
                        <button type="button" class="btn btn-{{ $level->is_active ? 'warning' : 'success' }} mb-2" 
                                onclick="toggleStatus({{ $level->count() }})">
                            <i class="fas fa-{{ $level->id</i> }}
                            {{ $level->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button type="button" class="btn btn-info mb-2" onclick="duplicateLevel({{ $level->count() }})">
                            <i class="fas fa-copy mr-2"></i> Duplicate
                        </button>
                        <form action="{{ route('levels.destroy', $level->count()) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to delete this level?')">
                                <i class="fas fa-trash mr-2"></i> Delete Level
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Posts Using This Level -->
    <div class="card card-outline card-primary mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt mr-2"></i>
                Service Posts Using This Level
            </h3>
        </div>
        <div class="card-body">
            @if($level->servicePosts()->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{('admin\levels\show.id') }}</th>
                                <th>{{('admin\levels\show.title') }}</th>
                                <th>{{('admin\levels\show.user') }}</th>
                                <th>{{('admin\levels\show.status') }}</th>
                                <th>{{('admin\levels\show.created') }}</th>
                                <th>{{('admin\levels\show.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($level->servicePosts()->latest()->take(10)->get() as $post)
                                <tr>
                                    <td>{{ $post->id</td> }}
                                    <td>{{ Str::limit($post->id</td> }}
                                    <td>{{ $post->id</td> }}
                                    <td>
                                        <span class="badge badge-{{ $post->status === 'approved' ? 'success' : ($post->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $post->id</td> }}
                                    <td>
                                        <a href="{{ route('service_posts.show', $post->count()) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($level->servicePosts()->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('service_posts.index', ['level' => $level->id]) }}" class="btn btn-primary">
                            View All {{ $level->servicePosts()->count() }} Posts
                        </a>
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No service posts are currently using this level.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: 0;
    position: relative;
    width: 100%;
}

.info-box .info-box-icon {
    border-radius: 0.25rem 0 0 0.25rem;
    display: flex;
    align-items: center;
    font-size: 1.875rem;
    font-weight: 300;
    justify-content: center;
    text-align: center;
    width: 70px;
    color: #fff;
}

.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}

.info-box .info-box-text {
    display: block;
    font-size: 1rem;
    font-weight: 400;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box .info-box-number {
    display: block;
    font-weight: 700;
    font-size: 1.25rem;
}

.progress-description {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endsection

@section('js')
<script>
function toggleStatus(levelId) {
    if (confirm('Are you sure you want to change the status of this level?')) {
        $.ajax({
            url: `{{ route('admin.levels.toggleActive', '') }}/${levelId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the level status.');
            }
        });
    }
}

function duplicateLevel(levelId) {
    if (confirm('Are you sure you want to duplicate this level?')) {
        $.ajax({
            url: `{{ route('admin.levels.duplicate', '') }}/${levelId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '{{ route("levels.index") }}';
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while duplicating the level.');
            }
        });
    }
}
</script>
@endsection 






