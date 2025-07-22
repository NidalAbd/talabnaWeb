<!-- Points Used Statistics Component -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Points Used Statistics
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box bg-gradient-primary">
                    <span class="info-box-icon"><i class="far fa-calendar-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('components\points-used-stats.today') }}</span>
                        <span class="info-box-number">{{ number_format($pointsUsedToday) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $pointsUsedToday > 0 && $pointsUsedLifetime > 0 ? min(100, ($pointsUsedToday / $pointsUsedLifetime) * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $pointsUsedToday > 0 && $pointsUsedLifetime > 0 ? number_format(($pointsUsedToday / $pointsUsedLifetime) * 100, 1) : 0 }}% of all points used
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-calendar-week"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('components\points-used-stats.last_7_days') }}</span>
                        <span class="info-box-number">{{ number_format($pointsUsedWeek) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $pointsUsedWeek > 0 && $pointsUsedLifetime > 0 ? min(100, ($pointsUsedWeek / $pointsUsedLifetime) * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $pointsUsedWeek > 0 && $pointsUsedLifetime > 0 ? number_format(($pointsUsedWeek / $pointsUsedLifetime) * 100, 1) : 0 }}% of all points used
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('components\points-used-stats.this_month') }}</span>
                        <span class="info-box-number">{{ number_format($pointsUsedMonth) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $pointsUsedMonth > 0 && $pointsUsedLifetime > 0 ? min(100, ($pointsUsedMonth / $pointsUsedLifetime) * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $pointsUsedMonth > 0 && $pointsUsedLifetime > 0 ? number_format(($pointsUsedMonth / $pointsUsedLifetime) * 100, 1) : 0 }}% of all points used
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-12 mt-3">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="far fa-calendar-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('components\points-used-stats.this_year') }}</span>
                        <span class="info-box-number">{{ number_format($pointsUsedYear) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $pointsUsedYear > 0 && $pointsUsedLifetime > 0 ? min(100, ($pointsUsedYear / $pointsUsedLifetime) * 100) : 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $pointsUsedYear > 0 && $pointsUsedLifetime > 0 ? number_format(($pointsUsedYear / $pointsUsedLifetime) * 100, 1) : 0 }}% of all points used
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-sm-6 col-12 mt-3">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-history"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{('components\points-used-stats.lifetime') }}</span>
                        <span class="info-box-number">{{ number_format($pointsUsedLifetime) }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Total points ever used in the system
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







