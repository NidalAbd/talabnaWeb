<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageApiController extends Controller
{
    /**
     * List all active languages
     *
     * GET /api/languages
     */
    public function index(): JsonResponse
    {
        $languages = Language::getActiveOrdered();
        $defaultLanguage = Language::getDefault();

        return response()->json([
            'success' => true,
            'default_locale' => $defaultLanguage?->code ?? 'ar',
            'languages' => $languages->map(function ($language) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'is_default' => $language->is_default,
                    'is_active' => $language->is_active,
                ];
            }),
        ]);
    }

    /**
     * Get language configuration with version hash
     *
     * GET /api/languages/config
     */
    public function config(): JsonResponse
    {
        $languages = Language::getActiveOrdered();
        $defaultLanguage = Language::getDefault();

        // Generate a global version hash based on all translations
        $version = Cache::remember('translations_version', 3600, function () use ($languages) {
            $hashData = '';
            foreach ($languages as $language) {
                $hashData .= $language->getTranslationsVersionHash();
            }
            return md5($hashData);
        });

        return response()->json([
            'success' => true,
            'version' => $version,
            'default_locale' => $defaultLanguage?->code ?? 'ar',
            'languages' => $languages->map(function ($language) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'native_name' => $language->native_name,
                    'direction' => $language->direction,
                    'is_default' => $language->is_default,
                    'is_active' => $language->is_active,
                    'version' => $language->getTranslationsVersionHash(),
                ];
            }),
        ]);
    }

    /**
     * Check language status (for mobile app to verify if language is still active)
     *
     * GET /api/languages/status/{locale}
     */
    public function status(string $locale): JsonResponse
    {
        $language = Language::getByCode($locale);
        $defaultLanguage = Language::getDefault();

        if (!$language) {
            return response()->json([
                'success' => true,
                'exists' => false,
                'is_active' => false,
                'should_fallback' => true,
                'fallback_locale' => $defaultLanguage?->code ?? 'ar',
            ]);
        }

        $shouldFallback = !$language->is_active;

        return response()->json([
            'success' => true,
            'exists' => true,
            'is_active' => $language->is_active,
            'should_fallback' => $shouldFallback,
            'fallback_locale' => $shouldFallback ? ($defaultLanguage?->code ?? 'ar') : null,
        ]);
    }

    /**
     * Get all translations for a locale
     *
     * GET /api/translations/{locale}
     */
    public function translations(string $locale): JsonResponse
    {
        // Check if locale exists and is active
        $language = Language::getByCode($locale);

        if (!$language || !$language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found or inactive',
            ], 404);
        }

        $translations = Translation::getTranslationsForLocale($locale);
        $version = $language->getTranslationsVersionHash();

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'version' => $version,
            'direction' => $language->direction,
            'translations' => $translations,
        ]);
    }

    /**
     * Get translations by group for a locale
     *
     * GET /api/translations/{locale}/{group}
     */
    public function translationsByGroup(string $locale, string $group): JsonResponse
    {
        // Check if locale exists and is active
        $language = Language::getByCode($locale);

        if (!$language || !$language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found or inactive',
            ], 404);
        }

        $translations = Translation::getTranslationsForLocaleAndGroup($locale, $group);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'group' => $group,
            'translations' => $translations,
        ]);
    }

    /**
     * Check for translation updates (used by mobile app)
     *
     * GET /api/translations/{locale}/check?version={version}
     */
    public function checkUpdates(Request $request, string $locale): JsonResponse
    {
        $clientVersion = $request->query('version', '');

        $language = Language::getByCode($locale);

        if (!$language || !$language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found or inactive',
            ], 404);
        }

        $serverVersion = $language->getTranslationsVersionHash();
        $hasUpdates = $clientVersion !== $serverVersion;

        return response()->json([
            'success' => true,
            'has_updates' => $hasUpdates,
            'server_version' => $serverVersion,
            'client_version' => $clientVersion,
        ]);
    }
}
