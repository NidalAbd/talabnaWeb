@extends('adminlte::page')

@section('title', 'Marketing Notifications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-bell text-primary mr-2"></i> Marketing Notifications</h1>
        <div>
            <a href="{{ route('admin.notifications.marketing.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize select2
            $('.select2').select2({
                theme: 'bootstrap4',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route("admin.api.users.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.users.map(function(user) {
                                return {
                                    id: user.id,
                                    text: user.user_name + ' (' + user.email + ')'
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });

            // Character counters for title and body
            $('#title, #title_specific').on('input', function() {
                let max = 100;
                let current = $(this).val().length;
                let remaining = max - current;

                let counter = $(this).closest('.form-group').find('.character-counter');
                if (counter.length === 0) {
                    $(this).closest('.form-group').append('<small class="text-muted character-counter float-right">{{('admin\notifications\marketing\send._remaining_characters_remaining') }}</small>');
                } else {
                    counter.text(remaining + ' characters remaining');
                }

                if (remaining < 20) {
                    counter.removeClass('text-muted').addClass('text-danger');
                } else {
                    counter.removeClass('text-danger').addClass('text-muted');
                }
            });

            $('#body, #body_specific').on('input', function() {
                let max = 500;
                let current = $(this).val().length;
                let remaining = max - current;

                let counter = $(this).closest('.form-group').find('.character-counter');
                if (counter.length === 0) {
                    $(this).closest('.form-group').append('<small class="text-muted character-counter float-right">{{('admin\notifications\marketing\send._remaining_characters_remaining') }}</small>');
                } else {
                    counter.text(remaining + ' characters remaining');
                }

                if (remaining < 50) {
                    counter.removeClass('text-muted').addClass('text-danger');
                } else {
                    counter.removeClass('text-danger').addClass('text-muted');
                }
            });
        });

        // Select all users
        $('#select-all').on('change', function() {
            $('.user-checkbox').prop('checked', $(this).is(':checked'));
            updateSelectedCount();
        });

        // Update counter when individual checkboxes change
        $(document).on('change', '.user-checkbox', function() {
            updateSelectedCount();

// Update "select all" checkbox
            let allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length;
            $('#select-all').prop('checked', allChecked);
        });

        // Update selected count
        function updateSelectedCount() {
            let count = $('.user-checkbox:checked').length;
            $('#selected-count').text(count);
        }

        // Select users button
        $('#select-users').on('click', function() {
            let selectedUsers = [];

            $('.user-checkbox:checked').each(function() {
                selectedUsers.push($(this).val());
            });

            if (selectedUsers.length > 0) {
// Clear previous selections
                $('#user_ids').empty();

// Add new selections
                selectedUsers.forEach(function(userId) {
                    let userName = $('#user-' + userId).closest('tr').find('td:nth-child(2)').text();
                    let userEmail = $('#user-' + userId).closest('tr').find('td:nth-child(3)').text();
                    let option = new Option(userName + ' (' + userEmail + ')', userId, true, true);
                    $('#user_ids').append(option);
                });

// Trigger change event
                $('#user_ids').trigger('change');

// Close modal
                $('#userSearchModal').modal('hide');
            } else {
                alert('Please select at least one user');
            }
        });

        // Reset filters
        $('#reset-filters').on('click', function() {
            $('#role-filter, #country-filter, #status-filter, #last-active-filter').val('');
            $('#user-results').empty();
            $('#select-all').prop('checked', false);
            updateSelectedCount();
        });

        // Advanced user selection
        $('#open-user-search').on('click', function(e) {
            e.preventDefault();
            $('#userSearchModal').modal('show');

// Load roles
            $.ajax({
                url: '{{ route("admin.api.roles.list") }}',
                method: 'GET',
                success: function(data) {
                    let roleSelect = $('#role-filter');
                    roleSelect.empty().append('<option value="">{{('admin\notifications\marketing\send.all_roles') }}</option>{{('admin\notifications\marketing\send._data_foreach_fu') }}<option value="' + role.id + '">{{('admin\notifications\marketing\send._role_name_') }}</option>');
                    });
                }
            });

// Load countries
            $.ajax({
                url: '{{ route("admin.api.countries.list") }}',
                method: 'GET',
                success: function(data) {
                    let countrySelect = $('#country-filter');
                    countrySelect.empty().append('<option value="">{{('admin\notifications\marketing\send.all_countries') }}</option>{{('admin\notifications\marketing\send._data_foreach_fu') }}<option value="' + country.id + '">{{('admin\notifications\marketing\send._country_name_') }}</option>');
                    });
                }
            });
        });

        // Apply filters for user search
        $('#apply-filters').on('click', function() {
            let filters = {
                role: $('#role-filter').val(),
                country: $('#country-filter').val(),
                status: $('#status-filter').val(),
                lastActive: $('#last-active-filter').val()
            };

            $.ajax({
                url: '{{ route("admin.api.users.filter") }}',
                method: 'GET',
                data: filters,
                success: function(data) {
                    let tbody = $('#user-results');
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.append('<tr><td colspan="5" class="text-center">{{('admin\notifications\marketing\send.no_users_found_matching_your_criteria') }}</td></tr>');
                        return;
                    }

                    data.forEach(function(user) {
                        let statusClass = user.is_active === 'active' ? 'success' : 'danger';
                        let statusText = user.is_active === 'active' ? 'Active' : 'Inactive';

                        let row = `
<tr>
    <td>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input user-checkbox" id="user-${user.id}" value="${user.id}">
            <label class="custom-control-label" for="user-${user.id}"></label>
        </div>
    </td>
    <td>{{('admin\notifications\marketing\send._user_user_name_') }}</td>
    <td>{{('admin\notifications\marketing\send._user_email_') }}</td>
    <td>{{('admin\notifications\marketing\send._user_roles_map_r_r_name_join_') }}</td>
    <td><span class="badge badge-${statusClass}">{{('admin\notifications\marketing\send._statustext_') }}</span></td>
</tr>
`;

                        tbody.append(row);
                    });

// Update counter
                    updateSelectedCount();
                }
            });
        });

        // Live preview functionality for the notification
        function updatePreview() {
// Update title
            let title = $('#title').val() || 'Notification Title';
            $('#preview-title').text(title);

// Update body
            let body = $('#body').val() || 'Notification message will appear here.';
            $('#preview-body').text(body);

// Update image
            let imageUrl = $('#image_url').val();
            if (imageUrl) {
                $('#preview-image').attr('src', imageUrl).show();
                $('#preview-image-container').show();
            } else {
                $('#preview-image').hide();
                $('#preview-image-container').hide();
            }
        }

        // Initialize preview on load
        updatePreview();

        // Update preview when inputs change
        $('#title, #body, #image_url').on('input', updatePreview);

        // Show preview modal
        $('.preview-btn').on('click', function(e) {
            e.preventDefault();
            updatePreview();
            $('#previewModal').modal('show');
        });

        // Preview for specific users form
        $('.preview-specific-btn').on('click', function(e) {
            e.preventDefault();
// Update title
            let title = $('#title_specific').val() || 'Notification Title';
            $('#preview-title').text(title);

// Update body
            let body = $('#body_specific').val() || 'Notification message will appear here.';
            $('#preview-body').text(body);

// Update image
            let imageUrl = $('#image_url_specific').val();
            if (imageUrl) {
                $('#preview-image').attr('src', imageUrl).show();
                $('#preview-image-container').show();
            } else {
                $('#preview-image').hide();
                $('#preview-image-container').hide();
            }

            $('#previewModal').modal('show');
        });
    </script>
@stop



@section('content')
    <div class="container-fluid">
        <!-- Dashboard Summary Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow">
                    <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('admin\notifications\marketing\send.users_with_fcm_tokens') }}</span>
                        <span class="info-box-number">{{ number_format($fcmUsersCount) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Potential notification reach
                        </span>
                    </div>
                </div>
            </div>

            @if(isset($notificationResult) && $notificationResult['success'])
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\notifications\marketing\send.successfully_sent') }}</span>
                            <span class="info-box-number">{{ number_format($notificationResult['successful']) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: {{ $notificationResult['total'] > 0 ? ($notificationResult['successful'] / $notificationResult['total']) * 100 : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $notificationResult['total'] > 0 ? round(($notificationResult['successful'] / $notificationResult['total']) * 100) : 0 }}% success rate
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow">
                        <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\notifications\marketing\send.failed_deliveries') }}</span>
                            <span class="info-box-number">{{ number_format($notificationResult['failed']) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: {{ $notificationResult['total'] > 0 ? ($notificationResult['failed'] / $notificationResult['total']) * 100 : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $notificationResult['total'] > 0 ? round(($notificationResult['failed'] / $notificationResult['total']) * 100) : 0 }}% failure rate
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow">
                        <span class="info-box-icon bg-info"><i class="fas fa-paper-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{('admin\notifications\marketing\send.total_attempts') }}</span>
                            <span class="info-box-number">{{ number_format($notificationResult['total']) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Campaign completion
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Notification Result Alert -->
        @if(isset($notificationResult))
            @if($notificationResult['success'])
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <h5><i class="icon fas fa-check"></i> Notification Campaign Completed!</h5>
                    <p class="mb-0">Your notification "{{ $notificationResult['title'] }}" has been sent to {{ number_format($notificationResult['total']) }} users with {{ number_format($notificationResult['successful']) }} successful deliveries.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">{{('admin\notifications\marketing\send._times_') }}</span>
                    </button>
                </div>
            @else
                <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                    <h5><i class="icon fas fa-ban"></i> Notification Campaign Failed!</h5>
                    <p class="mb-0">{{ $notificationResult['message'] }}</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">{{('admin\notifications\marketing\send._times_') }}</span>
                    </button>
                </div>
            @endif
        @endif

        <!-- Main Card -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bullhorn mr-1"></i>
                            Send Marketing Notification to All Users
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.notifications.marketing.send-all') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title">Notification Title <span class="text-danger">{{('admin\notifications\marketing\send._') }}</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title" name="title" value="{{ old('title') }}"
                                           placeholder="Enter notification title" maxlength="100" required>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">{{('admin\notifications\marketing\send.keep_titles_short_and_attention_grabbing') }}</small>
                            </div>

                            <div class="form-group">
                                <label for="body">Notification Message <span class="text-danger">{{('admin\notifications\marketing\send._') }}</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                    </div>
                                    <textarea class="form-control @error('body') is-invalid @enderror"
                                              id="body" name="body" rows="4"
                                              placeholder="Enter notification message" maxlength="500" required>{{ old('body') }}</textarea>
                                    @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">{{('admin\notifications\marketing\send.provide_clear_and_valuable_information_') }}</small>
                            </div>

                            <div class="form-group">
                                <label for="image_url">{{('admin\notifications\marketing\send.image_url_optional_') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    </div>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror"
                                           id="image_url" name="image_url" value="{{ old('image_url') }}"
                                           placeholder="https://example.com/image.jpg">
                                    @error('image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">{{('admin\notifications\marketing\send.adding_an_image_can_increase_engagement_') }}</small>
                            </div>

                            <div class="form-group">
                                <label for="deep_link">{{('admin\notifications\marketing\send.deep_link_optional_') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('deep_link') is-invalid @enderror"
                                           id="deep_link" name="deep_link" value="{{ old('deep_link') }}"
                                           placeholder="https://talbna.cloud/api/deep-link/reels/1026">
                                    @error('deep_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Use format: <code>{{('admin\notifications\marketing\send.https_talbna_cloud_api_deep_link_type') }}</code> where type is 'reels', 'service-post', 'user', or 'category'
                                </small>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> This notification will be sent to <strong>{{ number_format($fcmUsersCount) }}</strong> active users with registered FCM tokens.
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary preview-btn">
                                    <i class="fas fa-eye mr-1"></i> Preview
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-1"></i> Send Notification to All Users
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-check mr-1"></i>
                            Send Notification to Specific Users
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.notifications.marketing.send-specific') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title_specific">Notification Title <span class="text-danger">{{('admin\notifications\marketing\send._') }}</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title_specific" name="title" value="{{ old('title') }}"
                                           placeholder="Enter notification title" maxlength="100" required>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="body_specific">Notification Message <span class="text-danger">{{('admin\notifications\marketing\send._') }}</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                    </div>
                                    <textarea class="form-control @error('body') is-invalid @enderror"
                                              id="body_specific" name="body" rows="4"
                                              placeholder="Enter notification message" maxlength="500" required>{{ old('body') }}</textarea>
                                    @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="user_ids">Select Users <span class="text-danger">{{('admin\notifications\marketing\send._') }}</span></label>
                                <div class="d-flex mb-2">
                                    <select class="form-control select2 @error('user_ids') is-invalid @enderror"
                                            id="user_ids" name="user_ids[]" multiple="multiple"
                                            data-placeholder="Search users..." style="width: 100%;" required>
                                        <!-- Users will be populated via Select2 -->
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary ml-2" id="open-user-search">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                                @error('user_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{('admin\notifications\marketing\send.search_and_select_specific_users_or_use_') }}</small>
                            </div>

                            <div class="form-group">
                                <label for="image_url_specific">{{('admin\notifications\marketing\send.image_url_optional_') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    </div>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror"
                                           id="image_url_specific" name="image_url" value="{{ old('image_url') }}"
                                           placeholder="https://example.com/image.jpg">
                                    @error('image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="deep_link_specific">{{('admin\notifications\marketing\send.deep_link_optional_') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('deep_link') is-invalid @enderror"
                                           id="deep_link_specific" name="deep_link" value="{{ old('deep_link') }}"
                                           placeholder="talabna://feature/123">
                                    @error('deep_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-info preview-specific-btn">
                                    <i class="fas fa-eye mr-1"></i> Preview
                                </button>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-paper-plane mr-1"></i> Send to Selected Users
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification History -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i>
                    Recent Notification Campaigns
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <a href="{{ route('admin.notifications.marketing.history') }}" class="btn btn-tool">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-light">
                        <tr>
                            <th>{{('admin\notifications\marketing\send.id') }}</th>
                            <th>{{('admin\notifications\marketing\send.title') }}</th>
                            <th>{{('admin\notifications\marketing\send.sent_by') }}</th>
                            <th>{{('admin\notifications\marketing\send.recipients') }}</th>
                            <th>{{('admin\notifications\marketing\send.success_rate') }}</th>
                            <th>{{('admin\notifications\marketing\send.date_sent') }}</th>
                            <th>{{('admin\notifications\marketing\send.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($notificationLogs) && count($notificationLogs) > 0)
                            @foreach($notificationLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                {{ $log->title }}
                                            </span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            @if($log->admin && $log->admin->photos && $log->admin->photos->count() > 0)
                                                @php
                                                    $photo = $log->admin->photos->first();
                                                    $imgSrc = $photo->is_external ? $photo->src : asset($photo->src);
                                                @endphp
                                                <img class="img-circle img-size-32 mr-2" src="{{ $imgSrc }}" alt="{{ $log->admin->user_name }}">
                                            @else
                                                <img class="img-circle img-size-32 mr-2" src="{{ asset('vendor/adminlte/dist/img/user-default.jpg') }}" alt="Admin Image">
                                            @endif
                                            <span>{{ $log->id }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($log->recipients) }}</td>
                                    <td>
                                        @php
                                            $successRate = $log->total_recipients > 0 ? ($log->successful_count / $log->total_recipients) * 100 : 0;
                                            $rateColor = $successRate > 90 ? 'success' : ($successRate > 70 ? 'info' : ($successRate > 50 ? 'warning' : 'danger'));
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $rateColor }}" role="progressbar"
                                                 style="width: {{ $successRate }}%;"
                                                 aria-valuenow="{{ $successRate }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($successRate) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $log->date_sent }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal{{ $log->id }}">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="alert alert-info m-0">
                                        <i class="fas fa-info-circle mr-1"></i> No notification history found
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @if(isset($notificationLogs) && $notificationLogs->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $notificationLogs->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Notification Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="previewModalLabel"><i class="fas fa-mobile-alt mr-2"></i>{{('admin\notifications\marketing\send.notification_preview') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{('admin\notifications\marketing\send._times_') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="device-mockup">
                            <div class="device-frame">
                                <div class="device-header">
                                    <div class="device-status-bar"></div>
                                </div>
                                <div class="device-content">
                                    <div class="notification-preview">
                                        <div class="notification-preview-header">
                                            <img src="{{ asset('img/logo.png') }}" alt="App Logo" class="notification-logo">
                                            <div class="notification-app-info">
                                                <div class="notification-app-name">{{('admin\notifications\marketing\send.talabna') }}</div>
                                                <div class="notification-time">{{('admin\notifications\marketing\send.just_now') }}</div>
                                            </div>
                                        </div>
                                        <div class="notification-preview-body">
                                            <div class="notification-title" id="preview-title">{{('admin\notifications\marketing\send.notification_title') }}</div>
                                            <div class="notification-message" id="preview-body">{{('admin\notifications\marketing\send.notification_message_will_appear_here_') }}</div>
                                        </div>
                                        <div class="notification-preview-image" id="preview-image-container">
                                            <img src="" alt="Notification Image" id="preview-image" style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> This is a preview of how your notification might appear on a user's device. Actual appearance may vary based on device type and OS version.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{('admin\notifications\marketing\send.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Search Modal -->
    <div class="modal fade" id="userSearchModal" tabindex="-1" role="dialog" aria-labelledby="userSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="userSearchModalLabel"><i class="fas fa-users mr-2"></i>{{('admin\notifications\marketing\send.advanced_user_selection') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">{{('admin\notifications\marketing\send._times_') }}</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\notifications\marketing\send.filter_by_role') }}</label>
                                <select class="form-control" id="role-filter">
                                    <option value="">{{('admin\notifications\marketing\send.all_roles') }}</option>
                                    <!-- Roles will be populated via JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\notifications\marketing\send.filter_by_country') }}</label>
                                <select class="form-control" id="country-filter">
                                    <option value="">{{('admin\notifications\marketing\send.all_countries') }}</option>
                                    <!-- Countries will be populated via JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\notifications\marketing\send.activity_status') }}</label>
                                <select class="form-control" id="status-filter">
                                    <option value="">{{('admin\notifications\marketing\send.all_statuses') }}</option>
                                    <option value="active">{{('admin\notifications\marketing\send.active') }}</option>
                                    <option value="inactive">{{('admin\notifications\marketing\send.inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{('admin\notifications\marketing\send.last_active') }}</label>
                                <select class="form-control" id="last-active-filter">
                                    <option value="">{{('admin\notifications\marketing\send.any_time') }}</option>
                                    <option value="7">{{('admin\notifications\marketing\send.last_7_days') }}</option>
                                    <option value="30">{{('admin\notifications\marketing\send.last_30_days') }}</option>
                                    <option value="90">{{('admin\notifications\marketing\send.last_90_days') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" id="apply-filters">
                            <i class="fas fa-filter mr-1"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="reset-filters">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>
                    <div class="user-search-results mt-3">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th width="50px">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all">
                                        <label class="custom-control-label" for="select-all"></label>
                                    </div>
                                </th>
                                <th>{{('admin\notifications\marketing\send.user') }}</th>
                                <th>{{('admin\notifications\marketing\send.email') }}</th>
                                <th>{{('admin\notifications\marketing\send.role') }}</th>
                                <th>{{('admin\notifications\marketing\send.status') }}</th>
                            </tr>
                            </thead>
                            <tbody id="user-results">
                            <!-- Users will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{('admin\notifications\marketing\send.cancel') }}</button>
                    <button type="button" class="btn btn-info" id="select-users">
                        <i class="fas fa-check mr-1"></i> Select Users (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* User info styling */
        .user-info {
            display: flex;
            align-items: center;
        }

        .img-circle {
            object-fit: cover;
            width: 32px;
            height: 32px;
        }

        /* Progress bar styling */
        .progress {
            height: 4px;
            margin: 5px 0;
            border-radius: 2px;
        }

        .info-box .progress-bar {
            height: 4px;
        }

        /* Card styling */
        .card {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .card .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        /* Notification preview device mockup */
        .device-mockup {
            width: 300px;
            margin: 0 auto;
            border: 10px solid #333;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background-color: #fff;
        }

        .device-frame {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #f8f9fa;
        }

        .device-header {
            height: 20px;
            background-color: #333;
        }

        .device-status-bar {
            height: 100%;
            background-color: #333;
        }

        .device-content {
            padding: 10px;
        }

        .notification-preview {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 10px;
        }

        .notification-preview-header {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .notification-logo {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            margin-right: 10px;
        }

        .notification-app-info {
            flex: 1;
        }

        .notification-app-name {
            font-size: 12px;
            font-weight: bold;
        }

        .notification-time {
            font-size: 10px;
            color: #777;
        }

        .notification-preview-body {
            padding: 10px;
        }

        .notification-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notification-message {
            font-size: 12px;
            color: #333;
        }

        .notification-preview-image {
            width: 100%;
        }

        .notification-preview-image img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
        }

        /* Select2 customization */
        .select2-container--default .select2-selection--multiple {
            border-color: #ced4da;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@stop







