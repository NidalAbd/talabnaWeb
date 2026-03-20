<?php

namespace App\Console\Commands;

use App\Models\countries;
use App\Services\AiLocationService;
use Illuminate\Console\Command;

class TranslateLocations extends Command
{
    protected $signature = 'locations:translate {--country= : Specific country ID} {--locale= : Specific locale only}';
    protected $description = 'Translate country and city names to all active languages via OpenAI';

    public function handle(AiLocationService $service): int
    {
        $countryId = $this->option('country');

        if ($countryId) {
            $allCountries = countries::where('id', $countryId)->get();
        } else {
            $allCountries = countries::orderBy('id')->get();
        }

        $this->info("Translating locations for {$allCountries->count()} countries...");
        $totalTranslated = 0;

        foreach ($allCountries as $country) {
            $name = $country->name['en'] ?? 'Unknown';
            $cityCount = $country->cities()->count();
            $this->info("\n--- {$name} ({$country->iso_code}) - {$cityCount} cities ---");

            $result = $service->translateLocationNames($country);
            $count = $result['translated'] ?? 0;
            $totalTranslated += $count;

            $this->info("  Translated: {$count} items");
        }

        $this->info("\n=== DONE === Total translated: {$totalTranslated}");
        return 0;
    }
}
