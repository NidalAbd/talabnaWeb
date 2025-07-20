<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points_amount',
        'price',
        'currency_code',
        'currency_name',
        'is_active',
        'is_popular',
        'display_order',
        'features',
        'validity_days',
        'discount_percentage',
        'icon',
        'color',
        'max_purchases'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
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
        return number_format($this->points_amount) . ' points';
    }
} 