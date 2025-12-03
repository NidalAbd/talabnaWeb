<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArabCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Adds missing Arab countries and their major cities.
     * Safe to run multiple times - uses insertOrIgnore.
     */
    public function run(): void
    {
        // Missing Arab Countries
        $missingCountries = [
            [
                'name' => json_encode(['ar' => 'جيبوتي', 'en' => 'Djibouti']),
                'country_code' => 'DJ',
                'currency_code' => 'DJF',
                'currency_name' => json_encode(['ar' => 'فرنك جيبوتي', 'en' => 'Djiboutian Franc']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'جزر القمر', 'en' => 'Comoros']),
                'country_code' => 'KM',
                'currency_code' => 'KMF',
                'currency_name' => json_encode(['ar' => 'فرنك قمري', 'en' => 'Comorian Franc']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($missingCountries as $country) {
            // Check if country already exists by country_code
            $exists = DB::table('countries')
                ->where('country_code', $country['country_code'])
                ->exists();

            if (!$exists) {
                DB::table('countries')->insert($country);
            }
        }

        // Get the IDs of newly added countries
        $djiboutiId = DB::table('countries')->where('country_code', 'DJ')->value('id');
        $comorosId = DB::table('countries')->where('country_code', 'KM')->value('id');

        $this->command->info('Added missing countries: Djibouti (ID: ' . $djiboutiId . '), Comoros (ID: ' . $comorosId . ')');

        // Cities for Djibouti (Country ID: 21)
        $djiboutiCities = [
            ['ar' => 'جيبوتي', 'en' => 'Djibouti City'],
            ['ar' => 'علي صبيح', 'en' => 'Ali Sabieh'],
            ['ar' => 'تاجورة', 'en' => 'Tadjoura'],
            ['ar' => 'أوبوك', 'en' => 'Obock'],
            ['ar' => 'دخيل', 'en' => 'Dikhil'],
            ['ar' => 'آرتا', 'en' => 'Arta'],
        ];

        // Cities for Comoros (Country ID: 22)
        $comorosCities = [
            ['ar' => 'موروني', 'en' => 'Moroni'],
            ['ar' => 'موتسامودو', 'en' => 'Mutsamudu'],
            ['ar' => 'فومبوني', 'en' => 'Fomboni'],
            ['ar' => 'دوموني', 'en' => 'Domoni'],
            ['ar' => 'ميتساميولي', 'en' => 'Mitsamiouli'],
            ['ar' => 'مبيني', 'en' => 'Mbeni'],
        ];

        // Additional cities for UAE (Country ID: 4) - currently only has 8
        $uaeCities = [
            ['ar' => 'العين', 'en' => 'Al Ain'],
            ['ar' => 'خورفكان', 'en' => 'Khor Fakkan'],
            ['ar' => 'دبا الفجيرة', 'en' => 'Dibba Al-Fujairah'],
            ['ar' => 'مدينة زايد', 'en' => 'Madinat Zayed'],
            ['ar' => 'الرويس', 'en' => 'Ruwais'],
            ['ar' => 'ليوا', 'en' => 'Liwa'],
            ['ar' => 'جبل علي', 'en' => 'Jebel Ali'],
            ['ar' => 'مصفح', 'en' => 'Musaffah'],
        ];

        // Additional cities for Saudi Arabia (Country ID: 3)
        $saudiCities = [
            ['ar' => 'أبها', 'en' => 'Abha'],
            ['ar' => 'نجران', 'en' => 'Najran'],
            ['ar' => 'جازان', 'en' => 'Jazan'],
            ['ar' => 'حائل', 'en' => 'Hail'],
            ['ar' => 'تبوك', 'en' => 'Tabuk'],
            ['ar' => 'الجبيل', 'en' => 'Jubail'],
            ['ar' => 'ينبع', 'en' => 'Yanbu'],
            ['ar' => 'الأحساء', 'en' => 'Al-Ahsa'],
            ['ar' => 'القطيف', 'en' => 'Qatif'],
            ['ar' => 'الخرج', 'en' => 'Al-Kharj'],
            ['ar' => 'سكاكا', 'en' => 'Sakaka'],
            ['ar' => 'عرعر', 'en' => 'Arar'],
            ['ar' => 'الباحة', 'en' => 'Al Bahah'],
        ];

        // Additional cities for Jordan (Country ID: 6)
        $jordanCities = [
            ['ar' => 'الزرقاء', 'en' => 'Zarqa'],
            ['ar' => 'السلط', 'en' => 'Salt'],
            ['ar' => 'المفرق', 'en' => 'Mafraq'],
            ['ar' => 'جرش', 'en' => 'Jerash'],
            ['ar' => 'عجلون', 'en' => 'Ajloun'],
            ['ar' => 'الكرك', 'en' => 'Karak'],
            ['ar' => 'معان', 'en' => 'Maan'],
            ['ar' => 'الطفيلة', 'en' => 'Tafilah'],
            ['ar' => 'مادبا', 'en' => 'Madaba'],
        ];

        // Insert cities using dynamic IDs
        if ($djiboutiId) {
            $this->insertCities($djiboutiId, $djiboutiCities);
        }
        if ($comorosId) {
            $this->insertCities($comorosId, $comorosCities);
        }
        $this->insertCities(4, $uaeCities);
        $this->insertCities(3, $saudiCities);
        $this->insertCities(6, $jordanCities);

        $this->command->info('Added cities for Djibouti, Comoros, UAE, Saudi Arabia, Jordan');
    }

    /**
     * Insert cities for a country (skips duplicates)
     */
    private function insertCities(int $countryId, array $cities): void
    {
        foreach ($cities as $city) {
            // Check if city already exists by English name
            $exists = DB::table('cities')
                ->where('country_id', $countryId)
                ->where('name', 'LIKE', '%"en":"' . $city['en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('cities')->insert([
                    'country_id' => $countryId,
                    'name' => json_encode($city),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
