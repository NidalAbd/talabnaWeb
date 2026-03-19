<?php

namespace App\Console\Commands;

use App\Models\countries;
use App\Models\cities;
use App\Services\AiLocationService;
use Illuminate\Console\Command;

class GenerateAllCities extends Command
{
    protected $signature = 'cities:generate-all {--country= : Specific ISO code} {--no-translate : Skip translation, English only}';
    protected $description = 'Fetch ALL cities from CountriesNow API for all countries and translate via OpenAI';

    public function handle(AiLocationService $service): int
    {
        $specificCountry = $this->option('country');
        $noTranslate = $this->option('no-translate');

        if ($specificCountry) {
            $countriesToProcess = countries::where('iso_code', strtoupper($specificCountry))->get();
        } else {
            $countriesToProcess = countries::orderBy('id')->get();
        }

        $this->info("Processing {$countriesToProcess->count()} countries...");

        foreach ($countriesToProcess as $country) {
            $countryName = $country->name['en'] ?? 'Unknown';
            $existingCities = $country->cities()->count();

            $this->info("\n--- {$countryName} ({$country->iso_code}) ---");

            if ($existingCities > 0) {
                $this->info("Deleting {$existingCities} existing cities...");
                cities::where('country_id', $country->id)->delete();
            }

            // Try CountriesNow API first
            $cityNames = [];
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->get("https://countriesnow.space/api/v0.1/countries/cities/q", [
                        'country' => $countryName,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!($data['error'] ?? true)) {
                        $cityNames = $data['data'] ?? [];
                    }
                }
            } catch (\Exception $e) {
                $this->warn("CountriesNow failed: {$e->getMessage()}");
            }

            $source = 'CountriesNow';

            // Fallback to OpenAI
            if (empty($cityNames)) {
                $this->info("CountriesNow has no data. Using OpenAI...");
                $source = 'OpenAI';

                // Use the service's OpenAI fallback
                try {
                    $result = $service->generateCitiesForCountry($country, 0, false);
                    $this->info("OpenAI: Created {$result['created']} cities");
                    continue; // Skip to next country, OpenAI already inserted
                } catch (\Exception $e) {
                    $this->error("OpenAI also failed: {$e->getMessage()}");
                    continue;
                }
            }

            $this->info("{$source}: Found " . count($cityNames) . " cities");

            // Insert all cities with English names
            $created = 0;
            foreach ($cityNames as $name) {
                if (empty(trim($name))) continue;
                cities::create([
                    'name' => ['en' => $name],
                    'country_id' => $country->id,
                ]);
                $created++;
            }

            $this->info("Inserted {$created} cities (English only)");

            // Translate if not skipped
            if (!$noTranslate && $created > 0) {
                $this->info("Translating city names...");
                $result = $service->translateLocationNames($country);
                $this->info("Translated {$result['translated']} names");
            }
        }

        $this->info("\n=== DONE ===");
        $totalCities = cities::count();
        $this->info("Total cities in database: {$totalCities}");

        return 0;
    }
}
