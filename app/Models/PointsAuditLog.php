<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointsAuditLog extends Model
{
    use HasFactory;

    protected $table = 'points_audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * Get the user that owns this audit log entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referenced model (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get logs for a specific action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs for a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get only credit transactions (positive amount)
     */
    public function scopeCredits($query)
    {
        return $query->where('amount', '>', 0);
    }

    /**
     * Scope to get only debit transactions (negative amount)
     */
    public function scopeDebits($query)
    {
        return $query->where('amount', '<', 0);
    }

    /**
     * Check if this is a credit transaction
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if this is a debit transaction
     */
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get the absolute amount (always positive)
     */
    public function getAbsoluteAmountAttribute(): int
    {
        return abs($this->amount);
    }

    /**
     * Get a human-readable description of the action
     */
    public function getActionDescriptionAttribute(): string
    {
        return match ($this->action) {
            'transfer_sent' => 'Points Sent',
            'transfer_received' => 'Points Received',
            'purchase' => 'Points Purchased',
            'refund' => 'Points Refunded',
            'admin_grant' => 'Admin Grant',
            'admin_deduct' => 'Admin Deduction',
            'badge_purchase' => 'Badge Purchase',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
