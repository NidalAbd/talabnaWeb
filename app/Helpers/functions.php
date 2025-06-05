<?php
if (!function_exists('getTranslatedName')) {
    function getTranslatedName($name) {
        // If name is already a string, return it
        if (is_string($name)) {
            return $name;
        }

        // If name is an array, get the current locale or fall back to English
        $locale = app()->getLocale();

        if (is_array($name) && isset($name[$locale])) {
            return $name[$locale];
        }

        // Fallback to English or the first available translation
        return $name['en'] ?? (is_array($name) ? array_values($name)[0] : 'Unknown');
    }
}
