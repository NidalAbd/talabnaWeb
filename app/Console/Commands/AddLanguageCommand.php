<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;

class AddLanguageCommand extends Command
{
    protected $signature = 'language:add
                            {code : Language code (e.g., fr, de, es)}
                            {name : Language name in English (e.g., French)}
                            {native : Native name (e.g., Français)}
                            {--direction=ltr : Text direction (ltr or rtl)}
                            {--default : Set as default language}';

    protected $description = 'Add a new language to the system';

    // Common languages for quick reference
    private array $commonLanguages = [
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
        'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'direction' => 'rtl'],
        'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'direction' => 'rtl'],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'direction' => 'rtl'],
    ];

    public function handle(): int
    {
        $code = strtolower($this->argument('code'));
        $name = $this->argument('name');
        $native = $this->argument('native');
        $direction = $this->option('direction');
        $isDefault = $this->option('default');

        // Check if language already exists
        if (Language::where('code', $code)->exists()) {
            $this->error("Language with code '{$code}' already exists!");
            return Command::FAILURE;
        }

        // Validate direction
        if (!in_array($direction, ['ltr', 'rtl'])) {
            $this->error("Direction must be 'ltr' or 'rtl'");
            return Command::FAILURE;
        }

        // Get next sort order
        $sortOrder = Language::max('sort_order') + 1;

        // If setting as default, unset other defaults
        if ($isDefault) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        // Create the language
        $language = Language::create([
            'code' => $code,
            'name' => $name,
            'native_name' => $native,
            'direction' => $direction,
            'is_active' => true,
            'is_default' => $isDefault,
            'sort_order' => $sortOrder,
        ]);

        $this->info("✓ Language '{$name}' ({$code}) added successfully!");
        $this->table(
            ['ID', 'Code', 'Name', 'Native', 'Direction', 'Default'],
            [[$language->id, $language->code, $language->name, $language->native_name, $language->direction, $language->is_default ? 'Yes' : 'No']]
        );

        $this->newLine();
        $this->info("Next step: Add translations using:");
        $this->line("  php artisan language:copy-translations ar {$code}");

        return Command::SUCCESS;
    }
}
