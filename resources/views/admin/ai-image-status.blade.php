@extends('adminlte::page')

@section('title', 'AI Image Generation')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-magic text-dark mr-2"></i> AI Image Generation</h1>
        <div>
            @if($batchType === 'category')
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Categories
                </a>
            @else
                <a href="{{ route('indexSubCategory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Subcategories
                </a>
            @endif
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs mr-2"></i>
                            Batch Generation - {{ ucfirst($batchType) === 'Category' ? 'Categories' : 'Subcategories' }}
                            @if($isComplete)
                                <span class="badge badge-success ml-2">Complete</span>
                            @else
                                <span class="badge badge-warning ml-2">In Progress</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Progress Bar --}}
                        @php
                            $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                        @endphp
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="font-weight-bold">Progress: {{ $done }} / {{ $total }}</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar {{ $isComplete ? 'bg-success' : 'bg-primary progress-bar-striped progress-bar-animated' }}"
                                     role="progressbar"
                                     style="width: {{ $percent }}%"
                                     aria-valuenow="{{ $percent }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    {{ $percent }}%
                                </div>
                            </div>
                        </div>

                        {{-- Current Item --}}
                        @if(!$isComplete && $nextItemName)
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                <strong>Next:</strong> Generating image for "{{ $nextItemName }}"...
                            </div>
                        @endif

                        {{-- Elapsed Time --}}
                        @if($startedAt)
                            <div class="text-muted mb-3">
                                <i class="fas fa-clock mr-1"></i>
                                Started: {{ \Carbon\Carbon::parse($startedAt)->diffForHumans() }}
                            </div>
                        @endif

                        {{-- Completed Items --}}
                        @if(count($completed) > 0)
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-success">
                                    <i class="fas fa-check-circle mr-1"></i> Completed ({{ count($completed) }})
                                </h6>
                                <ul class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($completed as $name)
                                        <li class="list-group-item py-1 px-2 text-success">
                                            <i class="fas fa-check mr-1"></i> {{ $name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Errors --}}
                        @if(count($errors) > 0)
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-danger">
                                    <i class="fas fa-times-circle mr-1"></i> Failed ({{ count($errors) }})
                                </h6>
                                <ul class="list-group list-group-flush" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($errors as $name)
                                        <li class="list-group-item py-1 px-2 text-danger">
                                            <i class="fas fa-times mr-1"></i> {{ $name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Summary when complete --}}
                        @if($isComplete && $total > 0)
                            <div class="alert alert-success">
                                <h5 class="mb-1"><i class="fas fa-check-double mr-2"></i> Generation Complete!</h5>
                                <p class="mb-0">
                                    Successfully generated {{ count($completed) }} image(s).
                                    @if(count($errors) > 0)
                                        {{ count($errors) }} item(s) failed.
                                    @endif
                                </p>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="mt-3 d-flex justify-content-between">
                            @if($batchType === 'category')
                                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-stop mr-1"></i> Stop & Go Back
                                </a>
                            @else
                                <a href="{{ route('indexSubCategory.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-stop mr-1"></i> Stop & Go Back
                                </a>
                            @endif

                            @if($isComplete)
                                @if($batchType === 'category')
                                    <a href="{{ route('categories.index') }}" class="btn btn-success">
                                        <i class="fas fa-check mr-1"></i> Done
                                    </a>
                                @else
                                    <a href="{{ route('indexSubCategory.index') }}" class="btn btn-success">
                                        <i class="fas fa-check mr-1"></i> Done
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-continue form (hidden, auto-submits after 1.5s) --}}
    @if(!$isComplete)
        <form id="continue-form"
              action="{{ $batchType === 'category' ? route('ai-image.generate-all-categories') : route('ai-image.generate-all-subcategories') }}"
              method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="continue" value="1">
        </form>
    @endif
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            @if(!$isComplete)
                setTimeout(function() {
                    document.getElementById('continue-form').submit();
                }, 1500);
            @endif
        });
    </script>
@endpush
