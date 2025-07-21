<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBladeOptionSyntax extends Command
{
    protected $signature = 'translations:fix-option-syntax';
    protected $description = 'Fix select option lines in Blade files with broken or incomplete Blade expressions.';

    public function handle()
    {
        $bladeFiles = File::allFiles(resource_path('views'));
        $fixCount = 0;
        foreach ($bladeFiles as $file) {
            $path = $file->getRealPath();
            $contents = File::get($path);
            $original = $contents;

            // Fix static Arabic/English offer/request options
            $patterns = [
                // Broken Arabic Offer
                '/<option value="عرض"\s*\{\{\s*old\(\'type\',?\s*\$servicePost->field.*?<\/option>/s' => "<option value='عرض' {{ old('type', isset( [1m$servicePost->type [0m) ?  [1m$servicePost->type [0m : '') == 'عرض' ? 'selected' : '' }}>{{ __('service_posts.edit.offer') }}</option>",
                // Broken Arabic Request
                '/<option value="طلب"\s*\{\{\s*old\(\'type\',?\s*\$servicePost->field.*?<\/option>/s' => "<option value='طلب' {{ old('type', isset( [1m$servicePost->type [0m) ?  [1m$servicePost->type [0m : '') == 'طلب' ? 'selected' : '' }}>{{ __('service_posts.edit.request') }}</option>",
                // Broken USD option
                '/<option value="USD"\s*\{\{\s*old\(\'price_currency_code\'.*?<\/option>/s' => "<option value='USD' {{ old('price_currency_code', isset( [1m$servicePost->price_currency_code [0m) ?  [1m$servicePost->price_currency_code [0m : '') == 'USD' ? 'selected' : '' }}>{{ __('service_posts.edit.usd') }}</option>",
            ];
            foreach ($patterns as $pattern => $replace) {
                $contents = preg_replace($pattern, $replace, $contents, -1, $count);
                $fixCount += $count;
            }

            // Fix generic broken option tags with old() or variable not closed
            $contents = preg_replace_callback('/<option([^>]*)\{\{\s*old\(([^)]*)\).*?(?=<\/option>)/s', function ($matches) {
                // Try to extract the value and variable
                $attrs = $matches[1];
                $oldArgs = $matches[2];
                // Guess the variable name
                if (preg_match("/'([a-zA-Z0-9_]+)'/", $oldArgs, $m)) {
                    $field = $m[1];
                } else {
                    $field = 'value';
                }
                return "<option$attrs {{ old('$field') == '$field' ? 'selected' : '' }}";
            }, $contents, -1, $count2);
            $fixCount += $count2;

            if ($contents !== $original) {
                File::put($path, $contents);
                $this->info("Fixed: {$file->getRelativePathname()}");
            }
        }
        $this->info("Option tag syntax fix complete. $fixCount fixes made. Please manually review any remaining complex cases.");
    }
} 