<!-- resources/views/components/device-info-modal.blade.php -->
<div class="modal fade" id="deviceModal{{ $device->id }}" tabindex="-1" role="dialog" aria-labelledby="deviceModalLabel{{ $device->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="deviceModalLabel{{ $device->id }}">
                    <i class="fas fa-mobile-alt mr-2"></i> Device Information
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">{{ __('components\device-info-modal._times_') }}</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('components\device-info-modal.device_details') }}</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 40%">{{ __('components\device-info-modal.device_id') }}</th>
                                        <td><code>{{ $device->field</code></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.brand') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.model') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.os_version') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.ip_address') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.fcm_token') }}</th>
                                        <td>
                                            @if($device->fcm_token)
                                                <code class="text-truncate d-block" style="max-width: 220px;">{{ $device->field</code>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-outline card-danger h-100">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('components\device-info-modal.ban_information') }}</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 40%">{{ __('components\device-info-modal.status') }}</th>
                                        <td>
                                            @if($device->isActive())
                                                <span class="badge badge-danger">{{ __('components\device-info-modal.banned') }}</span>
                                            @else
                                                <span class="badge badge-success">{{ __('components\device-info-modal.unbanned') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.banned_at') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.unbanned_at') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.reason') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.associated_user') }}</th>
                                        <td>
                                            @if($device->user)
                                                <a href="{{ route('users.show', $device->user_id) }}">
                                                    {{ $device->user->name }} (ID: {{ $device->user_id }})
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.email') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('components\device-info-modal.phone') }}</th>
                                        <td>{{ $device->field</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if($device->isActive())
                    <form action="{{ route('devices.unban', $device->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to unban this device?')">
                            <i class="fas fa-check-circle mr-1"></i> Unban Device
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('components\device-info-modal.close') }}</button>
            </div>
        </div>
    </div>
</div>
