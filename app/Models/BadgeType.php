<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class BadgeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'secondary_color',
        'icon',
        'points_per_day',
        'priority',
        'view_boost_percent',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'points_per_day' => 'integer',
        'priority' => 'integer',
        'view_boost_percent' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['name_ar', 'name_en'];

    /**
     * Get Arabic name
     */
    public function getNameArAttribute(): string
    {
        return $this->name['ar'] ?? $this->name['en'] ?? '';
    }

    /**
     * Get English name
     */
    public function getNameEnAttribute(): string
    {
        return $this->name['en'] ?? $this->name['ar'] ?? '';
    }

    /**
     * Get localized name based on locale
     */
    public function getLocalizedName(string $locale = 'ar'): string
    {
        return $locale === 'ar' ? $this->name_ar : $this->name_en;
    }

    /**
     * Relationship with service posts
     */
    public function servicePosts(): HasMany
    {
        return $this->hasMany(ServicePost::class, 'badge_type_id');
    }

    /**
     * Scope for active badges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Get the default badge type
     */
    public static function getDefault(): ?self
    {
        return Cache::remember('badge_type_default', 3600, function () {
            return self::where('is_default', true)->first();
        });
    }

    /**
     * Get all active badge types ordered by priority
     */
    public static function getActiveOrdered()
    {
        return Cache::remember('badge_types_active', 3600, function () {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Get badge type by slug
     */
    public static function getBySlug(string $slug): ?self
    {
        return Cache::remember("badge_type_{$slug}", 3600, function () use ($slug) {
            return self::where('slug', $slug)->first();
        });
    }

    /**
     * Get badge type by old have_badge value (for migration)
     */
    public static function getByOldBadgeName(string $oldName): ?self
    {
        $mapping = [
            'ماسي' => 'diamond',
            'ذهبي' => 'gold',
            'عادي' => 'normal',
        ];

        $slug = $mapping[$oldName] ?? 'normal';
        return self::getBySlug($slug);
    }

    /**
     * Calculate total cost for a duration
     */
    public function calculateCost(int $days): int
    {
        return $this->points_per_day * $days;
    }

    /**
     * Get SQL ORDER BY clause for sorting posts by badge priority
     */
    public static function getOrderByClause(): string
    {
        $badges = self::getActiveOrdered();

        if ($badges->isEmpty()) {
            return 'badge_type_id ASC';
        }

        $ids = $badges->pluck('id')->toArray();
        $idsString = implode(',', $ids);

        return "FIELD(badge_type_id, {$idsString})";
    }

    /**
     * Get SQL ORDER BY clause for legacy have_badge field sorting by priority
     * For backward compatibility with old queries
     */
    public static function getLegacyOrderByClause(): string
    {
        $badges = self::getActiveOrdered();

        if ($badges->isEmpty()) {
            return 'have_badge ASC';
        }

        // Map slugs to legacy Arabic names
        $legacyNames = [];
        foreach ($badges as $badge) {
            $legacyName = match($badge->slug) {
                'diamond' => 'ماسي',
                'gold' => 'ذهبي',
                'silver' => 'فضي',
                'normal' => 'عادي',
                'palestine' => 'فلسطين',
                default => $badge->slug,
            };
            $legacyNames[] = "'{$legacyName}'";
        }

        $namesString = implode(', ', $legacyNames);
        return "FIELD(have_badge, {$namesString})";
    }

    /**
     * Clear all badge type caches
     */
    public static function clearCache(): void
    {
        Cache::forget('badge_type_default');
        Cache::forget('badge_types_active');

        // Clear individual slug caches
        self::all()->each(function ($badge) {
            Cache::forget("badge_type_{$badge->slug}");
        });
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
