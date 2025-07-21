<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\TranslationLoader\LanguageLine;

class ScanBladeTranslations extends Command
{
    protected $signature = 'translations:scan-and-insert';
    protected $description = 'Scan Blade files for hardcoded text and insert as translation keys in the database (en/ar)';

    public function handle()
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $count = 0;
        foreach ($bladeFiles as $file) {
            $contents = File::get($file->getRealPath());
            // Find hardcoded text between HTML tags (improve regex as needed)
            preg_match_all('/>([^<\n\r\{\}@][^<]*)</', $contents, $matches);
            foreach ($matches[1] as $text) {
                $clean = trim($text);
                if ($clean && !preg_match('/{{|@lang|__/', $clean)) {
                    // Generate group and key
                    $group = str_replace(['/', '.blade.php'], ['_', ''], $file->getRelativePathname());
                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', substr($clean, 0, 40)));
                    // Insert if not exists
                    $exists = LanguageLine::where('group', $group)->where('key', $key)->exists();
                    if (!$exists) {
                        LanguageLine::create([
                            'group' => $group,
                            'key' => $key,
                            'text' => [
                                'en' => $clean,
                                'ar' => '',
                            ],
                        ]);
                        $this->info("Inserted: [$group.$key] => $clean");
                        $count++;
                    }
                }
            }
        }
        $this->info("Scan complete. $count new translations inserted.");
    }
} 