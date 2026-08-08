<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Exports every active language's translations to JSON files that the Flutter
 * app bundles as an OFFLINE fallback (assets/translations/<locale>.json), plus
 * an index file. Each file matches the /api/translations/{locale} response shape
 * so the app can decode it with the same TranslationData model.
 *
 *   php artisan translations:export-bundle
 *   php artisan translations:export-bundle --path=/abs/path/to/assets/translations
 *
 * Default output: ../talabna/assets/translations  (sibling Flutter project).
 * (Named *-bundle to avoid clashing with barryvdh/laravel-translation-manager's
 *  `translations:export {group}` command.)
 */
class ExportTranslationsCommand extends Command
{
    protected $signature = 'translations:export-bundle
                            {--path= : Output directory (defaults to the sibling Flutter app assets)}';

    protected $description = 'Export DB translations to JSON assets for the app offline bundle';

    /**
     * Regional variants declared in android .../res/xml/locale_config.xml that
     * are linguistically identical to a base language. We emit an alias JSON
     * file for each (a copy of the base) so the offline bundle matches every
     * locale Android can hand the app 1:1. They are NOT added to _index.json,
     * so the in-app language picker still shows one entry per real language.
     *
     * base code => [variant codes]
     */
    private array $regionalVariants = [
        'en' => ['en-AU', 'en-CA', 'en-GB', 'en-IN', 'en-SG', 'en-ZA'],
        'es' => ['es-MX', 'es-US'],
        'fr' => ['fr-CA'],
        'ms' => ['ms-MY'],
        'pt' => ['pt-BR', 'pt-PT'],
        'zh' => ['zh-CN', 'zh-HK', 'zh-TW'],
    ];

    public function handle(): int
    {
        $dir = $this->option('path') ?: base_path('../talabna/assets/translations');

        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $languages = Language::getActiveOrdered();
        $index = [];
        $payloads = [];
        $total = 0;

        foreach ($languages as $language) {
            $translations = Translation::getTranslationsForLocale($language->code);

            $payload = [
                'locale'       => $language->code,
                'version'      => $language->getTranslationsVersionHash(),
                'direction'    => $language->direction,
                'name'         => $language->name,
                'native_name'  => $language->native_name,
                'translations' => $translations,
            ];

            $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $language->code . '.json';
            File::put($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $payloads[$language->code] = $payload;

            $index[] = [
                'code'        => $language->code,
                'name'        => $language->name,
                'native_name' => $language->native_name,
                'direction'   => $language->direction,
                'is_default'  => $language->is_default,
                'count'       => count($translations),
                'version'     => $payload['version'],
            ];

            $total += count($translations);
            $this->line("  ✓ {$language->code}: " . count($translations) . ' strings');
        }

        // Emit regional-variant alias files (copies of their base) so the bundle
        // matches every locale in locale_config.xml. Not added to _index.
        $variantCount = 0;
        foreach ($this->regionalVariants as $base => $variants) {
            if (! isset($payloads[$base])) {
                continue; // base language not active — skip its variants
            }
            foreach ($variants as $variant) {
                $payload = $payloads[$base];
                $payload['locale'] = $variant; // keep base content, relabel locale
                $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $variant . '.json';
                File::put($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                $variantCount++;
                $this->line("  ✓ {$variant}: alias of {$base}");
            }
        }

        // Index file lists the bundled languages (used as the offline language list).
        File::put(
            rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '_index.json',
            json_encode(['languages' => $index], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        $this->newLine();
        $this->info('Exported ' . $languages->count() . " languages + {$variantCount} regional variants ({$total} strings) to: {$dir}");
        $this->warn('If new languages were added, run `flutter pub get` and rebuild the app to bundle them.');

        return self::SUCCESS;
    }
}
