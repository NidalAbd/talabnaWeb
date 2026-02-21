<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Console\Command;

class CopyTranslationsCommand extends Command
{
    protected $signature = 'language:copy-translations
                            {from : Source language code (e.g., ar)}
                            {to : Target language code (e.g., fr)}
                            {--overwrite : Overwrite existing translations}';

    protected $description = 'Copy translations from one language to another (for initial setup)';

    public function handle(): int
    {
        $fromCode = $this->argument('from');
        $toCode = $this->argument('to');
        $overwrite = $this->option('overwrite');

        // Validate languages exist
        $fromLang = Language::where('code', $fromCode)->first();
        $toLang = Language::where('code', $toCode)->first();

        if (!$fromLang) {
            $this->error("Source language '{$fromCode}' not found!");
            return Command::FAILURE;
        }

        if (!$toLang) {
            $this->error("Target language '{$toCode}' not found!");
            $this->info("Create it first: php artisan language:add {$toCode} \"Language Name\" \"Native Name\"");
            return Command::FAILURE;
        }

        // Get source translations
        $sourceTranslations = Translation::where('locale', $fromCode)->get();

        if ($sourceTranslations->isEmpty()) {
            $this->error("No translations found for '{$fromCode}'!");
            return Command::FAILURE;
        }

        $this->info("Copying {$sourceTranslations->count()} translations from '{$fromCode}' to '{$toCode}'...");

        $bar = $this->output->createProgressBar($sourceTranslations->count());
        $bar->start();

        $copied = 0;
        $skipped = 0;

        foreach ($sourceTranslations as $translation) {
            $exists = Translation::where('locale', $toCode)
                ->where('group', $translation->group)
                ->where('key', $translation->key)
                ->exists();

            if ($exists && !$overwrite) {
                $skipped++;
                $bar->advance();
                continue;
            }

            Translation::updateOrCreate(
                [
                    'locale' => $toCode,
                    'group' => $translation->group,
                    'key' => $translation->key,
                ],
                [
                    'value' => $translation->value, // Copy original value (needs translation later)
                ]
            );

            $copied++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Copied: {$copied} translations");
        if ($skipped > 0) {
            $this->warn("⊘ Skipped: {$skipped} existing translations (use --overwrite to replace)");
        }

        $this->newLine();
        $this->info("Note: The translations are copied with original values.");
        $this->info("You should translate them via the admin API or database.");

        return Command::SUCCESS;
    }
}
