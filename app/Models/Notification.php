<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'read',
        'type',
        'target_type',
        'target_id',
    ];
    protected $casts = [
        'message' => 'array', // Automatically decode JSON to an array
    ];
    protected $appends = ['target'];

    /** Types whose tap opens the points / transactions screen. */
    public const POINT_TYPES = ['pointIn', 'pointOut', 'points_approved', 'point_sent', 'point_purchase',
        'purchase', 'purchased', 'refund', 'transfer', 'admin_grant'];

    /**
     * Where tapping this notification leads: ['type' => post|user|points|
     * password|email|notifications, 'id' => ?int]. Stored when the sender
     * knows it; otherwise derived from the type and a [post_id:N] tag.
     */
    public function getTargetAttribute(): array
    {
        if (!empty($this->attributes['target_type'])) {
            return ['type' => $this->attributes['target_type'], 'id' => $this->attributes['target_id'] ?? null];
        }
        return self::deriveTarget((string) ($this->attributes['type'] ?? ''), (string) ($this->attributes['message'] ?? ''));
    }

    public static function deriveTarget(string $type, string $rawMessage): array
    {
        if (preg_match('/\[post_id:(\d+)\]/', stripslashes($rawMessage), $m)) {
            return ['type' => 'post', 'id' => (int) $m[1]];
        }
        if (in_array($type, self::POINT_TYPES, true)) return ['type' => 'points', 'id' => null];
        if ($type === 'password') return ['type' => 'password', 'id' => null];
        if ($type === 'email') return ['type' => 'email', 'id' => null];
        return ['type' => 'notifications', 'id' => null];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(point_transactions::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reportable');
    }

    public function servicePosts()
    {
        return $this->hasMany(ServicePost::class, 'sub_categories_id');
    }

    public function sub_categories()
    {
        return $this->hasMany(Sub_categories::class);
    }

}
