<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_brand',
        'device_model',
        'os_version',
        'ip_address',
        'fcm_token',
        'email',
        'phone',
        'ban_reason',
        'banned_at',
        'unban_at',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'unban_at' => 'datetime',
    ];

    /**
     * Get the user associated with the banned device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ban history records for this device.
     */
    public function banHistories(): HasMany
    {
        return $this->hasMany(BanHistory::class, 'banned_device_id');
    }

    /**
     * Check if the device is currently banned.
     */
    public function isActive(): bool
    {
        if ($this->unban_at !== null && now()->greaterThan($this->unban_at)) {
            return false; // Ban period has expired
        }
        return true; // Still banned
    }

    /**
     * Unban this device.
     */
    public function unban($reason = null, $performedBy = null): void
    {
        $this->unban_at = now();
        $this->save();

        // Record in history if BanHistory model exists
        if (class_exists(BanHistory::class)) {
            BanHistory::create([
                'user_id' => $this->user_id,
                'banned_device_id' => $this->id,
                'action' => 'unban',
                'performed_by' => $performedBy,
                'reason' => $reason ?? 'Manual unban',
            ]);
        }
    }
}
