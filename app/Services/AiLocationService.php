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
     * Generate ALL cities for a country.
     * Step 1: Fetch city names from CountriesNow API (free, complete data)
     * Step 2: Translate names to all active languages via OpenAI
     * Deletes existing cities first.
     */
    public function generateCitiesForCountry(countries $country, int $count = 0, bool $deleteExisting = true): array
    {
        $countryName = $country->name['en'] ?? 'Unknown';

        // Delete existing cities
        if ($deleteExisting) {
            $deletedCount = cities::where('country_id', $country->id)->count();
            cities::where('country_id', $country->id)->delete();
            Log::info("Deleted {$deletedCount} existing cities for {$countryName}");
        }

        try {
            // Step 1: Fetch ALL city names — try CountriesNow API first, fallback to OpenAI
            $cityNames = [];
            $source = 'unknown';

            try {
                $cityNames = $this->fetchCitiesFromApi($countryName);
                $source = 'CountriesNow';
            } catch (\Exception $e) {
                Log::info("CountriesNow failed for {$countryName}: {$e->getMessage()}. Falling back to OpenAI.");
            }

            if (empty($cityNames)) {
                // Fallback: use OpenAI to generate city list (English only)
                $cityNames = $this->generateCityNamesViaAi($countryName);
                $source = 'OpenAI';
            }

            if (empty($cityNames)) {
                return ['success' => false, 'error' => 'No cities found from any source', 'created' => 0];
            }

            Log::info("Fetched " . count($cityNames) . " cities for {$countryName} from {$source}");

            // Step 2: Insert ALL cities immediately with English names
            $totalCreated = 0;
            foreach ($cityNames as $name) {
                if (empty(trim($name))) continue;
                cities::create([
                    'name' => ['en' => $name],
                    'country_id' => $country->id,
                ]);
                $totalCreated++;
            }

            Log::info("Inserted {$totalCreated} cities for {$countryName} (English only)");

            // Step 3: Translate city names to all active languages via OpenAI (in batches)
            $activeLocales = Language::getActiveOrdered()->pluck('code')->toArray();
            $needsTranslation = array_filter($activeLocales, fn($l) => $l !== 'en');
            $translatedCount = 0;

            if (!empty($needsTranslation)) {
                $allCities = cities::where('country_id', $country->id)->get();
                $batches = $allCities->chunk(50);

                foreach ($batches as $batchIndex => $batch) {
                    $cityNamesForTranslation = $batch->map(fn($c) => $c->name['en'] ?? '')->filter()->values()->toArray();
                    if (empty($cityNamesForTranslation)) continue;

                    $translated = $this->translateCityBatch($cityNamesForTranslation, $countryName, $needsTranslation);

                    // Update each city with translations
                    foreach ($batch as $city) {
                        $enName = $city->name['en'] ?? '';
                        // Find matching translation
                        foreach ($translated as $tData) {
                            if (($tData['en'] ?? '') === $enName) {
                                $city->name = $tData;
                                $city->save();
                                $translatedCount++;
                                break;
                            }
                        }
                    }

                    Log::info("Translated batch " . ($batchIndex + 1) . "/" . $batches->count() . " for {$countryName} ({$translatedCount} done)");
                }
            }

            return [
                'success' => true,
                'country' => $countryName,
                'created' => $totalCreated,
                'translated' => $translatedCount,
                'fetched_from_api' => count($cityNames),
                'total' => $totalCreated,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate cities for {$countryName}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage(), 'created' => 0];
        }
    }

    /**
     * Fetch city names from CountriesNow API (free, no key needed).
     */
    /**
     * Common name aliases for countries that CountriesNow uses differently.
     */
    protected array $countryNameAliases = [
        'UAE' => 'United Arab Emirates',
        'USA' => 'United States',
        'UK' => 'United Kingdom',
        'South Korea' => 'Korea, South',
        'North Korea' => 'Korea, North',
        'Czech Republic' => 'Czechia',
        'DR Congo' => 'Congo, The Democratic Republic of the',
    ];

    protected function fetchCitiesFromApi(string $countryName): array
    {
        // Try the given name and known aliases
        $namesToTry = [$countryName];
        if (isset($this->countryNameAliases[$countryName])) {
            $namesToTry[] = $this->countryNameAliases[$countryName];
        }
        // Also try reverse alias
        $reverseAlias = array_search($countryName, $this->countryNameAliases);
        if ($reverseAlias) {
            $namesToTry[] = $reverseAlias;
        }

        foreach ($namesToTry as $name) {
            try {
                $response = Http::timeout(30)
                    ->get("https://countriesnow.space/api/v0.1/countries/cities/q", [
                        'country' => $name,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!($data['error'] ?? true) && !empty($data['data'])) {
                        return $data['data'];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new \Exception("CountriesNow API: No data for {$countryName}");
    }

    /**
     * Fallback: Generate city names via OpenAI when CountriesNow doesn't have the country.
     * Returns array of English city name strings.
     */
    protected function generateCityNamesViaAi(string $countryName): array
    {
        $allCities = [];

        // Multiple rounds to get comprehensive list
        for ($round = 1; $round <= 5; $round++) {
            $excludeList = !empty($allCities) ? "\nDo NOT include: " . implode(', ', array_slice($allCities, 0, 300)) : '';

            $prompt = $round === 1
                ? "List ALL cities, towns, villages, and populated places in {$countryName}. Return ONLY a JSON array of English names. Example: [\"Gaza City\", \"Ramallah\", ...]. Be extremely comprehensive. Return ONLY the JSON array, no markdown."
                : "List MORE cities and towns in {$countryName} not already listed. Return ONLY a JSON array of English names. If no more, return []. No markdown." . $excludeList;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a geography expert. Return only valid JSON arrays.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 16384,
                ]);

                $content = $response->json('choices.0.message.content', '');
                $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
                $content = preg_replace('/\n?```\s*$/m', '', $content);
                $names = json_decode(trim($content), true);

                if (!is_array($names) || empty($names)) break;

                $newNames = array_diff($names, $allCities);
                $allCities = array_merge($allCities, array_values($newNames));

                Log::info("OpenAI round {$round}: Got " . count($newNames) . " new cities for {$countryName} (total: " . count($allCities) . ")");

                if (count($newNames) < 5) break; // Diminishing returns
            } catch (\Exception $e) {
                Log::error("OpenAI city generation round {$round} failed: " . $e->getMessage());
                break;
            }
        }

        return $allCities;
    }

    /**
     * Translate a batch of city names to multiple languages via OpenAI.
     */
    protected function translateCityBatch(array $cityNames, string $countryName, array $targetLocales): array
    {
        $localeList = implode(', ', $targetLocales);

        // Build input: {"City1": "City1", "City2": "City2", ...}
        $input = [];
        foreach ($cityNames as $name) {
            $input[$name] = $name;
        }

        $jsonInput = json_encode($input, JSON_UNESCAPED_UNICODE);

        $prompt = "Translate these city/town names from {$countryName} into these languages: {$localeList}\n"
            . "Use the official/commonly accepted name in each language.\n"
            . "Return a JSON object where each key is the English city name and the value is an object with language codes.\n"
            . "Example: {\"Cairo\": {\"ar\": \"القاهرة\", \"tr\": \"Kahire\", \"fr\": \"Le Caire\"}}\n"
            . "Return ONLY the JSON, no markdown.\n\n"
            . $jsonInput;

        try {
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
                            'content' => "You are a geography expert. Translate city names accurately. Return only valid JSON.",
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 16384,
                ]);

            if (!$response->successful()) {
                Log::error("OpenAI city translation failed: HTTP {$response->status()}");
                // Fallback: return English-only
                return array_map(fn($name) => ['en' => $name], $cityNames);
            }

            $content = $response->json('choices.0.message.content', '');
            $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
            $content = preg_replace('/\n?```\s*$/m', '', $content);
            $translated = json_decode(trim($content), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse city translations: " . json_last_error_msg());
                return array_map(fn($name) => ['en' => $name], $cityNames);
            }

            // Build final city data with en + translated locales
            $result = [];
            foreach ($cityNames as $name) {
                $cityData = ['en' => $name];
                if (isset($translated[$name]) && is_array($translated[$name])) {
                    $cityData = array_merge($cityData, $translated[$name]);
                }
                $result[] = $cityData;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("City translation error: " . $e->getMessage());
            return array_map(fn($name) => ['en' => $name], $cityNames);
        }
    }

    /**
     * Call OpenAI to get cities list.
     */
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
