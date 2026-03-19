<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Services\AutoTranslationService;
use Illuminate\Console\Command;

class AutoTranslate extends Command
{
    protected $signature = 'translate:auto {locale} {--language-name=} {--tier=all} {--limit=}';
    protected $description = 'Auto-translate content using OpenAI GPT-4o-mini';

    public function handle(AutoTranslationService $service): int
    {
        $locale = $this->argument('locale');
        $tier = $this->option('tier');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        // Get language name
        $languageName = $this->option('language-name');
        if (!$languageName) {
            $language = Language::getByCode($locale);
            $languageName = $language?->name ?? config("languages.supported.{$locale}.name", $locale);
        }

        $this->info("Translating to {$languageName} ({$locale}), tier: {$tier}");

        $results = [];

        if ($tier === 'all' || $tier === '1') {
            $this->info('Starting Tier 1 (UI strings)...');
            $results['tier1'] = $service->translateTier1($locale, $languageName);
            $this->info("Tier 1: {$results['tier1']['completed']} completed, {$results['tier1']['errors']} errors");
        }

        if ($tier === 'all' || $tier === '2') {
            $this->info('Starting Tier 2 (core content)...');
            $results['tier2'] = $service->translateTier2($locale, $languageName);
            $this->info("Tier 2: {$results['tier2']['completed']} completed, {$results['tier2']['errors']} errors");
        }

        if ($tier === 'all' || $tier === '3') {
            $this->info('Starting Tier 3 (service posts)...');
            $results['tier3'] = $service->translateTier3($locale, $languageName, $limit);
            $this->info("Tier 3: " . ($results['tier3']['translated'] ?? $results['tier3']['completed'] ?? 0) . " translated");
        }

        $this->info('Translation complete!');
        return 0;
    }
}
