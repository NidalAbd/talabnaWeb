<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoKeyword extends Model
{
    use HasFactory;

    protected $table = 'seo_keywords';

    protected $fillable = [
        'keyword',
        'language',
        'country',
        'device',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'top_page',
        'trend',
        'trend_change',
        'priority',
        'is_tracked',
        'date',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'decimal:2',
        'position' => 'decimal:2',
        'trend_change' => 'decimal:2',
        'is_tracked' => 'boolean',
        'date' => 'date',
    ];

    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeTrending($query, $direction = 'up')
    {
        return $query->where('trend', $direction);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
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
