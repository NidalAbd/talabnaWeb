<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SubscriptionPlan extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'price_points',
        'duration_days',
        'features',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'is_popular',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'price_points' => 'integer',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_points');
    }

    /**
     * Get a specific feature value.
     */
    public function getFeature(string $key, $default = null)
    {
        return $this->features[$key] ?? $default;
    }

    /**
     * Get all active plans (cached).
     */
    public static function getActivePlans()
    {
        return Cache::remember('subscription_plans_active', 3600, function () {
            return self::active()->ordered()->get();
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('subscription_plans_active');
        });

        static::deleted(function () {
            Cache::forget('subscription_plans_active');
        });
    }
}
