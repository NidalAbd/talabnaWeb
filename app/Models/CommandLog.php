<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandLog extends Model
{
    protected $fillable = [
        'command',
        'status',
        'started_at',
        'finished_at',
        'records_affected',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function scopeForCommand($query, string $name)
    {
        return $query->where('command', $name);
    }

    public function scopeInTimeRange($query, $from)
    {
        return $query->where('started_at', '>=', $from);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('started_at', 'desc')->limit($limit);
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->finished_at->diffInSeconds($this->started_at);
    }
}
