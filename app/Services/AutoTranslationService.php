<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\BadgeType;
use App\Models\ServicePost;
use App\Models\SubscriptionPlan;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoTranslationService
{
    protected string $apiKey;
    protected string $model = 'gpt-4o-mini';
    protected string $defaultLocale;
    protected string $defaultLanguageName;

    protected int $uiBatchSize = 50;
    protected int $modelBatchSize = 20;
    protected int $concurrency = 5;

    protected array $modelMap = [
        'categories' => Categories::class,
        'sub_categories' => Sub_categories::class,
        'badge_types' => BadgeType::class,
        'subscription_plans' => SubscriptionPlan::class,
        'service_posts' => ServicePost::class,
    ];

    protected array $tier2Tables = ['categories', 'sub_categories', 'badge_types', 'subscription_plans'];
    protected array $tier3Tables = ['service_posts'];

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');

        // Detect source/default language
        $defaultLang = \App\Models\Language::getDefault();
        $this->defaultLocale = $defaultLang?->code ?? 'ar';
        $this->defaultLanguageName = $defaultLang?->name ?? 'Arabic';
    }

    /**
     * Atomically save translation fields for a JSON-column model.
     * For models using HasTranslations trait with JSON columns.
     */
    protected function saveTranslationsAtomic($item, string $locale, array $fieldValues): void
    {
        $table = $item->getTable();
        $fieldValues = array_filter($fieldValues);
        if (empty($fieldValues)) return;

        DB::transaction(function () use ($table, $item, $locale, $fieldValues) {
            foreach ($fieldValues as $field => $value) {
                // Read current JSON, merge new locale, write back
                $row = DB::selectOne("SELECT `{$field}` FROM `{$table}` WHERE `id` = ? FOR UPDATE", [$item->id]);
                $current = json_decode($row->{$field} ?? '{}', true) ?: [];
                $current[$locale] = $value;

                DB::update("UPDATE `{$table}` SET `{$field}` = ? WHERE `id` = ?", [
                    json_encode($current, JSON_UNESCAPED_UNICODE),
                    $item->id,
                ]);
            }
        });
    }

    /**
     * Translate Tier 1: UI strings from the translations table.
     */
    public function translateTier1(string $targetLocale, string $targetLanguageName): array
    {
        $progressKey = "auto_translate_progress_{$targetLocale}";
        $completed = 0;
        $errors = 0;

        // Get all UI translation keys for the default locale
        $defaultLocale = $this->defaultLocale;
        $defaultTranslations = Translation::where('locale', $defaultLocale)->get();
        $total = $defaultTranslations->count();

        // Check which already exist for target
        $existingTargetTranslations = Translation::where('locale', $targetLocale)
            ->select('group', 'key', 'value')
            ->get();
        $existingKeys = [];
        foreach ($existingTargetTranslations as $t) {
            $existingKeys["{$t->group}.{$t->key}"] = $t->value;
        }

        $toTranslate = [];
        foreach ($defaultTranslations as $trans) {
            $compositeKey = "{$trans->group}.{$trans->key}";
            if (!empty($existingKeys[$compositeKey])) {
                $completed++;
                continue;
            }
            $toTranslate[$compositeKey] = $trans->value;
        }

        if (empty($toTranslate)) {
            return ['success' => true, 'tier' => 1, 'total' => $total, 'completed' => $completed, 'errors' => 0];
        }

        Cache::put($progressKey, [
            'status' => 'running', 'tier' => 1, 'total' => $total,
            'completed' => $completed, 'percentage' => 0,
            'current_task' => 'Translating UI strings...', 'errors' => 0,
        ], 3600);

        try {
            $batches = array_chunk($toTranslate, $this->uiBatchSize, true);
            $concurrentGroups = array_chunk($batches, $this->concurrency);

            foreach ($concurrentGroups as $group) {
                $results = $this->sendConcurrentBatches($group, $targetLanguageName);

                foreach ($results as $translations) {
                    foreach ($translations as $compositeKey => $translatedText) {
                        if ($translatedText) {
                            [$grp, $key] = explode('.', $compositeKey, 2);
                            Translation::setTranslation($targetLocale, $grp, $key, $translatedText);
                            $completed++;
                        } else {
                            $errors++;
                            $completed++;
                        }
                    }
                }

                $this->updateProgress($progressKey, $completed, $total, 'Translating UI strings...', $errors, 1);
            }

            Cache::put($progressKey, [
                'status' => 'completed', 'tier' => 1, 'total' => $total,
                'completed' => $completed, 'percentage' => 100,
                'current_task' => 'Tier 1 completed!', 'errors' => $errors,
            ], 3600);

            Translation::clearCache($targetLocale);

            return ['success' => true, 'tier' => 1, 'total' => $total, 'completed' => $completed, 'errors' => $errors];
        } catch (\Exception $e) {
            Log::error("Tier 1 translation failed for {$targetLocale}: " . $e->getMessage());
            return ['success' => false, 'tier' => 1, 'error' => $e->getMessage()];
        }
    }

    /**
     * Translate Tier 2: Core content (categories, subcategories, badge types).
     */
    public function translateTier2(string $targetLocale, string $targetLanguageName): array
    {
        $progressKey = "auto_translate_progress_{$targetLocale}";
        $completed = 0;
        $errors = 0;
        $total = 0;

        // Count total items
        foreach ($this->tier2Tables as $table) {
            $modelClass = $this->modelMap[$table] ?? null;
            if (!$modelClass) continue;
            $fields = config("languages.required_fields.{$table}", []);
            $total += $modelClass::count() * count($fields);
        }

        Cache::put($progressKey, [
            'status' => 'running', 'tier' => 2, 'total' => $total,
            'completed' => 0, 'percentage' => 0,
            'current_task' => 'Translating core content...', 'errors' => 0,
        ], 3600);

        try {
            foreach ($this->tier2Tables as $table) {
                $result = $this->translateModelContent(
                    $table, $targetLocale, $targetLanguageName,
                    $progressKey, $completed, $total, $errors
                );
                $completed = $result['completed'];
                $errors = $result['errors'];
            }

            Cache::put($progressKey, [
                'status' => 'completed', 'tier' => 2, 'total' => $total,
                'completed' => $completed, 'percentage' => 100,
                'current_task' => 'Tier 2 completed!', 'errors' => $errors,
            ], 3600);

            return ['success' => true, 'tier' => 2, 'total' => $total, 'completed' => $completed, 'errors' => $errors];
        } catch (\Exception $e) {
            Log::error("Tier 2 translation failed: " . $e->getMessage());
            return ['success' => false, 'tier' => 2, 'error' => $e->getMessage()];
        }
    }

    /**
     * Translate Tier 3: Dynamic content (service posts).
     */
    public function translateTier3(string $targetLocale, string $targetLanguageName, ?int $limit = null): array
    {
        $progressKey = "auto_translate_progress_{$targetLocale}";
        $completed = 0;
        $errors = 0;

        Cache::put($progressKey, [
            'status' => 'running', 'tier' => 3, 'total' => 0,
            'completed' => 0, 'percentage' => 0,
            'current_task' => 'Translating service posts...', 'errors' => 0,
        ], 3600);

        try {
            foreach ($this->tier3Tables as $table) {
                $result = $this->translateModelContent(
                    $table, $targetLocale, $targetLanguageName,
                    $progressKey, $completed, $limit ?? 999999, $errors,
                    $limit
                );
                $completed = $result['completed'];
                $errors = $result['errors'];
            }

            Cache::put($progressKey, [
                'status' => 'completed', 'tier' => 3,
                'total' => $completed + $errors, 'completed' => $completed,
                'percentage' => 100, 'current_task' => 'Tier 3 batch completed!',
                'errors' => $errors,
            ], 3600);

            return ['success' => true, 'tier' => 3, 'translated' => $completed, 'errors' => $errors];
        } catch (\Exception $e) {
            Log::error("Tier 3 translation failed: " . $e->getMessage());
            return ['success' => false, 'tier' => 3, 'error' => $e->getMessage()];
        }
    }

    /**
     * Translate on-demand for a single service post.
     */
    public function translateOnDemand(int $postId, string $targetLocale, string $targetLanguageName): array
    {
        $post = ServicePost::find($postId);
        if (!$post) {
            return ['success' => false, 'error' => 'Post not found'];
        }

        $fields = config('languages.required_fields.service_posts', ['title', 'description']);
        $toTranslate = [];

        foreach ($fields as $field) {
            $value = $post->getAttribute($field);
            if (is_array($value) && !empty($value[$targetLocale])) continue;

            // Get source text (prefer Arabic, then first available)
            $sourceText = is_array($value)
                ? ($value['ar'] ?? array_values(array_filter($value))[0] ?? '')
                : ($value ?? '');

            if (!empty($sourceText)) {
                $toTranslate[$field] = $sourceText;
            }
        }

        if (empty($toTranslate)) {
            return ['success' => true, 'already_translated' => true];
        }

        $translated = $this->sendSingleBatch($toTranslate, $targetLanguageName);
        if (!$translated) {
            return ['success' => false, 'error' => 'Translation API failed'];
        }

        $this->saveTranslationsAtomic($post, $targetLocale, array_filter($translated));

        return ['success' => true, 'translations' => $translated];
    }

    /**
     * Translate model content using concurrent batches.
     */
    protected function translateModelContent(
        string $table, string $targetLocale, string $targetLanguageName,
        string $progressKey, int $completed, int $total, int &$errors,
        ?int $limit = null
    ): array {
        $modelClass = $this->modelMap[$table] ?? null;
        if (!$modelClass) return ['completed' => $completed, 'errors' => $errors];

        $fields = config("languages.required_fields.{$table}", []);
        if (empty($fields)) return ['completed' => $completed, 'errors' => $errors];

        $priorityConfig = config("languages.tier3_priority.{$table}", []);
        $orderBy = $priorityConfig['order_by'] ?? 'id';
        $direction = $priorityConfig['direction'] ?? 'desc';

        $items = $modelClass::orderBy($orderBy, $direction)->get();
        $toTranslate = [];

        foreach ($items as $item) {
            if ($limit !== null && count($toTranslate) >= $limit * count($fields)) break;

            foreach ($fields as $field) {
                $value = $item->getAttribute($field);

                // Skip if already translated
                if (is_array($value) && !empty($value[$targetLocale])) {
                    $completed++;
                    continue;
                }

                // Get source text
                $sourceText = is_array($value)
                    ? ($value[$this->defaultLocale] ?? array_values(array_filter($value))[0] ?? '')
                    : ($value ?? '');

                if (empty($sourceText)) {
                    $completed++;
                    continue;
                }

                $toTranslate["{$item->id}|{$field}"] = $sourceText;
            }
        }

        if (empty($toTranslate)) return ['completed' => $completed, 'errors' => $errors];

        $batches = array_chunk($toTranslate, $this->modelBatchSize, true);
        $concurrentGroups = array_chunk($batches, $this->concurrency);

        foreach ($concurrentGroups as $groupIndex => $group) {
            $this->updateProgress($progressKey, $completed, $total,
                "Translating {$table} (batch " . ($groupIndex + 1) . "/" . count($concurrentGroups) . ")...",
                $errors);

            $results = $this->sendConcurrentBatches($group, $targetLanguageName);

            $itemTranslations = [];
            foreach ($results as $translations) {
                foreach ($translations as $compositeKey => $translatedText) {
                    [$itemId, $fieldName] = explode('|', $compositeKey);
                    if ($translatedText) {
                        $itemTranslations[(int)$itemId][$fieldName] = $translatedText;
                    } else {
                        $errors++;
                    }
                    $completed++;
                }
            }

            foreach ($itemTranslations as $itemId => $fieldValues) {
                $item = $items->firstWhere('id', $itemId);
                if ($item) {
                    $this->saveTranslationsAtomic($item, $targetLocale, $fieldValues);
                }
            }
        }

        return ['completed' => $completed, 'errors' => $errors];
    }

    /**
     * Send concurrent translation batches via OpenAI.
     */
    protected function sendConcurrentBatches(array $batches, string $targetLanguage): array
    {
        if (empty($batches) || empty($this->apiKey)) return [];

        $results = [];
        $failedBatches = [];

        $responses = Http::pool(function ($pool) use ($batches, $targetLanguage) {
            foreach ($batches as $index => $batch) {
                $pool->as("batch_{$index}")
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->timeout(300)
                    ->connectTimeout(30)
                    ->post('https://api.openai.com/v1/chat/completions', $this->buildPayload($batch, $targetLanguage));
            }
        });

        foreach ($responses as $key => $response) {
            $parsed = $this->parseResponse($response, $key);
            if ($parsed !== null) {
                $results[] = $parsed;
            } else {
                $index = (int) str_replace('batch_', '', $key);
                if (isset($batches[$index])) {
                    $failedBatches[$index] = $batches[$index];
                }
            }
        }

        // Retry failed batches sequentially
        foreach ($failedBatches as $batch) {
            sleep(2);
            $retryResult = $this->sendSingleBatch($batch, $targetLanguage);
            if ($retryResult !== null) {
                $results[] = $retryResult;
            }
        }

        return $results;
    }

    protected function sendSingleBatch(array $batch, string $targetLanguage): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(300)
                ->connectTimeout(30)
                ->retry(2, 3000)
                ->post('https://api.openai.com/v1/chat/completions', $this->buildPayload($batch, $targetLanguage));

            return $this->parseResponse($response, 'retry');
        } catch (\Exception $e) {
            Log::error("Single batch translation failed: " . $e->getMessage());
            return null;
        }
    }

    protected function buildPayload(array $batch, string $targetLanguage, ?string $sourceLanguage = null): array
    {
        $sourceLang = $sourceLanguage ?? $this->defaultLanguageName;
        $jsonInput = json_encode($batch, JSON_UNESCAPED_UNICODE);

        $prompt = "Translate the following JSON object values from {$sourceLang} to {$targetLanguage}.\n"
            . "Keep the JSON keys exactly the same. Only translate the values.\n"
            . "Maintain the same tone, formatting, and any special characters.\n"
            . "For technical terms, keep them as-is or use the commonly accepted translation.\n"
            . "Return ONLY the JSON object with translated values, no markdown formatting.\n\n"
            . $jsonInput;

        return [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate text for a marketplace/classifieds app called 'Talabna' (a services and classifieds platform). The source language is {$sourceLang}. Return only valid JSON.",
                ],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 8192,
        ];
    }

    protected function parseResponse($response, string $label): ?array
    {
        try {
            if ($response instanceof \Exception) return null;
            if (!$response->successful()) {
                Log::error("Batch {$label} HTTP error {$response->status()}: " . $response->body());
                return null;
            }

            $content = $response->json('choices.0.message.content', '');
            $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
            $content = preg_replace('/\n?```\s*$/m', '', $content);
            $content = trim($content);

            $translated = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse batch {$label}: " . json_last_error_msg());
                return null;
            }

            return $translated;
        } catch (\Exception $e) {
            Log::error("Error processing batch {$label}: " . $e->getMessage());
            return null;
        }
    }

    protected function updateProgress(string $key, int $completed, int $total, string $task, int $errors, ?int $tier = null): void
    {
        $data = [
            'status' => 'running',
            'total' => $total,
            'completed' => $completed,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'current_task' => $task,
            'errors' => $errors,
        ];
        if ($tier !== null) $data['tier'] = $tier;
        Cache::put($key, $data, 3600);
    }

    public static function getProgress(string $targetLocale): ?array
    {
        return Cache::get("auto_translate_progress_{$targetLocale}");
    }
}
