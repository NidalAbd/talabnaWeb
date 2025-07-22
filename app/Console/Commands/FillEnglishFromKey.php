<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\TranslationLoader\LanguageLine;

class FillEnglishFromKey extends Command
{
    protected $signature = 'translations:fill-en-from-key';
    protected $description = 'Fill all empty English (en) translations in language_lines with a prettified version of the key.';

    public function handle()
    {
        $count = 0;
        LanguageLine::chunk(100, function ($lines) use (&$count) {
            foreach ($lines as $line) {
                $text = $line->text;
                if (!isset($text['en']) || empty($text['en'])) {
                    // Prettify the key: take last segment after dot, replace _ and - with space, capitalize words
                    $segments = preg_split('/[.]/', $line->key);
                    $last = end($segments);
                    $pretty = ucwords(str_replace(['_', '-'], ' ', $last));
                    $text['en'] = $pretty;
                    $line->text = $text;
                    $line->save();
                    $count++;
                }
            }
        });
        $this->info("Filled $count empty English translations with prettified key.");
        return 0;
    }
} 