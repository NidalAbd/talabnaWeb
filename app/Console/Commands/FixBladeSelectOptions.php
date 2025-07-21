<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBladeSelectOptions extends Command
{
    protected $signature = 'translations:fix-select-options';
    protected $description = 'Fix select option lines in Blade files where selected attribute is incorrectly wrapped with a translation helper.';

    public function handle()
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $fixCount = 0;
        foreach ($bladeFiles as $file) {
            $path = $file->getRealPath();
            $contents = File::get($path);
            $original = $contents;

            // Fix language select options (en/ar)
            $patterns = [
                // English option
                '/<option value="en"\s*\{\{[^}]*__\([^)]+getlocale_en_selected_[^)]*\)[^}]*\}\}\s*>/i' => '<option value="en" {{ app()->getLocale() == \"en\" ? \"selected\" : \"\" }}>',
                // Arabic option
                '/<option value="ar"\s*\{\{[^}]*__\([^)]+getlocale_ar_selected_[^)]*\)[^}]*\}\}\s*>/i' => '<option value="ar" {{ app()->getLocale() == \"ar\" ? \"selected\" : \"\" }}>',
            ];
            foreach ($patterns as $pattern => $replace) {
                $contents = preg_replace($pattern, $replace, $contents, -1, $count);
                $fixCount += $count;
            }

            if ($contents !== $original) {
                File::put($path, $contents);
                $this->info("Fixed: {$file->getRelativePathname()}");
            }
        }
        $this->info("Select option fix complete. $fixCount fixes made.");
    }
} 