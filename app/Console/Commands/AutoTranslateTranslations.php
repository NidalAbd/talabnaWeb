<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\TranslationLoader\LanguageLine;
use Illuminate\Support\Facades\Http;

class AutoTranslateTranslations extends Command
{
    protected $signature = 'translations:auto-translate {lang}';
    protected $description = 'Auto-translate all missing translations for a given language using Google Translate API.';

    // Set your Google Translate API key here
    protected $apiKey = 'YOUR_GOOGLE_TRANSLATE_API_KEY';

    public function handle()
    {
        $lang = $this->argument('lang');
        $lines = LanguageLine::all();
        $count = 0;
        foreach ($lines as $line) {
            $text = $line->text;
            if (empty($text[$lang]) && !empty($text['en'])) {
                $translated = $this->translate($text['en'], 'en', $lang);
                if ($translated) {
                    $text[$lang] = $translated;
                    $line->text = $text;
                    $line->save();
                    $this->info("[{$line->group}.{$line->key}] => $translated");
                    $count++;
                }
            }
        }
        $this->info("Auto-translation complete. $count translations added for '$lang'.");
    }

    protected function translate($text, $source, $target)
    {
        $response = Http::post('https://translation.googleapis.com/language/translate/v2', [
            'q' => $text,
            'source' => $source,
            'target' => $target,
            'format' => 'text',
            'key' => $this->apiKey,
        ]);
        if ($response->ok() && isset($response['data']['translations'][0]['translatedText'])) {
            return $response['data']['translations'][0]['translatedText'];
        }
        return null;
    }
} 