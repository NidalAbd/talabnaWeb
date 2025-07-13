<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'point_cost',
        'is_active',
        'feature_type',
        'icon',
        'color'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'point_cost' => 'integer'
    ];

    public function packages()
    {
        return $this->belongsToMany(PointPackage::class, 'package_features');
    }

    public function userFeatures()
    {
        return $this->hasMany(UserPremiumFeature::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('feature_type', $type);
    }

    public function getFormattedCostAttribute()
    {
        return number_format($this->point_cost) . ' points';
    }

    public function getTypeLabelAttribute()
    {
        return match($this->feature_type) {
            'post_enhancement' => 'Post Enhancement',
            'user_benefit' => 'User Benefit',
            'system_feature' => 'System Feature',
            default => 'Unknown'
        };
    }
} 