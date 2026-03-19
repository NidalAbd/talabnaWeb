<?php

namespace App\Services;

use App\Models\cities;
use App\Models\countries;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiLocationService
{
    protected string $apiKey;
    protected string $model = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
    }

    /**
     * Fetch countries from RestCountries API.
     * Free, no key needed. Returns parsed array.
     */
    public function fetchCountriesFromApi(?string $region = null): array
    {
        $url = $region
            ? "https://restcountries.com/v3.1/region/{$region}"
            : "https://restcountries.com/v3.1/all";

        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("RestCountries API failed: HTTP {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Import countries from RestCountries API data into DB.
     * Skips duplicates by iso_code. Returns stats.
     */
    public function importCountries(array $apiCountries, string $progressKey = null): array
    {
        $created = 0;
        $skipped = 0;
        $errors = 0;
        $total = count($apiCountries);

        $activeLocales = Language::getActiveOrdered()->pluck('code')->toArray();

        foreach ($apiCountries as $index => $apiCountry) {
            try {
                $isoCode = $apiCountry['cca2'] ?? null;
                if (!$isoCode) {
                    $skipped++;
                    continue;
                }

                // Skip if already exists
                if (countries::where('iso_code', $isoCode)->exists()) {
                    $skipped++;
                    if ($progressKey) {
                        $this->updateProgress($progressKey, $index + 1, $total, "Skipped {$apiCountry['name']['common']} (exists)", $created, $skipped);
                    }
                    continue;
                }

                // Build name JSON
                $englishName = $apiCountry['name']['common'] ?? 'Unknown';
                $nameMap = ['en' => $englishName];

                // Try to get Arabic name from nativeName
                if (isset($apiCountry['name']['nativeName']['ara']['common'])) {
                    $nameMap['ar'] = $apiCountry['name']['nativeName']['ara']['common'];
                }

                // Get currency info
                $currencyCode = '';
                $currencyName = ['en' => ''];
                $currencySymbol = '';
                if (!empty($apiCountry['currencies'])) {
                    $firstCurrency = array_key_first($apiCountry['currencies']);
                    $currencyCode = $firstCurrency;
                    $currencyName = ['en' => $apiCountry['currencies'][$firstCurrency]['name'] ?? ''];
                    $currencySymbol = $apiCountry['currencies'][$firstCurrency]['symbol'] ?? '';
                }

                // Flag URL
                $flagUrl = $apiCountry['flags']['png'] ?? $apiCountry['flags']['svg'] ?? null;

                // Create country
                $country = countries::create([
                    'name' => $nameMap,
                    'country_code' => $isoCode,
                    'iso_code' => $isoCode,
                    'currency_code' => $currencyCode,
                    'currency_name' => $currencyName,
                    'currency_symbol' => $currencySymbol,
                    'flag' => $flagUrl,
                    'price_per_point' => 7.50,
                    'exchange_rate_to_usd' => 1.0,
                    'use_custom_price' => false,
                    'allow_point_transfers' => true,
                ]);

                $created++;

                if ($progressKey) {
                    $this->updateProgress($progressKey, $index + 1, $total, "Imported {$englishName}", $created, $skipped);
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Failed to import country: " . ($apiCountry['name']['common'] ?? 'unknown') . " - " . $e->getMessage());
            }
        }

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors, 'total' => $total];
    }

    /**
     * Generate ALL cities for a country using OpenAI.
     * Makes multiple API calls if needed to get comprehensive coverage.
     */
    public function generateCitiesForCountry(countries $country, int $count = 0): array
    {
        $countryName = $country->name['en'] ?? 'Unknown';
        $activeLocales = Language::getActiveOrdered()->pluck('code')->toArray();
        if (!in_array('en', $activeLocales)) array_unshift($activeLocales, 'en');
        if (!in_array('ar', $activeLocales)) $activeLocales[] = 'ar';

        $localeList = implode(', ', $activeLocales);
        $totalCreated = 0;
        $totalSkipped = 0;

        // Get existing city names to exclude
        $existingNames = cities::where('country_id', $country->id)
            ->get()
            ->map(fn($c) => $c->name['en'] ?? '')
            ->filter()
            ->toArray();

        $excludeList = !empty($existingNames) ? "\nDo NOT include these cities (already exist): " . implode(', ', array_slice($existingNames, 0, 100)) : '';

        try {
            // Round 1: Get all major cities, towns, and districts
            $prompt1 = "List ALL cities, towns, and significant populated places in {$countryName}.\n"
                . "Include: the capital, all governorate/state/province capitals, all cities, major towns, and district centers.\n"
                . "Be as comprehensive as possible — include every city and town that would appear on a detailed map.\n"
                . "For each place, provide the name in these languages: {$localeList}\n"
                . "Return ONLY a JSON array. Each element: {\"en\": \"Name\", \"ar\": \"الاسم\", ...}\n"
                . "Return as many cities as you can (aim for 50-200+ depending on country size)."
                . $excludeList
                . "\nReturn ONLY the JSON array, no markdown.";

            $cities1 = $this->callOpenAiForCities($prompt1);
            $result1 = $this->insertCities($cities1, $country->id, $existingNames);
            $totalCreated += $result1['created'];
            $totalSkipped += $result1['skipped'];

            // Update existing names list
            $existingNames = array_merge($existingNames, collect($cities1)->pluck('en')->filter()->toArray());

            // Round 2: Get remaining smaller towns if the country is large
            if ($totalCreated >= 40) {
                $excludeList2 = "\nDo NOT include these cities (already added): " . implode(', ', array_slice($existingNames, 0, 200));

                $prompt2 = "List MORE cities and towns in {$countryName} that were not in the previous list.\n"
                    . "Focus on: smaller cities, rural towns, suburban areas, industrial towns, coastal towns, border towns.\n"
                    . "For each place, provide the name in these languages: {$localeList}\n"
                    . "Return ONLY a JSON array. Each element: {\"en\": \"Name\", \"ar\": \"الاسم\", ...}"
                    . $excludeList2
                    . "\nReturn ONLY the JSON array, no markdown.";

                $cities2 = $this->callOpenAiForCities($prompt2);
                $result2 = $this->insertCities($cities2, $country->id, $existingNames);
                $totalCreated += $result2['created'];
                $totalSkipped += $result2['skipped'];
            }

            return [
                'success' => true,
                'country' => $countryName,
                'created' => $totalCreated,
                'skipped' => $totalSkipped,
                'total' => $totalCreated + $totalSkipped,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate cities for {$countryName}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'created' => $totalCreated];
        }
    }

    /**
     * Call OpenAI to get cities list.
     */
    protected function callOpenAiForCities(string $prompt): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(180)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a geography expert with comprehensive knowledge of world cities. Provide accurate, complete city data with correct official translations. Return only valid JSON arrays.",
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 16384,
            ]);

        if (!$response->successful()) {
            throw new \Exception("OpenAI API error: HTTP {$response->status()}");
        }

        $content = $response->json('choices.0.message.content', '');
        $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
        $content = preg_replace('/\n?```\s*$/m', '', $content);
        $content = trim($content);

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON parse error: " . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Insert cities into DB, skipping duplicates.
     */
    protected function insertCities(array $citiesData, int $countryId, array $existingNames): array
    {
        $created = 0;
        $skipped = 0;

        foreach ($citiesData as $cityData) {
            if (!is_array($cityData)) continue;
            $englishName = $cityData['en'] ?? array_values($cityData)[0] ?? null;
            if (!$englishName) continue;

            // Skip duplicate
            if (in_array($englishName, $existingNames)) {
                $skipped++;
                continue;
            }

            // Also check DB
            $exists = cities::where('country_id', $countryId)
                ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$englishName])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            cities::create([
                'name' => $cityData,
                'country_id' => $countryId,
            ]);
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Translate missing country and city names to all active languages.
     */
    public function translateLocationNames(countries $country): array
    {
        $activeLocales = Language::getActiveOrdered()->pluck('code', 'name')->toArray();
        $translated = 0;

        // Collect all names that need translation
        $toTranslate = [];

        // Country name
        $countryName = $country->name;
        foreach ($activeLocales as $langName => $locale) {
            if (empty($countryName[$locale])) {
                $sourceText = $countryName['en'] ?? array_values(array_filter($countryName))[0] ?? '';
                if ($sourceText) {
                    $toTranslate["country_{$country->id}_{$locale}"] = $sourceText;
                }
            }
        }

        // Currency name
        $currName = $country->currency_name;
        foreach ($activeLocales as $langName => $locale) {
            if (empty($currName[$locale])) {
                $sourceText = $currName['en'] ?? array_values(array_filter($currName))[0] ?? '';
                if ($sourceText) {
                    $toTranslate["currency_{$country->id}_{$locale}"] = $sourceText;
                }
            }
        }

        // City names
        $countryCities = $country->cities;
        foreach ($countryCities as $city) {
            $cityName = $city->name;
            foreach ($activeLocales as $langName => $locale) {
                if (empty($cityName[$locale])) {
                    $sourceText = $cityName['en'] ?? array_values(array_filter($cityName))[0] ?? '';
                    if ($sourceText) {
                        $toTranslate["city_{$city->id}_{$locale}"] = $sourceText;
                    }
                }
            }
        }

        if (empty($toTranslate)) {
            return ['success' => true, 'translated' => 0, 'message' => 'All names already translated'];
        }

        // Group by target locale for batch translation
        $localeGroups = [];
        foreach ($toTranslate as $key => $text) {
            $parts = explode('_', $key);
            $locale = end($parts);
            $localeGroups[$locale][$key] = $text;
        }

        foreach ($localeGroups as $locale => $batch) {
            $langName = array_search($locale, $activeLocales) ?: $locale;

            // Send to OpenAI
            $result = $this->translateBatch($batch, $langName);
            if (!$result) continue;

            // Apply translations
            foreach ($result as $key => $translatedText) {
                $parts = explode('_', $key, 3);
                $type = $parts[0]; // country, currency, city
                $id = (int) $parts[1];
                $targetLocale = $parts[2];

                if ($type === 'country') {
                    $c = countries::find($id);
                    if ($c) {
                        $name = $c->name;
                        $name[$targetLocale] = $translatedText;
                        $c->name = $name;
                        $c->save();
                        $translated++;
                    }
                } elseif ($type === 'currency') {
                    $c = countries::find($id);
                    if ($c) {
                        $cn = $c->currency_name;
                        $cn[$targetLocale] = $translatedText;
                        $c->currency_name = $cn;
                        $c->save();
                        $translated++;
                    }
                } elseif ($type === 'city') {
                    $city = cities::find($id);
                    if ($city) {
                        $name = $city->name;
                        $name[$targetLocale] = $translatedText;
                        $city->name = $name;
                        $city->save();
                        $translated++;
                    }
                }
            }
        }

        return ['success' => true, 'translated' => $translated];
    }

    /**
     * Translate a batch of texts using OpenAI.
     */
    protected function translateBatch(array $batch, string $targetLanguage): ?array
    {
        if (empty($batch) || empty($this->apiKey)) return null;

        $jsonInput = json_encode($batch, JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->retry(2, 3000)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a professional translator specializing in geography and place names. Return only valid JSON.",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Translate the following JSON values to {$targetLanguage}.\n"
                                . "These are country and city names — use the official/commonly accepted name in the target language.\n"
                                . "Keep the JSON keys exactly the same.\n"
                                . "Return ONLY the JSON object, no markdown.\n\n"
                                . $jsonInput,
                        ],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 4096,
                ]);

            if (!$response->successful()) return null;

            $content = $response->json('choices.0.message.content', '');
            $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
            $content = preg_replace('/\n?```\s*$/m', '', $content);

            $result = json_decode(trim($content), true);
            return json_last_error() === JSON_ERROR_NONE ? $result : null;
        } catch (\Exception $e) {
            Log::error("Location translation failed: " . $e->getMessage());
            return null;
        }
    }

    protected function updateProgress(string $key, int $current, int $total, string $task, int $created, int $skipped): void
    {
        Cache::put($key, [
            'status' => 'running',
            'total' => $total,
            'current' => $current,
            'percentage' => $total > 0 ? round(($current / $total) * 100) : 0,
            'current_task' => $task,
            'created' => $created,
            'skipped' => $skipped,
        ], 7200);
    }

    public static function getProgress(string $key): ?array
    {
        return Cache::get($key);
    }
}
