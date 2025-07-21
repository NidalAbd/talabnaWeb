<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\TranslationLoader\LanguageLine;

class ReplaceBladeTextWithTranslations extends Command
{
    protected $signature = 'translations:auto-replace-blades';
    protected $description = 'Auto-replace hardcoded text in Blade files with translation helpers using database keys';

    public function handle()
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $replacedCount = 0;
        foreach ($bladeFiles as $file) {
            $path = $file->getRealPath();
            $contents = File::get($path);
            $group = str_replace(['/', '.blade.php'], ['_', ''], $file->getRelativePathname());
            // Find hardcoded text between HTML tags (improve regex as needed)
            preg_match_all('/>([^<\n\r\{\}@][^<]*)</', $contents, $matches);
            $replacements = [];
            foreach ($matches[1] as $text) {
                $clean = trim($text);
                if ($clean && !preg_match('/{{|@lang|__/', $clean)) {
                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', substr($clean, 0, 40)));
                    $exists = LanguageLine::where('group', $group)->where('key', $key)->exists();
                    if ($exists) {
                        $replacements[$clean] = "{{ __('$group.$key') }}";
                    }
                }
            }
            if (!empty($replacements)) {
                // Replace only outside of Blade echo/translation helpers
                foreach ($replacements as $find => $replace) {
                    // Replace >Text< with >{{ __('group.key') }}<
                    $contents = preg_replace('/>' . preg_quote($find, '/') . '</', '>' . $replace . '<', $contents);
                }
                File::put($path, $contents);
                $this->info("Updated: {$file->getRelativePathname()} (" . count($replacements) . " replacements)");
                $replacedCount += count($replacements);
            }
        }
        $this->info("Auto-replacement complete. $replacedCount replacements made.");
    }
} 