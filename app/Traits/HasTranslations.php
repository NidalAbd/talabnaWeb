<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get the translatable fields for this model.
     * Override $translatable in each model.
     */
    public function getTranslatableFields(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Get a translated value for a field.
     * Fallback: requested locale -> default locale (ar) -> raw field value
     *
     * Storage format: {"ar": "...", "en": "...", "tr": "..."}
     * Each translatable field is a JSON column with locale keys.
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? App::getLocale();
        $value = $this->getAttribute($field);

        // If the value is already a decoded array/map (JSON cast)
        if (is_array($value)) {
            if (isset($value[$locale]) && !empty($value[$locale])) {
                return $value[$locale];
            }
            // Fallback to default locale
            $defaultLocale = config('app.fallback_locale', 'ar');
            if (isset($value[$defaultLocale]) && !empty($value[$defaultLocale])) {
                return $value[$defaultLocale];
            }
            // Fallback to first available
            return !empty($value) ? array_values(array_filter($value))[0] ?? null : null;
        }

        // If it's a plain string (legacy data), return as-is
        return $value;
    }

    /**
     * Get all translations for a specific locale.
     */
    public function getTranslationsForLocale(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();
        $result = [];

        foreach ($this->getTranslatableFields() as $field) {
            $result[$field] = $this->translate($field, $locale);
        }

        return $result;
    }

    /**
     * Set a single translation for a locale and field.
     */
    public function setTranslation(string $locale, string $field, ?string $value): void
    {
        $current = $this->getAttribute($field);
        if (!is_array($current)) {
            // Convert legacy string to array
            $defaultLocale = config('app.fallback_locale', 'ar');
            $current = $current ? [$defaultLocale => $current] : [];
        }
        $current[$locale] = $value;
        $this->setAttribute($field, $current);
    }

    /**
     * Set multiple translations for a locale.
     */
    public function setTranslations(string $locale, array $fieldValues): void
    {
        foreach ($fieldValues as $field => $value) {
            $this->setTranslation($locale, $field, $value);
        }
    }

    /**
     * Get all available locales for this model's translations.
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        foreach ($this->getTranslatableFields() as $field) {
            $value = $this->getAttribute($field);
            if (is_array($value)) {
                $locales = array_merge($locales, array_keys($value));
            }
        }
        return array_unique($locales);
    }

    /**
     * Check which locales have complete translations.
     */
    public function getCompletedLocales(): array
    {
        $fields = $this->getTranslatableFields();
        $allLocales = $this->getAvailableLocales();
        $completed = [];

        foreach ($allLocales as $locale) {
            $allFilled = true;
            foreach ($fields as $field) {
                $value = $this->getAttribute($field);
                if (!is_array($value) || empty($value[$locale] ?? null)) {
                    $allFilled = false;
                    break;
                }
            }
            if ($allFilled) {
                $completed[] = $locale;
            }
        }

        return $completed;
    }

    /**
     * Override toArray to resolve translatable fields to current locale.
     * Also includes a 'translations' key with all locale data for editing.
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $locale = App::getLocale();

        foreach ($this->getTranslatableFields() as $field) {
            $rawValue = $this->getAttribute($field);

            // Store all translations under a nested key for frontend editing
            if (is_array($rawValue)) {
                $array['translations'][$field] = $rawValue;
                // Resolve to current locale for display
                $array[$field] = $this->translate($field, $locale);
            }
        }

        return $array;
    }

    /**
     * Convert model to array with translations for a specific locale.
     */
    public function toLocalizedArray(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();
        $array = parent::toArray();

        foreach ($this->getTranslatableFields() as $field) {
            $array[$field] = $this->translate($field, $locale);
        }

        return $array;
    }

    /**
     * Scope to filter models that have a translation for a specific locale.
     * Usage: ServicePost::hasTranslation('title', 'en')->get()
     */
    public function scopeHasTranslation($query, string $field, string $locale)
    {
        return $query->whereNotNull($field)
            ->whereRaw("JSON_EXTRACT({$field}, '$.{$locale}') IS NOT NULL")
            ->whereRaw("JSON_EXTRACT({$field}, '$.{$locale}') != '\"\"'");
    }

    /**
     * Scope to order by a translated field for a locale.
     */
    public function scopeOrderByTranslation($query, string $field, string $locale, string $direction = 'asc')
    {
        return $query->orderByRaw("JSON_EXTRACT({$field}, '$.{$locale}') {$direction}");
    }
}
