<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'starts_at',
        'expires_at',
        'points_paid',
        'status',
        'features_snapshot',
        'usage',
        'extras',
        'auto_renew',
        'scheduled_plan_id',
        'replaced_by_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'features_snapshot' => 'array',
        'usage' => 'array',
        'extras' => 'array',
        'auto_renew' => 'boolean',
        'points_paid' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Check if subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    /**
     * Get remaining days.
     */
    public function getRemainingDays(): int
    {
        if (!$this->isActive()) return 0;
        return (int) Carbon::now()->diffInDays($this->expires_at, false);
    }

    /**
     * Get a feature value from the snapshot.
     */
    public function getFeature(string $key, $default = null)
    {
        $value = $this->features_snapshot[$key] ?? $default;
        // Top-up packs add to numeric allowances for the rest of this period.
        $extra = (int) (($this->extras ?? [])[$key] ?? 0);
        if ($extra > 0 && (is_int($value) || is_numeric($value) || $value === null || $value === false)) {
            return (int) $value + $extra;
        }
        return $value;
    }

    public function scheduledPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'scheduled_plan_id');
    }

    /** Fraction of the paid period still unused, 0..1. */
    public function unusedFraction(): float
    {
        $total = max(1, $this->starts_at->diffInSeconds($this->expires_at));
        $left = max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
        return min(1.0, $left / $total);
    }

    /**
     * Check if user has used up a feature limit.
     */
    public function hasFeatureRemaining(string $featureKey, string $usageKey): bool
    {
        $limit = $this->getFeature($featureKey, 0);
        if ($limit === true) return true; // unlimited boolean feature
        if ($limit === 0 || $limit === false) return false;

        $used = ($this->usage[$usageKey] ?? 0);
        return $used < $limit;
    }

    /**
     * Increment a usage counter.
     */
    public function incrementUsage(string $usageKey, int $amount = 1): void
    {
        $usage = $this->usage ?? [];
        $usage[$usageKey] = ($usage[$usageKey] ?? 0) + $amount;
        $this->usage = $usage;
        $this->save();
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope for expired subscriptions that need processing.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', Carbon::now());
    }
}
