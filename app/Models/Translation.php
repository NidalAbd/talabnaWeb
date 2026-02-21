<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * Relationship with language
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }

    /**
     * Scope for specific locale
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope for specific group
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get full translation key (group.key)
     */
    public function getFullKeyAttribute(): string
    {
        return "{$this->group}.{$this->key}";
    }

    /**
     * Get all translations for a locale as flat array
     */
    public static function getTranslationsForLocale(string $locale): array
    {
        return Cache::remember("translations_{$locale}", 3600, function () use ($locale) {
            $translations = self::forLocale($locale)->get();

            $result = [];
            foreach ($translations as $translation) {
                $result[$translation->full_key] = $translation->value;
            }

            return $result;
        });
    }

    /**
     * Get all translations for a locale and group
     */
    public static function getTranslationsForLocaleAndGroup(string $locale, string $group): array
    {
        return Cache::remember("translations_{$locale}_{$group}", 3600, function () use ($locale, $group) {
            $translations = self::forLocale($locale)->forGroup($group)->get();

            $result = [];
            foreach ($translations as $translation) {
                $result[$translation->key] = $translation->value;
            }

            return $result;
        });
    }

    /**
     * Get all unique groups
     */
    public static function getGroups(): array
    {
        return Cache::remember('translation_groups', 3600, function () {
            return self::distinct()->pluck('group')->toArray();
        });
    }

    /**
     * Create or update a translation
     */
    public static function setTranslation(string $locale, string $group, string $key, string $value): self
    {
        $translation = self::updateOrCreate(
            [
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );

        self::clearCache($locale, $group);

        return $translation;
    }

    /**
     * Bulk create/update translations
     */
    public static function setTranslations(string $locale, array $translations): int
    {
        $count = 0;

        foreach ($translations as $fullKey => $value) {
            // Split key into group and key parts
            $parts = explode('.', $fullKey, 2);
            $group = count($parts) > 1 ? $parts[0] : 'app';
            $key = count($parts) > 1 ? $parts[1] : $parts[0];

            self::updateOrCreate(
                [
                    'locale' => $locale,
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );

            $count++;
        }

        self::clearCache($locale);

        return $count;
    }

    /**
     * Clear translation caches
     */
    public static function clearCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale && $group) {
            Cache::forget("translations_{$locale}_{$group}");
        }

        if ($locale) {
            Cache::forget("translations_{$locale}");
        }

        Cache::forget('translation_groups');
        Cache::forget('translations_version');
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($translation) {
            self::clearCache($translation->locale, $translation->group);
        });

        static::deleted(function ($translation) {
            self::clearCache($translation->locale, $translation->group);
        });
    }
}
