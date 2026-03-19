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
     * Generate cities for a country using OpenAI.
     */
    public function generateCitiesForCountry(countries $country, int $count = 30): array
    {
        $countryName = $country->name['en'] ?? 'Unknown';
        $activeLocales = Language::getActiveOrdered()->pluck('code')->toArray();
        // Always include en and ar
        if (!in_array('en', $activeLocales)) array_unshift($activeLocales, 'en');
        if (!in_array('ar', $activeLocales)) $activeLocales[] = 'ar';

        $localeList = implode(', ', $activeLocales);

        $prompt = "Generate the top {$count} most important cities and towns in {$countryName}.\n"
            . "Include the capital, major cities, and important regional centers.\n"
            . "For each city, provide the name translated into these languages: {$localeList}\n"
            . "Return ONLY a JSON array. Each element should be an object with language codes as keys.\n"
            . "Example format: [{\"en\": \"Cairo\", \"ar\": \"القاهرة\", \"tr\": \"Kahire\"}, ...]\n"
            . "Return ONLY the JSON array, no markdown formatting.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a geography expert. Provide accurate city data with correct translations. Return only valid JSON.",
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 4096,
                ]);

            if (!$response->successful()) {
                throw new \Exception("OpenAI API error: HTTP {$response->status()}");
            }

            $content = $response->json('choices.0.message.content', '');
            $content = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
            $content = preg_replace('/\n?```\s*$/m', '', $content);
            $content = trim($content);

            $citiesData = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to parse city data: " . json_last_error_msg());
            }

            // Insert cities, skip duplicates
            $created = 0;
            $skipped = 0;

            foreach ($citiesData as $cityData) {
                $englishName = $cityData['en'] ?? array_values($cityData)[0] ?? null;
                if (!$englishName) continue;

                // Check duplicate
                $exists = cities::where('country_id', $country->id)
                    ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$englishName])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                cities::create([
                    'name' => $cityData, // Already a map with all locales
                    'country_id' => $country->id,
                ]);
                $created++;
            }

            return [
                'success' => true,
                'country' => $countryName,
                'created' => $created,
                'skipped' => $skipped,
                'total' => count($citiesData),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate cities for {$countryName}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
