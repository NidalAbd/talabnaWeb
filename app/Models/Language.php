<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationship with translations
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'locale', 'code');
    }

    /**
     * Scope for active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered languages
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get the default language
     */
    public static function getDefault(): ?self
    {
        return Cache::remember('language_default', 3600, function () {
            return self::where('is_default', true)->first();
        });
    }

    /**
     * Get all active languages ordered
     */
    public static function getActiveOrdered()
    {
        return Cache::remember('languages_active', 3600, function () {
            return self::active()->ordered()->get();
        });
    }

    /**
     * Get language by code
     */
    public static function getByCode(string $code): ?self
    {
        return Cache::remember("language_{$code}", 3600, function () use ($code) {
            return self::where('code', $code)->first();
        });
    }

    /**
     * Set this language as default (and unset others)
     */
    public function setAsDefault(): void
    {
        // Unset all other defaults
        self::where('is_default', true)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);

        // Clear cache
        self::clearCache();
    }

    /**
     * Generate version hash for translations
     */
    public function getTranslationsVersionHash(): string
    {
        $latestUpdate = $this->translations()->max('updated_at');
        $count = $this->translations()->count();

        return md5($this->code . $latestUpdate . $count);
    }

    /**
     * Clear all language caches
     */
    public static function clearCache(): void
    {
        Cache::forget('language_default');
        Cache::forget('languages_active');
        Cache::forget('translations_version');

        // Clear individual language caches
        self::all()->each(function ($language) {
            Cache::forget("language_{$language->code}");
            Cache::forget("translations_{$language->code}");
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
