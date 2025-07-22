<!-- resources/views/components/report-modal.blade.php -->
<div class="modal fade" id="reportModal{{ $reportableType }}{{ $reportableId }}" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-flag mr-2"></i> Report {{ $reportableType === 'user' ? 'User' : 'Service Post' }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">{{('components\report-modal._times_') }}</span>
                </button>
            </div>
            <form action="{{ route('report.' . $reportableType, $reportableId) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">{{('components\report-modal.reason_for_reporting') }}</label>
                        <select class="form-control" id="reason" name="reason" required>
                            <option value="">{{('components\report-modal._select_reason_') }}</option>
                            <option value="inappropriate_content">{{('components\report-modal.inappropriate_content') }}</option>
                            <option value="fake_information">{{('components\report-modal.fake_information') }}</option>
                            <option value="scam">{{('components\report-modal.scam_or_fraud') }}</option>
                            <option value="other">{{('components\report-modal.other') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        All reports are reviewed by our moderation team. False reports may result in account suspension.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{('components\report-modal.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{('components\report-modal.submit_report') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>







