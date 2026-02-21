<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LanguageManagementController extends Controller
{
    /**
     * List all languages (paginated)
     *
     * GET /api/admin/languages
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $direction = $request->query('direction', '');

        $query = Language::query()
            ->withCount('translations')
            ->ordered();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('native_name', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($direction === 'ltr' || $direction === 'rtl') {
            $query->where('direction', $direction);
        }

        $languages = $query->paginate($perPage);

        return response()->json($languages);
    }

    /**
     * Create a new language
     *
     * POST /api/admin/languages
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:100',
            'native_name' => 'required|string|max:100',
            'direction' => 'required|in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // New languages must start as inactive - they need translations first
        $data['is_active'] = false;
        $data['is_default'] = false;

        $language = Language::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Language created successfully',
            'data' => $language,
        ], 201);
    }

    /**
     * Show a specific language
     *
     * GET /api/admin/languages/{id}
     */
    public function show(int $id): JsonResponse
    {
        $language = Language::with(['translations' => function ($query) {
            $query->orderBy('group')->orderBy('key');
        }])->find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $language,
        ]);
    }

    /**
     * Update a language
     *
     * PUT /api/admin/languages/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $language = Language::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'string|max:10|unique:languages,code,' . $id,
            'name' => 'string|max:100',
            'native_name' => 'string|max:100',
            'direction' => 'in:ltr,rtl',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Prevent activating via update - must use toggle endpoint
        if (isset($data['is_active']) && $data['is_active'] && !$language->is_active) {
            $completeness = $this->checkTranslationCompleteness($language);
            if (!$completeness['complete']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate language with incomplete translations. Use the toggle endpoint after completing all translations.',
                    'completeness' => $completeness,
                ], 400);
            }
        }

        // If this is set as default, unset other defaults
        if (!empty($data['is_default'])) {
            Language::where('is_default', true)
                   ->where('id', '!=', $id)
                   ->update(['is_default' => false]);
        }

        $language->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Language updated successfully',
            'data' => $language->fresh(),
        ]);
    }

    /**
     * Delete a language
     *
     * DELETE /api/admin/languages/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $language = Language::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found',
            ], 404);
        }

        // Don't allow deleting the default language
        if ($language->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default language. Set another language as default first.',
            ], 400);
        }

        // Delete associated translations
        $language->translations()->delete();
        $language->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully',
        ]);
    }

    /**
     * Check if a language has all translation keys completed.
     * Compares against the default language's keys.
     *
     * Returns ['complete' => bool, 'missing' => int, 'empty' => int, 'total' => int]
     */
    private function checkTranslationCompleteness(Language $language): array
    {
        $defaultLanguage = Language::getDefault();
        $defaultLocale = $defaultLanguage ? $defaultLanguage->code : 'ar';

        // Get all group.key pairs from the default locale
        $defaultKeys = Translation::forLocale($defaultLocale)
            ->select('group', 'key')
            ->get()
            ->map(fn ($t) => $t->group . '.' . $t->key)
            ->toArray();

        $totalRequired = count($defaultKeys);

        if ($totalRequired === 0) {
            return ['complete' => true, 'missing' => 0, 'empty' => 0, 'total' => 0];
        }

        // Get all translations for this language
        $targetTranslations = Translation::forLocale($language->code)
            ->select('group', 'key', 'value')
            ->get();

        $targetMap = [];
        foreach ($targetTranslations as $t) {
            $targetMap[$t->group . '.' . $t->key] = $t->value;
        }

        $missing = 0;
        $empty = 0;
        foreach ($defaultKeys as $fullKey) {
            if (!isset($targetMap[$fullKey])) {
                $missing++;
            } elseif (trim($targetMap[$fullKey]) === '') {
                $empty++;
            }
        }

        return [
            'complete' => ($missing === 0 && $empty === 0),
            'missing' => $missing,
            'empty' => $empty,
            'total' => $totalRequired,
        ];
    }

    /**
     * Toggle language active status
     *
     * POST /api/admin/languages/{id}/toggle
     */
    public function toggle(int $id): JsonResponse
    {
        $language = Language::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found',
            ], 404);
        }

        // Don't allow deactivating the default language
        if ($language->is_default && $language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate the default language',
            ], 400);
        }

        // When activating, check translation completeness
        if (!$language->is_active) {
            $completeness = $this->checkTranslationCompleteness($language);

            if (!$completeness['complete']) {
                $details = [];
                if ($completeness['missing'] > 0) {
                    $details[] = $completeness['missing'] . ' missing';
                }
                if ($completeness['empty'] > 0) {
                    $details[] = $completeness['empty'] . ' empty';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate language. All translation keys must be completed. '
                        . implode(' and ', $details) . ' out of ' . $completeness['total'] . ' keys.',
                    'completeness' => $completeness,
                ], 400);
            }
        }

        $language->update(['is_active' => !$language->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Language status toggled successfully',
            'data' => $language->fresh(),
        ]);
    }

    /**
     * Set a language as default
     *
     * POST /api/admin/languages/{id}/set-default
     */
    public function setDefault(int $id): JsonResponse
    {
        $language = Language::find($id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found',
            ], 404);
        }

        if (!$language->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set inactive language as default',
            ], 400);
        }

        // Check translation completeness before setting as default
        $completeness = $this->checkTranslationCompleteness($language);

        if (!$completeness['complete']) {
            $details = [];
            if ($completeness['missing'] > 0) {
                $details[] = $completeness['missing'] . ' missing';
            }
            if ($completeness['empty'] > 0) {
                $details[] = $completeness['empty'] . ' empty';
            }

            return response()->json([
                'success' => false,
                'message' => 'Cannot set as default. All translation keys must be completed. '
                    . implode(' and ', $details) . ' out of ' . $completeness['total'] . ' keys.',
                'completeness' => $completeness,
            ], 400);
        }

        $language->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Language set as default successfully',
            'data' => $language->fresh(),
        ]);
    }

    /**
     * Get language statistics
     *
     * GET /api/admin/languages/stats
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'total' => Language::count(),
            'active' => Language::where('is_active', true)->count(),
            'inactive' => Language::where('is_active', false)->count(),
            'rtl_count' => Language::where('direction', 'rtl')->count(),
            'ltr_count' => Language::where('direction', 'ltr')->count(),
        ]);
    }

    /**
     * Reorder languages
     *
     * POST /api/admin/languages/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*.id' => 'required|exists:languages,id',
            'order.*.sort_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->order as $item) {
            Language::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        Language::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Languages reordered successfully',
        ]);
    }
}
