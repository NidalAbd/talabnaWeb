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
            [
                'id' => '1',
                'name' => ['ar' => 'فلسطين', 'en' => 'Palestine'],
                'country_code' => '00970',
                'currency_code' => 'ILS',
                'currency_name' => ['ar' => 'شيكل', 'en' => 'Shekel'],
                'src' => 'storage/countryFlag/palestine.png'
            ],
            [
                'id' => '2',
                'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
                'country_code' => '0020',
                'currency_code' => 'EGP',
                'currency_name' => ['ar' => 'جنيه مصري', 'en' => 'Egyptian Pound'],
                'src' => 'storage/countryFlag/egypt.png'
            ],
            [
                'id' => '3',
                'name' => ['ar' => 'السعودية', 'en' => 'Saudi Arabia'],
                'country_code' => '00966',
                'currency_code' => 'SAR',
                'currency_name' => ['ar' => 'ريال سعودي', 'en' => 'Saudi Riyal'],
                'src' => 'storage/countryFlag/saudi-arabia.png'
            ],
            [
                'id' => '4',
                'name' => ['ar' => 'الإمارات', 'en' => 'UAE'],
                'country_code' => '00971',
                'currency_code' => 'AED',
                'currency_name' => ['ar' => 'درهم إماراتي', 'en' => 'UAE Dirham'],
                'src' => 'storage/countryFlag/uae.png'
            ],
            [
                'id' => '5',
                'name' => ['ar' => 'العراق', 'en' => 'Iraq'],
                'country_code' => '00964',
                'currency_code' => 'IQD',
                'currency_name' => ['ar' => 'دينار عراقي', 'en' => 'Iraqi Dinar'],
                'src' => 'storage/countryFlag/iraq.png'
            ],
            [
                'id' => '6',
                'name' => ['ar' => 'الأردن', 'en' => 'Jordan'],
                'country_code' => '00962',
                'currency_code' => 'JOD',
                'currency_name' => ['ar' => 'دينار أردني', 'en' => 'Jordanian Dinar'],
                'src' => 'storage/countryFlag/jordan.png'
            ],
            [
                'id' => '7',
                'name' => ['ar' => 'البحرين', 'en' => 'Bahrain'],
                'country_code' => '00973',
                'currency_code' => 'BHD',
                'currency_name' => ['ar' => 'دينار بحريني', 'en' => 'Bahraini Dinar'],
                'src' => 'storage/countryFlag/bahrain.png'
            ],
            [
                'id' => '8',
                'name' => ['ar' => 'اليمن', 'en' => 'Yemen'],
                'country_code' => '00967',
                'currency_code' => 'YER',
                'currency_name' => ['ar' => 'ريال يمني', 'en' => 'Yemeni Rial'],
                'src' => 'storage/countryFlag/yemen.png'
            ],
            [
                'id' => '9',
                'name' => ['ar' => 'قطر', 'en' => 'Qatar'],
                'country_code' => '00974',
                'currency_code' => 'QAR',
                'currency_name' => ['ar' => 'ريال قطري', 'en' => 'Qatari Riyal'],
                'src' => 'storage/countryFlag/qatar.png'
            ],
            [
                'id' => '10',
                'name' => ['ar' => 'الجزائر', 'en' => 'Algeria'],
                'country_code' => '00213',
                'currency_code' => 'DZD',
                'currency_name' => ['ar' => 'دينار جزائري', 'en' => 'Algerian Dinar'],
                'src' => 'storage/countryFlag/algeria.png'
            ],
            [
                'id' => '11',
                'name' => ['ar' => 'المغرب', 'en' => 'Morocco'],
                'country_code' => '00212',
                'currency_code' => 'MAD',
                'currency_name' => ['ar' => 'درهم مغربي', 'en' => 'Moroccan Dirham'],
                'src' => 'storage/countryFlag/morocco.png'
            ],
            [
                'id' => '12',
                'name' => ['ar' => 'تونس', 'en' => 'Tunisia'],
                'country_code' => '00216',
                'currency_code' => 'TND',
                'currency_name' => ['ar' => 'دينار تونسي', 'en' => 'Tunisian Dinar'],
                'src' => 'storage/countryFlag/tunisia.png'
            ],
            [
                'id' => '13',
                'name' => ['ar' => 'ليبيا', 'en' => 'Libya'],
                'country_code' => '00218',
                'currency_code' => 'LYD',
                'currency_name' => ['ar' => 'دينار ليبي', 'en' => 'Libyan Dinar'],
                'src' => 'storage/countryFlag/libya.png'
            ],
            [
                'id' => '14',
                'name' => ['ar' => 'سوريا', 'en' => 'Syria'],
                'country_code' => '00963',
                'currency_code' => 'SYP',
                'currency_name' => ['ar' => 'ليرة سورية', 'en' => 'Syrian Pound'],
                'src' => 'storage/countryFlag/syria.png'
            ],
            [
                'id' => '15',
                'name' => ['ar' => 'السودان', 'en' => 'Sudan'],
                'country_code' => '00249',
                'currency_code' => 'SDG',
                'currency_name' => ['ar' => 'جنيه سوداني', 'en' => 'Sudanese Pound'],
                'src' => 'storage/countryFlag/sudan.png'
            ],
            [
                'id' => '16',
                'name' => ['ar' => 'الكويت', 'en' => 'Kuwait'],
                'country_code' => '00965',
                'currency_code' => 'KWD',
                'currency_name' => ['ar' => 'دينار كويتي', 'en' => 'Kuwaiti Dinar'],
                'src' => 'storage/countryFlag/kuwait.png'
            ],
            [
                'id' => '17',
                'name' => ['ar' => 'لبنان', 'en' => 'Lebanon'],
                'country_code' => '00961',
                'currency_code' => 'LBP',
                'currency_name' => ['ar' => 'ليرة لبنانية', 'en' => 'Lebanese Pound'],
                'src' => 'storage/countryFlag/lebanon.png'
            ],
            [
                'id' => '18',
                'name' => ['ar' => 'عمان', 'en' => 'Oman'],
                'country_code' => '00968',
                'currency_code' => 'OMR',
                'currency_name' => ['ar' => 'ريال عماني', 'en' => 'Omani Rial'],
                'src' => 'storage/countryFlag/oman.png'
            ],
            [
                'id' => '19',
                'name' => ['ar' => 'موريتانيا', 'en' => 'Mauritania'],
                'country_code' => '00222',
                'currency_code' => 'MRU',
                'currency_name' => ['ar' => 'أوقية موريتانية', 'en' => 'Mauritanian Ouguiya'],
                'src' => 'storage/countryFlag/mauritania.png'
            ],
            [
                'id' => '20',
                'name' => ['ar' => 'الصومال', 'en' => 'Somalia'],
                'country_code' => '00252',
                'currency_code' => 'SOS',
                'currency_name' => ['ar' => 'شلن صومالي', 'en' => 'Somali Shilling'],
                'src' => 'storage/countryFlag/somalia.png'
            ],
        ];

        foreach ($countries as $country) {
            $countryData = countries::create([
                'name' => $country['name'],
                'country_code' => $country['country_code'],
                'currency_code' => $country['currency_code'],
                'currency_name' => $country['currency_name'],
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
