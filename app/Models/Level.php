<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'points_per_day',
        'view_boost_percentage',
        'display_order',
        'is_active',
        'is_premium',
        'features'
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean'
    ];

    /**
     * Get the service posts for this level.
     */
    public function servicePosts(): HasMany
    {
        return $this->hasMany(ServicePost::class);
    }

    /**
     * Get the localized name for the current locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->name[$locale] ?? $this->name['ar'] ?? '';
    }

    /**
     * Get the localized description for the current locale.
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->description[$locale] ?? $this->description['ar'] ?? '';
    }

    /**
     * Get the localized features for the current locale.
     */
    public function getLocalizedFeaturesAttribute(): array
    {
        $locale = app()->getLocale();
        return $this->features[$locale] ?? $this->features['ar'] ?? [];
    }

    /**
     * Scope to get only active levels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only premium levels.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get the default level (first ordered level).
     */
    public static function getDefault(): ?self
    {
        return static::active()->ordered()->first();
    }

    /**
     * Calculate points cost for a given duration.
     */
    public function calculatePointsCost(int $duration): int
    {
        return $this->points_per_day * $duration;
    }

    /**
     * Check if this level is the default/regular level.
     */
    public function isDefault(): bool
    {
        return $this->points_per_day === 0;
    }
} 