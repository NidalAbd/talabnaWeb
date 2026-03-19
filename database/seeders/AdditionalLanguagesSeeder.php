<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class AdditionalLanguagesSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 4],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 5],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 6],
            ['code' => 'ur', 'name' => 'Urdu', 'native_name' => 'اردو', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 7],
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 8],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 9],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 10],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 11],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 12],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 13],
            ['code' => 'ku', 'name' => 'Kurdish', 'native_name' => 'کوردی', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 14],
            ['code' => 'fa', 'name' => 'Persian', 'native_name' => 'فارسی', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 15],
            ['code' => 'sw', 'name' => 'Swahili', 'native_name' => 'Kiswahili', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 16],
            ['code' => 'ms', 'name' => 'Malay', 'native_name' => 'Bahasa Melayu', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 17],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($languages as $lang) {
            if (Language::where('code', $lang['code'])->exists()) {
                $skipped++;
                continue;
            }
            Language::create($lang);
            $created++;
        }

        echo "{$created} languages created, {$skipped} skipped.\n";
    }
}
