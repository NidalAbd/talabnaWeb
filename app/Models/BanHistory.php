<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'banned_device_id',
        'action',
        'performed_by',
        'reason',
    ];

    /**
     * Get the user associated with this ban history record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the banned device associated with this ban history record.
     */
    public function bannedDevice(): BelongsTo
    {
        return $this->belongsTo(BannedDevice::class);
    }

    /**
     * Get the admin who performed this action.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
