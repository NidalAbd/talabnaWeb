<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
        'image_url',
        'deep_link',
        'total_recipients',
        'successful_count',
        'failed_count',
        'admin_id'
    ];

    /**
     * Get the admin that sent the notification.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Calculate the success rate percentage.
     *
     * @return float
     */
    public function successRate()
    {
        if ($this->total_recipients <= 0) {
            return 0;
        }

        return ($this->successful_count / $this->total_recipients) * 100;
    }

    /**
     * Check if the notification was fully successful.
     *
     * @return bool
     */
    public function isFullySuccessful()
    {
        return $this->failed_count === 0 && $this->total_recipients > 0;
    }

    /**
     * Check if the notification completely failed.
     *
     * @return bool
     */
    public function completelyFailed()
    {
        return $this->successful_count === 0 && $this->total_recipients > 0;
    }

    /**
     * Get a human-readable description of the notification's success.
     *
     * @return string
     */
    public function statusDescription()
    {
        if ($this->isFullySuccessful()) {
            return 'Complete Success';
        } elseif ($this->completelyFailed()) {
            return 'Complete Failure';
        } else {
            return 'Partial Success (' . round($this->successRate()) . '%)';
        }
    }
}
