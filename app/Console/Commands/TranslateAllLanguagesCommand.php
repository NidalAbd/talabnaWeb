<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Services\AutoTranslationService;
use Illuminate\Console\Command;

/**
 * Batch wrapper around the existing AutoTranslationService: translates UI
 * strings (Tier 1) into EVERY active language in one run, instead of invoking
 * `translate:auto {locale}` once per language.
 *
 *   php artisan translate:all                 # all active languages, Tier 1 (UI strings)
 *   php artisan translate:all --tier=all      # also core content (T2) + service posts (T3)
 *   php artisan translate:all --only=fr,de,es # restrict to specific languages
 *   php artisan translate:all --limit=200     # cap Tier 3 items per language
 *
 * Source language is the DB default (Arabic). Already-translated keys are
 * skipped automatically by AutoTranslationService. Run `translations:export-bundle`
 * afterwards to refresh the app's offline bundle.
 */
class TranslateAllLanguagesCommand extends Command
{
    protected $signature = 'translate:all
                            {--tier=1 : 1 = UI strings only, all = UI + content + posts}
                            {--only= : Comma-separated language codes to limit to}
                            {--limit= : Max Tier 3 items per language}';

    protected $description = 'Auto-translate UI strings into every active language (reuses AutoTranslationService)';

    public function handle(AutoTranslationService $service): int
    {
        if (! config('services.openai.key')) {
            $this->error('OPENAI_API_KEY is not set. Add it to .env before running translations.');
            return self::FAILURE;
        }

        $tier = (string) $this->option('tier');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $default = Language::getDefault();
        $sourceCode = $default?->code ?? 'ar';

        $only = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : null;

        $languages = Language::getActiveOrdered()
            ->filter(fn ($l) => $l->code !== $sourceCode)
            ->when($only, fn ($c) => $c->filter(fn ($l) => in_array($l->code, $only, true)));

        if ($languages->isEmpty()) {
            $this->warn('No target languages to translate. Did you run AllLanguagesSeeder?');
            return self::SUCCESS;
        }

        $this->info("Source: {$sourceCode}. Targets: {$languages->count()} languages. Tier: {$tier}.");
        $this->newLine();

        $totalCompleted = 0;
        $totalErrors = 0;

        foreach ($languages as $language) {
            $this->line("→ {$language->name} ({$language->code})");

            // Tier 1 — UI strings (always run; this is what the app needs).
            $r1 = $service->translateTier1($language->code, $language->name);
            $done = $r1['completed'] ?? 0;
            $err = $r1['errors'] ?? 0;
            $totalCompleted += $done;
            $totalErrors += $err;
            $this->line("  T1 UI strings: {$done} done, {$err} errors");

            if ($tier === 'all') {
                $r2 = $service->translateTier2($language->code, $language->name);
                $this->line('  T2 content:   ' . ($r2['completed'] ?? 0) . ' done, ' . ($r2['errors'] ?? 0) . ' errors');

                $r3 = $service->translateTier3($language->code, $language->name, $limit);
                $this->line('  T3 posts:     ' . ($r3['translated'] ?? 0) . ' done, ' . ($r3['errors'] ?? 0) . ' errors');
            }
        }

        Language::clearCache();

        $this->newLine();
        $this->info("Finished. UI strings: {$totalCompleted} translated, {$totalErrors} errors.");
        $this->warn('Next: php artisan translations:export-bundle   (refresh the app offline bundle)');

        return self::SUCCESS;
    }
}
