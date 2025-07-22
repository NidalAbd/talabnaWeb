<!-- resources/views/components/device-info-modal.blade.php -->
<div class="modal fade" id="deviceModal{{ $device->id }}" tabindex="-1" role="dialog" aria-labelledby="deviceModalLabel{{ $device->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="deviceModalLabel{{ $device->id }}">
                    <i class="fas fa-mobile-alt mr-2"></i> Device Information
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header">
                                <h3 class="card-title">Device Details</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 40%">Device ID</th>
                                        <td><code>{{ $device->device_id }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Brand</th>
                                        <td>{{ $device->device_brand ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Model</th>
                                        <td>{{ $device->device_model ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>OS Version</th>
                                        <td>{{ $device->os_version ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>IP Address</th>
                                        <td>{{ $device->ip_address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>FCM Token</th>
                                        <td>
                                            @if($device->fcm_token)
                                                <code class="text-truncate d-block" style="max-width: 220px;">{{ $device->fcm_token }}</code>
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
                                <h3 class="card-title">Ban Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th style="width: 40%">Status</th>
                                        <td>
                                            @if($device->isActive())
                                                <span class="badge badge-danger">Banned</span>
                                            @else
                                                <span class="badge badge-success">Unbanned</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Banned At</th>
                                        <td>{{ $device->banned_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Unbanned At</th>
                                        <td>{{ $device->unban_at ? $device->unban_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Reason</th>
                                        <td>{{ $device->ban_reason ?? 'No reason provided' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Associated User</th>
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
                                        <th>Email</th>
                                        <td>{{ $device->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $device->phone ?? 'N/A' }}</td>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
