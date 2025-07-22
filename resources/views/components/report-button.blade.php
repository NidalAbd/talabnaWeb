<!-- resources/views/components/report-button.blade.php -->
<button type="button" class="btn {{ $buttonClass ?? 'btn-sm btn-outline-danger' }}" data-toggle="modal" data-target="#reportModal{{ $reportableType }}{{ $reportableId }}">
    <i class="fas fa-flag mr-1"></i> {{ $buttonText ?? 'Report' }}
</button>

@once
    @push('modals')
        @include('components.report-modal', [
            'reportableType' => $reportableType,
            'reportableId' => $reportableId
        ])
    @endpush
@endonce







