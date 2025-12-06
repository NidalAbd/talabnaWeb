<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoIssue extends Model
{
    use HasFactory;

    protected $table = 'seo_issues';

    protected $fillable = [
        'url',
        'issue_type',
        'severity',
        'title',
        'description',
        'details',
        'status',
        'resolution_notes',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'details' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('issue_type', $type);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeForUrl($query, $url)
    {
        return $query->where('url', $url);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function markAsResolved($notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsIgnored($notes = null)
    {
        $this->update([
            'status' => 'ignored',
            'resolution_notes' => $notes,
        ]);
    }

    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
        ]);
    }

    public static function findOrCreateIssue($url, $issueType, $title, $severity = 'warning', $details = null)
    {
        return self::firstOrCreate(
            [
                'url' => $url,
                'issue_type' => $issueType,
                'status' => 'open',
            ],
            [
                'title' => $title,
                'severity' => $severity,
                'details' => $details,
                'detected_at' => now(),
            ]
        );
    }
}
