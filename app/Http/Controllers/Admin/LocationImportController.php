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
        $region = $request->input('region');

        try {
            set_time_limit(120);

            $apiData = $this->service->fetchCountriesFromApi($region);
            $result = $this->service->importCountries($apiData);

            return response()->json([
                'success' => true,
                'message' => "Imported {$result['created']} countries, skipped {$result['skipped']} (already exist), {$result['errors']} errors",
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
     * Generate ALL cities for a specific country using OpenAI.
     * POST /api/admin/location-import/countries/{id}/generate-cities
     */
    public function generateCities(Request $request, int $id): JsonResponse
    {
        $country = countries::findOrFail($id);
        $countryName = $country->name['en'] ?? 'Unknown';

        try {
            set_time_limit(300); // 5 min for OpenAI calls

            $result = $this->service->generateCitiesForCountry($country);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['success']
                    ? "Generated {$result['created']} cities for {$countryName} (skipped {$result['skipped']} duplicates)"
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
     * Get available regions for import.
     */
    public function regions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'regions' => [
                ['key' => 'africa', 'label' => 'Africa', 'icon' => 'fas fa-globe-africa'],
                ['key' => 'americas', 'label' => 'Americas', 'icon' => 'fas fa-globe-americas'],
                ['key' => 'asia', 'label' => 'Asia', 'icon' => 'fas fa-globe-asia'],
                ['key' => 'europe', 'label' => 'Europe', 'icon' => 'fas fa-globe-europe'],
                ['key' => 'oceania', 'label' => 'Oceania', 'icon' => 'fas fa-globe'],
            ],
        ]);
    }
}
