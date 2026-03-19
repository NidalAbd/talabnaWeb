<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\countries;
use App\Services\AiLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationImportController extends Controller
{
    protected AiLocationService $service;

    public function __construct(AiLocationService $service)
    {
        $this->service = $service;
    }

    /**
     * Import countries from RestCountries API.
     * Runs inline — RestCountries is fast (no OpenAI needed for countries).
     *
     * POST /api/admin/location-import/countries
     */
    public function importCountries(Request $request): JsonResponse
    {
        $isoCodes = $request->input('iso_codes', []);
        $language = $request->input('language', '');

        try {
            ignore_user_abort(true);
            set_time_limit(1800); // 30 min for countries + cities generation

            if (!empty($isoCodes)) {
                // Fetch specific countries by ISO code
                $apiData = [];
                foreach (array_chunk($isoCodes, 10) as $chunk) {
                    $codes = implode(',', $chunk);
                    $response = \Illuminate\Support\Facades\Http::timeout(30)
                        ->get("https://restcountries.com/v3.1/alpha?codes={$codes}");
                    if ($response->successful()) {
                        $apiData = array_merge($apiData, $response->json());
                    }
                }
            } else {
                $apiData = $this->service->fetchCountriesFromApi(null);
            }

            $result = $this->service->importCountries($apiData);

            // Auto-generate cities for all newly imported countries + existing ones missing cities
            $allIsoCodes = collect($apiData)->pluck('cca2')->filter()->toArray();
            $countriesNeedingCities = \App\Models\countries::whereIn('iso_code', $allIsoCodes)
                ->withCount('cities')
                ->get()
                ->filter(fn($c) => $c->cities_count === 0);

            $citiesCreated = 0;
            $citiesErrors = 0;
            foreach ($countriesNeedingCities as $country) {
                $cityResult = $this->service->generateCitiesForCountry($country);
                if ($cityResult['success'] ?? false) {
                    $citiesCreated += $cityResult['created'];
                } else {
                    $citiesErrors++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Imported {$result['created']} countries (skipped {$result['skipped']}). Generated {$citiesCreated} cities for {$countriesNeedingCities->count()} countries.",
                'data' => array_merge($result, ['cities_created' => $citiesCreated, 'cities_errors' => $citiesErrors]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate ALL cities for a specific country using OpenAI.
     * POST /api/admin/location-import/countries/{id}/generate-cities
     */
    public function generateCities(Request $request, int $id): JsonResponse
    {
        $country = countries::findOrFail($id);
        $countryName = $country->name['en'] ?? 'Unknown';
        $existingCount = $country->cities()->count();

        try {
            ignore_user_abort(true);
            set_time_limit(600);

            $result = $this->service->generateCitiesForCountry($country);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success']
                    ? "Generated {$result['created']} new cities for {$countryName} (had {$existingCount}, skipped {$result['skipped']} duplicates)"
                    : "Failed: " . ($result['error'] ?? 'Unknown error'),
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Translate country and city names to all active languages.
     * POST /api/admin/location-import/countries/{id}/translate
     */
    public function translateNames(int $id): JsonResponse
    {
        $country = countries::findOrFail($id);
        $countryName = $country->name['en'] ?? 'Unknown';

        $result = $this->service->translateLocationNames($country);

        return response()->json([
            'success' => $result['success'],
            'message' => "Translated {$result['translated']} names for {$countryName}",
            'translated' => $result['translated'],
        ]);
    }

    /**
     * Check import progress.
     */
    public function progress(): JsonResponse
    {
        $progress = AiLocationService::getProgress('location_import_progress');
        return response()->json(['success' => true, 'progress' => $progress]);
    }

    /**
     * Get import options based on active languages.
     */
    public function regions(): JsonResponse
    {
        // Map each active language to the countries that speak it
        $languageCountries = [
            'ar' => ['SA', 'AE', 'EG', 'IQ', 'JO', 'KW', 'BH', 'QA', 'OM', 'YE', 'SY', 'LB', 'PS', 'LY', 'TN', 'DZ', 'MA', 'SD', 'MR', 'SO', 'DJ', 'KM'],
            'en' => ['US', 'GB', 'CA', 'AU', 'NZ', 'IE', 'ZA', 'KE', 'NG', 'GH', 'PH', 'SG', 'JM', 'TT'],
            'hi' => ['IN'],
            'tr' => ['TR', 'CY'],
            'fr' => ['FR', 'BE', 'CH', 'CA', 'SN', 'CI', 'ML', 'BF', 'NE', 'TD', 'GN', 'CM', 'MG', 'CD', 'CG', 'GA', 'TG', 'BJ', 'HT'],
            'es' => ['ES', 'MX', 'CO', 'AR', 'PE', 'VE', 'CL', 'EC', 'GT', 'CU', 'BO', 'DO', 'HN', 'PY', 'SV', 'NI', 'CR', 'PA', 'UY'],
            'ur' => ['PK'],
            'bn' => ['BD'],
            'pt' => ['BR', 'PT', 'AO', 'MZ'],
            'ru' => ['RU', 'BY', 'KZ', 'KG', 'UZ'],
            'id' => ['ID'],
            'de' => ['DE', 'AT', 'CH'],
            'zh' => ['CN', 'TW', 'HK', 'MO'],
            'ku' => ['IQ', 'TR', 'SY', 'IR'],
            'fa' => ['IR', 'AF', 'TJ'],
            'sw' => ['TZ', 'KE', 'UG', 'RW', 'CD'],
            'ms' => ['MY', 'BN', 'SG'],
        ];

        $activeLanguages = \App\Models\Language::getActiveOrdered();
        $options = [];

        foreach ($activeLanguages as $lang) {
            $codes = $languageCountries[$lang->code] ?? [];
            if (empty($codes)) continue;

            // Count how many are already imported
            $existingCount = \App\Models\countries::whereIn('iso_code', $codes)->count();

            $options[] = [
                'key' => $lang->code,
                'label' => $lang->name,
                'native' => $lang->native_name,
                'countries_count' => count($codes),
                'existing_count' => $existingCount,
                'new_count' => count($codes) - $existingCount,
                'iso_codes' => $codes,
            ];
        }

        return response()->json([
            'success' => true,
            'options' => $options,
        ]);
    }
}
