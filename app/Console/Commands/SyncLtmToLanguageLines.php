<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LtmTranslation;
use Spatie\TranslationLoader\LanguageLine;

class SyncLtmToLanguageLines extends Command
{
    protected $signature = 'translations:sync-ltm-to-language-lines';
    protected $description = 'Sync all translations from ltm_translations to language_lines for Spatie translation loader.';

    public function handle()
    {
        $count = 0;
        $ltmRows = LtmTranslation::all();
        foreach ($ltmRows as $ltm) {
            // Find or create the language line for this group/key
            $line = LanguageLine::firstOrNew([
                'group' => $ltm->group,
                'key' => $ltm->key,
            ]);
            $text = $line->text ?? [];
            $text[$ltm->locale] = $ltm->value ?? '';
            $line->text = $text;
            $line->save();
            $count++;
        }
        $this->info("Synced $count translations from ltm_translations to language_lines.");
        return 0;
    }
} 