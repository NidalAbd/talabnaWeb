<?php

namespace App\Console\Commands;

use App\Models\countries;
use App\Services\AiLocationService;
use Illuminate\Console\Command;

class ImportLocationsCommand extends Command
{
    protected $signature = 'location:import
        {--region= : Region to import (africa, americas, asia, europe, oceania)}
        {--country= : Specific country ISO code (e.g. FR, DE, TR)}
        {--cities-per-country=0 : Number of cities (0 = all cities)}
        {--skip-cities : Import countries only, no city generation}
        {--translate : Translate all names to active languages after import}';

    protected $description = 'Import countries from RestCountries API and generate cities via OpenAI';

    public function handle(AiLocationService $service): int
    {
        $region = $this->option('region');
        $countryCode = $this->option('country');
        $citiesPerCountry = (int) $this->option('cities-per-country');
        $skipCities = $this->option('skip-cities');
        $translate = $this->option('translate');

        // Step 1: Fetch countries
        $this->info('Fetching countries from RestCountries API...');

        try {
            if ($countryCode) {
                // Fetch single country
                $apiData = \Illuminate\Support\Facades\Http::timeout(30)
                    ->get("https://restcountries.com/v3.1/alpha/{$countryCode}")
                    ->json();
                if (!is_array($apiData) || empty($apiData)) {
                    $this->error("Country not found: {$countryCode}");
                    return 1;
                }
                // API returns array for single country too
                if (isset($apiData['name'])) {
                    $apiData = [$apiData];
                }
            } else {
                $apiData = $service->fetchCountriesFromApi($region);
            }
        } catch (\Exception $e) {
            $this->error("Failed to fetch countries: {$e->getMessage()}");
            return 1;
        }

        $this->info('Found ' . count($apiData) . ' countries');

        // Step 2: Import countries
        $this->info('Importing countries...');
        $result = $service->importCountries($apiData);

        $this->info("Countries: {$result['created']} created, {$result['skipped']} skipped, {$result['errors']} errors");

        // Step 3: Generate cities
        if (!$skipCities) {
            $this->info("Generating cities ({$citiesPerCountry} per country)...");

            // Get countries for city generation
            $query = countries::query();
            if ($countryCode) {
                // Specific country — always regenerate (delete + recreate)
                $query->where('iso_code', strtoupper($countryCode));
            } elseif ($region) {
                $isoCodes = collect($apiData)->pluck('cca2')->filter()->toArray();
                $query->whereIn('iso_code', $isoCodes);
            }

            // For bulk import: only countries with 0 cities
            // For specific country: always regenerate
            if (!$countryCode) {
                $query->withCount('cities')->having('cities_count', '=', 0);
            }

            $countriesNeedingCities = $query->get();

            if ($countriesNeedingCities->isEmpty()) {
                $this->info('No countries need city generation.');
            } else {
                $bar = $this->output->createProgressBar($countriesNeedingCities->count());
                $bar->start();

                foreach ($countriesNeedingCities as $country) {
                    $countryName = $country->name['en'] ?? 'Unknown';
                    $bar->setMessage("Generating cities for {$countryName}...");

                    $cityResult = $service->generateCitiesForCountry($country, $citiesPerCountry);

                    if ($cityResult['success']) {
                        $this->line(" {$countryName}: {$cityResult['created']} cities created");
                    } else {
                        $this->warn(" {$countryName}: Failed - {$cityResult['error']}");
                    }

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            }
        }

        // Step 4: Translate
        if ($translate) {
            $this->info('Translating location names...');

            $allCountries = $countryCode
                ? countries::where('iso_code', strtoupper($countryCode))->get()
                : countries::all();

            $bar = $this->output->createProgressBar($allCountries->count());
            $bar->start();

            $totalTranslated = 0;
            foreach ($allCountries as $country) {
                $result = $service->translateLocationNames($country);
                $totalTranslated += $result['translated'] ?? 0;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Translated {$totalTranslated} names");
        }

        $this->info('Done!');
        return 0;
    }
}
