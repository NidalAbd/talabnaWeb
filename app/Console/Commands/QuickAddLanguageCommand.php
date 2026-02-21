<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Console\Command;

class QuickAddLanguageCommand extends Command
{
    protected $signature = 'language:quick
                            {code : Language code (e.g., fr, de, es)}
                            {--with-translations : Copy translations from default language}';

    protected $description = 'Quickly add a common language with predefined settings';

    // Common languages database
    private array $languages = [
        'fr' => ['name' => 'French', 'native' => 'Français', 'direction' => 'ltr'],
        'de' => ['name' => 'German', 'native' => 'Deutsch', 'direction' => 'ltr'],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'direction' => 'ltr'],
        'it' => ['name' => 'Italian', 'native' => 'Italiano', 'direction' => 'ltr'],
        'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'direction' => 'ltr'],
        'ru' => ['name' => 'Russian', 'native' => 'Русский', 'direction' => 'ltr'],
        'zh' => ['name' => 'Chinese', 'native' => '中文', 'direction' => 'ltr'],
        'ja' => ['name' => 'Japanese', 'native' => '日本語', 'direction' => 'ltr'],
        'ko' => ['name' => 'Korean', 'native' => '한국어', 'direction' => 'ltr'],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'direction' => 'ltr'],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'direction' => 'ltr'],
        'nl' => ['name' => 'Dutch', 'native' => 'Nederlands', 'direction' => 'ltr'],
        'pl' => ['name' => 'Polish', 'native' => 'Polski', 'direction' => 'ltr'],
        'sv' => ['name' => 'Swedish', 'native' => 'Svenska', 'direction' => 'ltr'],
        'da' => ['name' => 'Danish', 'native' => 'Dansk', 'direction' => 'ltr'],
        'no' => ['name' => 'Norwegian', 'native' => 'Norsk', 'direction' => 'ltr'],
        'fi' => ['name' => 'Finnish', 'native' => 'Suomi', 'direction' => 'ltr'],
        'el' => ['name' => 'Greek', 'native' => 'Ελληνικά', 'direction' => 'ltr'],
        'cs' => ['name' => 'Czech', 'native' => 'Čeština', 'direction' => 'ltr'],
        'ro' => ['name' => 'Romanian', 'native' => 'Română', 'direction' => 'ltr'],
        'hu' => ['name' => 'Hungarian', 'native' => 'Magyar', 'direction' => 'ltr'],
        'th' => ['name' => 'Thai', 'native' => 'ไทย', 'direction' => 'ltr'],
        'vi' => ['name' => 'Vietnamese', 'native' => 'Tiếng Việt', 'direction' => 'ltr'],
        'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'direction' => 'ltr'],
        'ms' => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'direction' => 'ltr'],
        'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'direction' => 'rtl'],
        'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'direction' => 'rtl'],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'direction' => 'rtl'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'direction' => 'rtl'],
        'en' => ['name' => 'English', 'native' => 'English', 'direction' => 'ltr'],
    ];

    public function handle(): int
    {
        $code = strtolower($this->argument('code'));
        $withTranslations = $this->option('with-translations');

        // Check if language code is known
        if (!isset($this->languages[$code])) {
            $this->error("Unknown language code: {$code}");
            $this->newLine();
            $this->info("Available language codes:");
            $this->table(
                ['Code', 'Name', 'Native', 'Direction'],
                collect($this->languages)->map(fn($l, $c) => [$c, $l['name'], $l['native'], $l['direction']])->toArray()
            );
            return Command::FAILURE;
        }

        // Check if already exists
        if (Language::where('code', $code)->exists()) {
            $this->error("Language '{$code}' already exists!");
            return Command::FAILURE;
        }

        $langData = $this->languages[$code];
        $sortOrder = Language::max('sort_order') + 1;

        // Create language
        $language = Language::create([
            'code' => $code,
            'name' => $langData['name'],
            'native_name' => $langData['native'],
            'direction' => $langData['direction'],
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $sortOrder,
        ]);

        $this->info("✓ Language '{$langData['name']}' ({$code}) added!");

        // Copy translations if requested
        if ($withTranslations) {
            $defaultLang = Language::where('is_default', true)->first();
            if ($defaultLang) {
                $this->copyTranslations($defaultLang->code, $code);
            }
        }

        $this->newLine();
        $this->table(
            ['ID', 'Code', 'Name', 'Native', 'Direction', 'Translations'],
            [[
                $language->id,
                $language->code,
                $language->name,
                $language->native_name,
                $language->direction,
                Translation::where('locale', $code)->count()
            ]]
        );

        return Command::SUCCESS;
    }

    private function copyTranslations(string $from, string $to): void
    {
        $translations = Translation::where('locale', $from)->get();

        $this->info("Copying {$translations->count()} translations from '{$from}'...");

        foreach ($translations as $translation) {
            Translation::create([
                'locale' => $to,
                'group' => $translation->group,
                'key' => $translation->key,
                'value' => $translation->value,
            ]);
        }

        $this->info("✓ Translations copied (need translation to {$to})");
    }
}
