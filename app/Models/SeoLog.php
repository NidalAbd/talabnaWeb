<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoLog extends Model
{
    use HasFactory;

    protected $table = 'seo_logs';

    protected $fillable = [
        'action',
        'source',
        'status',
        'details',
        'error_message',
        'records_processed',
        'records_created',
        'records_updated',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected $casts = [
        'details' => 'array',
        'records_processed' => 'integer',
        'records_created' => 'integer',
        'records_updated' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function start()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function complete($recordsProcessed = 0, $recordsCreated = 0, $recordsUpdated = 0)
    {
        $startedAt = $this->started_at ?? now();
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($startedAt),
            'records_processed' => $recordsProcessed,
            'records_created' => $recordsCreated,
            'records_updated' => $recordsUpdated,
        ]);
    }

    public function fail($errorMessage)
    {
        $startedAt = $this->started_at ?? now();
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($startedAt),
            'error_message' => $errorMessage,
        ]);
    }

    public static function createLog($action, $source = 'google_search_console', $details = null)
    {
        return self::create([
            'action' => $action,
            'source' => $source,
            'status' => 'pending',
            'details' => $details,
        ]);
    }
}
