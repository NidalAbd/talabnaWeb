<?php

namespace Database\Seeders;

use App\Models\countries;
use App\Models\Photos;
use Carbon\Carbon;
use Google\Service\Dfareporting\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['id' => '1', 'name' => 'فلسطين', 'country_code' => '00970', 'src' => 'storage/countryFlag/palestine.png'],
            ['id' => '2', 'name' => 'مصر', 'country_code' => '0020', 'src' => 'storage/countryFlag/egypt.png'],
            ['id' => '3', 'name' => 'السعودية', 'country_code' => '00966', 'src' => 'storage/countryFlag/saudi-arabia.png'],
            ['id' => '4', 'name' => 'الإمارات', 'country_code' => '00971', 'src' => 'storage/countryFlag/uae.png'],
            ['id' => '5', 'name' => 'العراق', 'country_code' => '00964', 'src' => 'storage/countryFlag/iraq.png'],
            ['id' => '6', 'name' => 'الأردن', 'country_code' => '00962', 'src' => 'storage/countryFlag/jordan.png'],
            ['id' => '7', 'name' => 'البحرين', 'country_code' => '00973', 'src' => 'storage/countryFlag/bahrain.png'],
            ['id' => '8', 'name' => 'اليمن', 'country_code' => '00967', 'src' => 'storage/countryFlag/yemen.png'],
            ['id' => '9', 'name' => 'قطر', 'country_code' => '00974', 'src' => 'storage/countryFlag/qatar.png'],
            ['id' => '10', 'name' => 'الجزائر', 'country_code' => '00213', 'src' => 'storage/countryFlag/algeria.png'],
            ['id' => '11', 'name' => 'المغرب', 'country_code' => '00212', 'src' => 'storage/countryFlag/morocco.png'],
            ['id' => '12', 'name' => 'تونس', 'country_code' => '00216', 'src' => 'storage/countryFlag/tunisia.png'],
            ['id' => '13', 'name' => 'ليبيا', 'country_code' => '00218', 'src' => 'storage/countryFlag/libya.png'],
            ['id' => '14', 'name' => 'سوريا', 'country_code' => '00963', 'src' => 'storage/countryFlag/syria.png'],
            ['id' => '15', 'name' => 'السودان', 'country_code' => '00249', 'src' => 'storage/countryFlag/sudan.png'],
            ['id' => '16', 'name' => 'الكويت', 'country_code' => '00965', 'src' => 'storage/countryFlag/kuwait.png'],
            ['id' => '17', 'name' => 'لبنان', 'country_code' => '00961', 'src' => 'storage/countryFlag/lebanon.png'],
            ['id' => '18', 'name' => 'عمان', 'country_code' => '00968', 'src' => 'storage/countryFlag/oman.png'],
            ['id' => '19', 'name' => 'موريتانيا', 'country_code' => '00222', 'src' => 'storage/countryFlag/mauritania.png'],
            ['id' => '20', 'name' => 'الصومال', 'country_code' => '00252', 'src' => 'storage/countryFlag/somalia.png'],
        ];
        foreach ($countries as $country) {
            $countryData = countries::create([
                'name' => $country['name'],
                'country_code' => $country['country_code'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            // Create a new Photo model for the flag image
            $photo = new Photos([
                'src' => $country['src'],
            ]);
            $countryData->photos()->save($photo);
        }
    }
}
