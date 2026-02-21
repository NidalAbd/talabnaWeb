<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TranslationManagementController extends Controller
{
    /**
     * List translations (paginated with filters)
     *
     * GET /api/admin/translations
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 50);
        $locale = $request->query('locale');
        $group = $request->query('group');
        $search = $request->query('search');

        $query = Translation::query()
            ->orderBy('group')
            ->orderBy('key');

        if ($locale) {
            $query->forLocale($locale);
        }

        if ($group) {
            $query->forGroup($group);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $translations = $query->paginate($perPage);

        return response()->json($translations);
    }

    /**
     * Get translation statistics
     *
     * GET /api/admin/translations/stats
     */
    public function stats(): JsonResponse
    {
        $totalTranslations = Translation::count();
        $uniqueKeys = Translation::select('group', 'key')
            ->distinct()
            ->count();
        $languagesCount = Language::count();
        $groupsCount = Translation::distinct()->count('group');

        return response()->json([
            'success' => true,
            'total_translations' => $totalTranslations,
            'unique_keys' => $uniqueKeys,
            'languages_count' => $languagesCount,
            'groups_count' => $groupsCount,
        ]);
    }

    /**
     * Create a new translation
     *
     * POST /api/admin/translations
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'required|string|exists:languages,code',
            'group' => 'required|string|max:50',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Check if translation already exists
        $existing = Translation::where('locale', $data['locale'])
            ->where('group', $data['group'])
            ->where('key', $data['key'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Translation already exists. Use PUT to update.',
            ], 409);
        }

        $translation = Translation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Translation created successfully',
            'data' => $translation,
        ], 201);
    }

    /**
     * Update a translation
     *
     * PUT /api/admin/translations/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json([
                'success' => false,
                'message' => 'Translation not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $translation->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'message' => 'Translation updated successfully',
            'data' => $translation->fresh(),
        ]);
    }

    /**
     * Delete a translation
     *
     * DELETE /api/admin/translations/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $translation = Translation::find($id);

        if (!$translation) {
            return response()->json([
                'success' => false,
                'message' => 'Translation not found',
            ], 404);
        }

        $translation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Translation deleted successfully',
        ]);
    }

    /**
     * Get all translation groups
     *
     * GET /api/admin/translations/groups
     */
    public function groups(): JsonResponse
    {
        $groups = Translation::getGroups();

        return response()->json([
            'success' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Export translations as JSON
     *
     * GET /api/admin/translations/export
     */
    public function export(Request $request): JsonResponse
    {
        $locale = $request->query('locale');
        $group = $request->query('group');

        $query = Translation::query()->orderBy('group')->orderBy('key');

        if ($locale) {
            $query->forLocale($locale);
        }

        if ($group) {
            $query->forGroup($group);
        }

        $translations = $query->get();

        // Structure the export
        $export = [];
        foreach ($translations as $translation) {
            if (!isset($export[$translation->locale])) {
                $export[$translation->locale] = [];
            }
            $export[$translation->locale]["{$translation->group}.{$translation->key}"] = $translation->value;
        }

        return response()->json([
            'success' => true,
            'export' => $export,
        ]);
    }

    /**
     * Import translations from JSON
     *
     * POST /api/admin/translations/import
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $imported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($request->translations as $locale => $items) {
                // Check if language exists
                $language = Language::where('code', $locale)->first();
                if (!$language) {
                    $errors[] = "Language '{$locale}' does not exist. Skipping.";
                    continue;
                }

                foreach ($items as $fullKey => $value) {
                    // Parse group.key format
                    $parts = explode('.', $fullKey, 2);
                    $group = count($parts) > 1 ? $parts[0] : 'app';
                    $key = count($parts) > 1 ? $parts[1] : $parts[0];

                    Translation::updateOrCreate(
                        [
                            'locale' => $locale,
                            'group' => $group,
                            'key' => $key,
                        ],
                        [
                            'value' => $value,
                        ]
                    );

                    $imported++;
                }
            }

            DB::commit();

            // Clear caches
            Translation::clearCache();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} translations",
                'imported' => $imported,
                'warnings' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk create/update translations for a single locale
     *
     * POST /api/admin/translations/bulk
     */
    public function bulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'required|string|exists:languages,code',
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $count = Translation::setTranslations(
            $request->locale,
            $request->translations
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$count} translations",
            'count' => $count,
        ]);
    }

    /**
     * Get missing translations (keys that exist in default locale but not in target)
     *
     * GET /api/admin/translations/missing/{locale}
     */
    public function missing(string $locale): JsonResponse
    {
        $defaultLanguage = Language::getDefault();

        if (!$defaultLanguage) {
            return response()->json([
                'success' => false,
                'message' => 'No default language configured',
            ], 400);
        }

        if ($defaultLanguage->code === $locale) {
            return response()->json([
                'success' => true,
                'missing' => [],
                'message' => 'Cannot check missing translations for default language',
            ]);
        }

        // Get all keys from default locale
        $defaultKeys = Translation::forLocale($defaultLanguage->code)
            ->select(DB::raw("CONCAT(`group`, '.', `key`) as full_key"))
            ->pluck('full_key')
            ->toArray();

        // Get all keys from target locale
        $targetKeys = Translation::forLocale($locale)
            ->select(DB::raw("CONCAT(`group`, '.', `key`) as full_key"))
            ->pluck('full_key')
            ->toArray();

        // Find missing keys
        $missing = array_diff($defaultKeys, $targetKeys);

        // Get the actual translations for missing keys
        $missingTranslations = [];
        foreach ($missing as $fullKey) {
            $parts = explode('.', $fullKey, 2);
            $group = $parts[0];
            $key = $parts[1] ?? '';

            $defaultTranslation = Translation::forLocale($defaultLanguage->code)
                ->forGroup($group)
                ->where('key', $key)
                ->first();

            if ($defaultTranslation) {
                $missingTranslations[] = [
                    'full_key' => $fullKey,
                    'group' => $group,
                    'key' => $key,
                    'default_value' => $defaultTranslation->value,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'default_locale' => $defaultLanguage->code,
            'missing_count' => count($missingTranslations),
            'missing' => $missingTranslations,
        ]);
    }
}
