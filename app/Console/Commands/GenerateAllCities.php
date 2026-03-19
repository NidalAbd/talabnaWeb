<?php

namespace App\Console\Commands;

use App\Models\countries;
use App\Models\cities;
use App\Services\AiLocationService;
use Illuminate\Console\Command;

class GenerateAllCities extends Command
{
    protected $signature = 'cities:generate-all {--country= : Specific ISO code} {--no-translate : Skip translation, English only}';
    protected $description = 'Fetch ALL cities for all countries (CountriesNow API + OpenAI fallback) and optionally translate';

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

        $totalCitiesCreated = 0;

        foreach ($countriesToProcess as $country) {
            $countryName = $country->name['en'] ?? 'Unknown';
            $this->info("\n--- {$countryName} ({$country->iso_code}) ---");

            // generateCitiesForCountry handles:
            // 1. Delete existing cities
            // 2. Try CountriesNow API (with aliases like UAE → United Arab Emirates)
            // 3. Fallback to OpenAI for countries not in CountriesNow
            // 4. Insert all cities with English names
            // 5. Translate to all active languages (unless --no-translate)

            if ($noTranslate) {
                // Custom fast path: just fetch and insert English names
                $existingCount = $country->cities()->count();
                if ($existingCount > 0) {
                    $this->info("Deleting {$existingCount} existing cities...");
                    cities::where('country_id', $country->id)->delete();
                }

                // Try CountriesNow with aliases
                $cityNames = [];
                try {
                    $cityNames = $service->fetchCitiesFromApi($countryName);
                    $this->info("CountriesNow: Found " . count($cityNames) . " cities");
                } catch (\Exception $e) {
                    $this->warn("CountriesNow: {$e->getMessage()}");
                }

                if (empty($cityNames)) {
                    $this->info("Using OpenAI fallback...");
                    try {
                        $cityNames = $service->generateCityNamesViaAi($countryName);
                        $this->info("OpenAI: Found " . count($cityNames) . " cities");
                    } catch (\Exception $e) {
                        $this->error("OpenAI failed: {$e->getMessage()}");
                        continue;
                    }
                }

                // Insert English only
                $created = 0;
                foreach ($cityNames as $name) {
                    if (empty(trim($name))) continue;
                    cities::create(['name' => ['en' => $name], 'country_id' => $country->id]);
                    $created++;
                }
                $this->info("Inserted {$created} cities (English only)");
                $totalCitiesCreated += $created;
            } else {
                // Full path: fetch + translate via service
                $result = $service->generateCitiesForCountry($country);
                if ($result['success'] ?? false) {
                    $this->info("Created {$result['created']} cities");
                    $totalCitiesCreated += $result['created'];
                } else {
                    $this->error("Failed: " . ($result['error'] ?? 'Unknown'));
                }
            }
        }

        $this->info("\n=== DONE ===");
        $this->info("Total cities created: {$totalCitiesCreated}");
        $this->info("Total cities in database: " . cities::count());

        return 0;
    }
}
