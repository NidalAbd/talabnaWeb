<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoPerformance extends Model
{
    use HasFactory;

    protected $table = 'seo_performance';

    protected $fillable = [
        'url',
        'page_type',
        'entity_id',
        'entity_type',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'top_keywords',
        'countries',
        'devices',
        'trend',
        'trend_change',
        'date',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'decimal:2',
        'position' => 'decimal:2',
        'top_keywords' => 'array',
        'countries' => 'array',
        'devices' => 'array',
        'trend_change' => 'decimal:2',
        'date' => 'date',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByPageType($query, $type)
    {
        return $query->where('page_type', $type);
    }

    public function scopeForUrl($query, $url)
    {
        return $query->where('url', $url);
    }

    public function scopeTrending($query, $direction = 'up')
    {
        return $query->where('trend', $direction);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->orderByDesc('clicks')->limit($limit);
    }

    public function getFormattedCtrAttribute()
    {
        return number_format($this->ctr, 2) . '%';
    }

    public function getFormattedPositionAttribute()
    {
        return number_format($this->position, 1);
    }
}
