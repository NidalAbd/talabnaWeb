<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'points',
        'price',
        'description',
        'is_active',
        'features'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'features' => 'array'
    ];

    public function features()
    {
        return $this->belongsToMany(PremiumFeature::class, 'package_features');
    }

    public function sales()
    {
        return $this->hasMany(point_transactions::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedPointsAttribute()
    {
        return number_format($this->points) . ' points';
    }
} 