<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\TranslationLoader\LanguageLine;
use App\Models\LtmTranslation;

class FillEmptyEnglishTranslations extends Command
{
    protected $signature = 'translations:fill-empty-en';
    protected $description = 'Fill all empty English (en) translations in language_lines table with their key name.';

    public function handle()
    {
        $count = 0;
        LanguageLine::chunk(100, function ($lines) use (&$count) {
            foreach ($lines as $line) {
                $text = $line->text;
                if (!isset($text['en']) || empty($text['en'])) {
                    // Try to get value from ltm_translations
                    $ltm = LtmTranslation::where('group', $line->group)
                        ->where('key', $line->key)
                        ->where('locale', 'en')
                        ->first();
                    if ($ltm && !empty($ltm->value)) {
                        $text['en'] = $ltm->value;
                    } else {
                        $text['en'] = $line->key;
                    }
                    $line->text = $text;
                    $line->save();
                    $count++;
                }
            }
        });
        $this->info("Filled $count empty English translations with their key name.");
        return 0;
    }
} 