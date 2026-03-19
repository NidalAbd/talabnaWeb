<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\Categories;
use App\Models\Language;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentTranslationController extends Controller
{
    protected array $modelMap = [
        'categories' => ['class' => Categories::class, 'fields' => ['name'], 'label_field' => 'name'],
        'sub_categories' => ['class' => Sub_categories::class, 'fields' => ['name'], 'label_field' => 'name'],
        'badge_types' => ['class' => BadgeType::class, 'fields' => ['name'], 'label_field' => 'name'],
        'service_posts' => ['class' => ServicePost::class, 'fields' => ['title', 'description'], 'label_field' => 'title'],
    ];

    /**
     * Get content translations for a specific language and model.
     * GET /api/admin/languages/{code}/content-translations?model=categories&page=1&per_page=20&filter=all&search=
     */
    public function index(Request $request, string $code): JsonResponse
    {
        $modelKey = $request->query('model', 'ui_strings');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 20);
        $filter = $request->query('filter', 'all'); // all, missing, done
        $search = $request->query('search', '');

        $defaultLang = Language::getDefault();
        $defaultLocale = $defaultLang?->code ?? 'ar';

        if ($modelKey === 'ui_strings') {
            return $this->getUiStringsTranslations($code, $defaultLocale, $page, $perPage, $filter, $search);
        }

        $config = $this->modelMap[$modelKey] ?? null;
        if (!$config) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        return $this->getModelTranslations($code, $defaultLocale, $modelKey, $config, $page, $perPage, $filter, $search);
    }

    /**
     * Save content translations.
     * POST /api/admin/languages/{code}/content-translations
     * Body: { model: "categories", translations: [{ id: 1, field: "name", value: "..." }] }
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $modelKey = $request->input('model');
        $translations = $request->input('translations', []);

        if (empty($translations)) {
            return response()->json(['success' => true, 'message' => 'Nothing to save']);
        }

        if ($modelKey === 'ui_strings') {
            return $this->saveUiStrings($code, $translations);
        }

        $config = $this->modelMap[$modelKey] ?? null;
        if (!$config) {
            return response()->json(['error' => 'Invalid model'], 400);
        }

        return $this->saveModelTranslations($code, $modelKey, $config, $translations);
    }

    /**
     * UI Strings: key-value pairs from translations table.
     */
    private function getUiStringsTranslations(string $targetLocale, string $defaultLocale, int $page, int $perPage, string $filter, string $search): JsonResponse
    {
        // Get all default locale translations
        $query = Translation::where('locale', $defaultLocale);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('value', 'like', "%{$search}%");
            });
        }

        $defaultItems = $query->orderBy('group')->orderBy('key')->get();

        // Get target translations
        $targetMap = Translation::where('locale', $targetLocale)
            ->select('group', 'key', 'value')
            ->get()
            ->keyBy(fn($t) => "{$t->group}.{$t->key}");

        // Build items
        $items = [];
        foreach ($defaultItems as $item) {
            $compositeKey = "{$item->group}.{$item->key}";
            $targetValue = $targetMap->get($compositeKey)?->value ?? '';
            $isTranslated = !empty(trim($targetValue));

            // Apply filter
            if ($filter === 'missing' && $isTranslated) continue;
            if ($filter === 'done' && !$isTranslated) continue;

            $items[] = [
                'id' => $item->id,
                'label' => "{$item->group}.{$item->key}",
                'fields' => [
                    [
                        'field' => 'value',
                        'field_label' => strtoupper($item->group) . '.' . $item->key,
                        'source' => $item->value,
                        'translation' => $targetValue,
                        'is_translated' => $isTranslated,
                    ]
                ],
            ];
        }

        // Paginate manually
        $total = count($items);
        $lastPage = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);

        return response()->json([
            'data' => array_values($pageItems),
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Model translations: JSON column fields.
     */
    private function getModelTranslations(string $targetLocale, string $defaultLocale, string $modelKey, array $config, int $page, int $perPage, string $filter, string $search): JsonResponse
    {
        $modelClass = $config['class'];
        $fields = $config['fields'];
        $labelField = $config['label_field'];

        $query = $modelClass::query();

        if ($search) {
            $query->where(function ($q) use ($search, $fields, $defaultLocale) {
                foreach ($fields as $field) {
                    $q->orWhereRaw("JSON_EXTRACT(`{$field}`, '$.{$defaultLocale}') LIKE ?", ["%{$search}%"]);
                }
            });
        }

        // Get all items first for filtering
        $allItems = $query->orderBy('id', 'desc')->get();

        $items = [];
        foreach ($allItems as $item) {
            $itemFields = [];
            $allTranslated = true;
            $anyTranslated = false;

            foreach ($fields as $field) {
                $value = $item->getAttribute($field);
                $sourceText = is_array($value) ? ($value[$defaultLocale] ?? array_values(array_filter($value))[0] ?? '') : ($value ?? '');
                $translationText = is_array($value) ? ($value[$targetLocale] ?? '') : '';
                $isTranslated = !empty(trim($translationText));

                if (!$isTranslated) $allTranslated = false;
                if ($isTranslated) $anyTranslated = true;

                $itemFields[] = [
                    'field' => $field,
                    'field_label' => strtoupper($field),
                    'source' => $sourceText,
                    'translation' => $translationText,
                    'is_translated' => $isTranslated,
                ];
            }

            // Apply filter
            if ($filter === 'missing' && $allTranslated) continue;
            if ($filter === 'done' && !$allTranslated) continue;

            // Get label
            $labelValue = $item->getAttribute($labelField);
            $label = is_array($labelValue) ? ($labelValue[$defaultLocale] ?? array_values(array_filter($labelValue))[0] ?? "#{$item->id}") : ($labelValue ?? "#{$item->id}");

            $items[] = [
                'id' => $item->id,
                'label' => $label,
                'fields' => $itemFields,
            ];
        }

        // Paginate
        $total = count($items);
        $lastPage = max(1, ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);

        return response()->json([
            'data' => array_values($pageItems),
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Save UI string translations.
     */
    private function saveUiStrings(string $locale, array $translations): JsonResponse
    {
        $defaultLocale = Language::getDefault()?->code ?? 'ar';
        $saved = 0;

        foreach ($translations as $item) {
            // id is the default locale translation ID, we need group+key
            $source = Translation::find($item['id']);
            if (!$source) continue;

            Translation::setTranslation($locale, $source->group, $source->key, $item['value']);
            $saved++;
        }

        Translation::clearCache($locale);

        return response()->json([
            'success' => true,
            'message' => "{$saved} translations saved",
            'saved' => $saved,
        ]);
    }

    /**
     * Save model translations (JSON column updates).
     */
    private function saveModelTranslations(string $locale, string $modelKey, array $config, array $translations): JsonResponse
    {
        $modelClass = $config['class'];
        $table = (new $modelClass)->getTable();
        $saved = 0;

        DB::transaction(function () use ($table, $locale, $translations, &$saved) {
            foreach ($translations as $item) {
                $id = $item['id'];
                $field = $item['field'];
                $value = $item['value'];

                // Atomic JSON update
                $row = DB::selectOne("SELECT `{$field}` FROM `{$table}` WHERE `id` = ? FOR UPDATE", [$id]);
                if (!$row) continue;

                $current = json_decode($row->{$field} ?? '{}', true) ?: [];
                $current[$locale] = $value;

                DB::update("UPDATE `{$table}` SET `{$field}` = ? WHERE `id` = ?", [
                    json_encode($current, JSON_UNESCAPED_UNICODE),
                    $id,
                ]);
                $saved++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$saved} translations saved",
            'saved' => $saved,
        ]);
    }
}
