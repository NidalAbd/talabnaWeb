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
     * POST /api/admin/location-import/countries
     */
    public function importCountries(Request $request): JsonResponse
    {
        $region = $request->input('region'); // africa, americas, asia, europe, oceania

        $progressKey = 'location_import_progress';

        Cache::put($progressKey, [
            'status' => 'running',
            'current_task' => 'Fetching countries from API...',
            'total' => 0,
            'current' => 0,
            'percentage' => 0,
            'created' => 0,
            'skipped' => 0,
        ], 7200);

        // Spawn background process
        $artisan = base_path('artisan');
        $logFile = storage_path('logs/location-import.log');
        $regionArg = $region ? "--region=" . escapeshellarg($region) : '';

        $command = "nohup php {$artisan} location:import {$regionArg} --skip-cities > {$logFile} 2>&1 &";
        exec($command);

        return response()->json([
            'success' => true,
            'message' => 'Country import started' . ($region ? " for region: {$region}" : ' (all countries)'),
        ]);
    }

    /**
     * Generate cities for a specific country.
     * POST /api/admin/location-import/countries/{id}/generate-cities
     */
    public function generateCities(Request $request, int $id): JsonResponse
    {
        $country = countries::findOrFail($id);
        $count = (int) $request->input('count', 30);
        $countryName = $country->name['en'] ?? 'Unknown';

        // Spawn background process
        $artisan = base_path('artisan');
        $logFile = storage_path("logs/cities-{$country->iso_code}.log");
        $isoCode = escapeshellarg($country->iso_code);

        $command = "nohup php {$artisan} location:import --country={$isoCode} --cities-per-country={$count} > {$logFile} 2>&1 &";
        exec($command);

        return response()->json([
            'success' => true,
            'message' => "City generation started for {$countryName} ({$count} cities)",
        ]);
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
