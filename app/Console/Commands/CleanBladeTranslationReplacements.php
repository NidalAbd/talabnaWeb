<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanBladeTranslationReplacements extends Command
{
    protected $signature = 'translations:clean-blade-replacements';
    protected $description = 'Clean up incorrect translation replacements in Blade files (e.g., variables wrapped in translation helpers)';

    public function handle()
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $fixCount = 0;
        foreach ($bladeFiles as $file) {
            $path = $file->getRealPath();
            $contents = File::get($path);
            $original = $contents;

            // Regex: Find {{ $variable->{{ __('group.key') }} }} and similar patterns
            // Replace with {{ $variable->field }} or highlight for manual review
            $pattern = '/\{\{\s*\$([a-zA-Z0-9_\->]+)\s*\{\{\s*__\([^)]+\)\s*\}\}\s*\}\}/';
            $contents = preg_replace_callback($pattern, function ($matches) {
                // Attempt to extract the variable part
                $var = $matches[1];
                // Fallback: just output the variable
                return "{{ ".$var." }}";
            }, $contents, -1, $count1);

            // Also fix cases like $object->{{ __('group.key') }} (inside Blade echo)
            $pattern2 = '/\$([a-zA-Z0-9_]+)->\{\{\s*__\([^)]+\)\s*\}\}/';
            $contents = preg_replace_callback($pattern2, function ($matches) {
                return '$'.$matches[1].'->field'; // You may want to manually review these
            }, $contents, -1, $count2);

            if ($contents !== $original) {
                File::put($path, $contents);
                $this->info("Cleaned: {$file->getRelativePathname()} (".($count1+$count2)." fixes)");
                $fixCount += ($count1+$count2);
            }
        }
        $this->info("Blade cleanup complete. $fixCount fixes made. Please manually review any remaining suspicious lines.");
    }
} 