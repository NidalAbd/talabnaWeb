<?php

namespace Database\Seeders;

use App\Models\cities;
use App\Models\countries;
use Illuminate\Database\Seeder;

class PalestineCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $country = countries::where('iso_code', 'PS')->first();
        if (!$country) {
            echo "Palestine not found\n";
            return;
        }

        // Delete existing
        cities::where('country_id', $country->id)->delete();

        $cityList = [
            // Gaza Strip - Cities
            ['en' => 'Gaza City', 'ar' => 'مدينة غزة'],
            ['en' => 'Khan Yunis', 'ar' => 'خان يونس'],
            ['en' => 'Rafah', 'ar' => 'رفح'],
            ['en' => 'Deir al-Balah', 'ar' => 'دير البلح'],
            ['en' => 'Jabalia', 'ar' => 'جباليا'],
            ['en' => 'Beit Hanoun', 'ar' => 'بيت حانون'],
            ['en' => 'Beit Lahia', 'ar' => 'بيت لاهيا'],
            ['en' => 'Nuseirat', 'ar' => 'النصيرات'],
            ['en' => 'Bureij', 'ar' => 'البريج'],
            ['en' => 'Maghazi', 'ar' => 'المغازي'],
            ['en' => 'Abasan al-Kabira', 'ar' => 'عبسان الكبيرة'],
            ['en' => 'Abasan al-Saghira', 'ar' => 'عبسان الصغيرة'],
            ['en' => 'Bani Suheila', 'ar' => 'بني سهيلا'],
            ['en' => 'Al-Qarara', 'ar' => 'القرارة'],
            ['en' => 'Al-Fukhari', 'ar' => 'الفخاري'],
            ['en' => 'Wadi Gaza', 'ar' => 'وادي غزة'],
            ['en' => 'Al-Zawaida', 'ar' => 'الزوايدة'],
            ['en' => 'Al-Musaddar', 'ar' => 'المصدر'],
            ['en' => 'Um al-Nasser', 'ar' => 'أم النصر'],
            ['en' => 'Juhr al-Dik', 'ar' => 'جحر الديك'],
            ['en' => 'Al-Mawasi', 'ar' => 'المواصي'],
            // West Bank - Major Cities
            ['en' => 'Ramallah', 'ar' => 'رام الله'],
            ['en' => 'Nablus', 'ar' => 'نابلس'],
            ['en' => 'Hebron', 'ar' => 'الخليل'],
            ['en' => 'Bethlehem', 'ar' => 'بيت لحم'],
            ['en' => 'Jericho', 'ar' => 'أريحا'],
            ['en' => 'Jenin', 'ar' => 'جنين'],
            ['en' => 'Tulkarm', 'ar' => 'طولكرم'],
            ['en' => 'Qalqilya', 'ar' => 'قلقيلية'],
            ['en' => 'Salfit', 'ar' => 'سلفيت'],
            ['en' => 'Tubas', 'ar' => 'طوباس'],
            ['en' => 'Al-Bireh', 'ar' => 'البيرة'],
            // West Bank - Towns
            ['en' => 'Bir Zeit', 'ar' => 'بير زيت'],
            ['en' => 'Beit Jala', 'ar' => 'بيت جالا'],
            ['en' => 'Beit Sahour', 'ar' => 'بيت ساحور'],
            ['en' => 'Dura', 'ar' => 'دورا'],
            ['en' => 'Halhul', 'ar' => 'حلحول'],
            ['en' => 'Yatta', 'ar' => 'يطا'],
            ['en' => 'Beit Ummar', 'ar' => 'بيت أمر'],
            ['en' => 'Azzun', 'ar' => 'عزون'],
            ['en' => 'Anabta', 'ar' => 'عنبتا'],
            ['en' => 'Qabatiya', 'ar' => 'قباطية'],
            ['en' => 'Arraba', 'ar' => 'عرابة'],
            ['en' => 'Silat al-Harithiya', 'ar' => 'سيلة الحارثية'],
            ['en' => 'Beit Furik', 'ar' => 'بيت فوريك'],
            ['en' => 'Aqraba', 'ar' => 'عقربا'],
            ['en' => 'Beita', 'ar' => 'بيتا'],
            ['en' => 'Huwara', 'ar' => 'حوارة'],
            ['en' => 'Asira al-Shamaliya', 'ar' => 'عصيرة الشمالية'],
            ['en' => 'Balata', 'ar' => 'بلاطة'],
            ['en' => 'Deir Istiya', 'ar' => 'دير استيا'],
            ['en' => 'Kafr ad-Dik', 'ar' => 'كفر الديك'],
            ['en' => 'Bidya', 'ar' => 'بديا'],
            ['en' => 'Beit Ula', 'ar' => 'بيت أولا'],
            ['en' => 'Tarqumiya', 'ar' => 'ترقوميا'],
            ['en' => 'Surif', 'ar' => 'صوريف'],
            ['en' => 'Bani Naim', 'ar' => 'بني نعيم'],
            ['en' => 'Al-Dhahiriya', 'ar' => 'الظاهرية'],
            ['en' => 'Al-Samou', 'ar' => 'السموع'],
            ['en' => 'Al-Shuyukh', 'ar' => 'الشيوخ'],
            ['en' => 'Taffuh', 'ar' => 'تفوح'],
            ['en' => 'Idhna', 'ar' => 'إذنا'],
            ['en' => 'Nuba', 'ar' => 'نوبا'],
            ['en' => 'Al-Khadr', 'ar' => 'الخضر'],
            ['en' => 'Tuqu', 'ar' => 'تقوع'],
            ['en' => 'Al-Ubeidiya', 'ar' => 'العبيدية'],
            ['en' => 'Husan', 'ar' => 'حوسان'],
            ['en' => 'Battir', 'ar' => 'بتير'],
            ['en' => 'Nahalin', 'ar' => 'نحالين'],
            ['en' => 'Al-Jib', 'ar' => 'الجيب'],
            ['en' => 'Bir Nabala', 'ar' => 'بير نبالا'],
            ['en' => 'Al-Ram', 'ar' => 'الرام'],
            ['en' => 'Beit Anan', 'ar' => 'بيت عنان'],
            ['en' => 'Biddu', 'ar' => 'بدو'],
            ['en' => 'Qatanna', 'ar' => 'قطنة'],
            ['en' => 'Al-Eizariya', 'ar' => 'العيزرية'],
            ['en' => 'Abu Dis', 'ar' => 'أبو ديس'],
            ['en' => 'As-Sawahira', 'ar' => 'السواحرة'],
            ['en' => 'Silwad', 'ar' => 'سلواد'],
            ['en' => 'Deir Jarir', 'ar' => 'دير جرير'],
            ['en' => 'Kafr Malik', 'ar' => 'كفر مالك'],
            ['en' => 'Turmus Ayya', 'ar' => 'ترمسعيا'],
            ['en' => 'Sinjil', 'ar' => 'سنجل'],
            ['en' => 'Al-Mazraa al-Sharqiya', 'ar' => 'المزرعة الشرقية'],
            ['en' => 'Beit Liqya', 'ar' => 'بيت لقيا'],
            ['en' => 'Deir Qaddis', 'ar' => 'دير قديس'],
            ['en' => 'Nilin', 'ar' => 'نعلين'],
            ['en' => 'Budrus', 'ar' => 'بدرس'],
            // Refugee Camps
            ['en' => 'Jabalia Camp', 'ar' => 'مخيم جباليا'],
            ['en' => 'Beach Camp', 'ar' => 'مخيم الشاطئ'],
            ['en' => 'Nuseirat Camp', 'ar' => 'مخيم النصيرات'],
            ['en' => 'Bureij Camp', 'ar' => 'مخيم البريج'],
            ['en' => 'Maghazi Camp', 'ar' => 'مخيم المغازي'],
            ['en' => 'Deir al-Balah Camp', 'ar' => 'مخيم دير البلح'],
            ['en' => 'Khan Yunis Camp', 'ar' => 'مخيم خان يونس'],
            ['en' => 'Rafah Camp', 'ar' => 'مخيم رفح'],
            ['en' => 'Balata Camp', 'ar' => 'مخيم بلاطة'],
            ['en' => 'Askar Camp', 'ar' => 'مخيم عسكر'],
            ['en' => 'Jenin Camp', 'ar' => 'مخيم جنين'],
            ['en' => 'Tulkarm Camp', 'ar' => 'مخيم طولكرم'],
            ['en' => 'Amari Camp', 'ar' => 'مخيم الأمعري'],
            ['en' => 'Qalandia Camp', 'ar' => 'مخيم قلنديا'],
            ['en' => 'Jalazone Camp', 'ar' => 'مخيم الجلزون'],
            ['en' => 'Dheisheh Camp', 'ar' => 'مخيم الدهيشة'],
            ['en' => 'Aida Camp', 'ar' => 'مخيم عايدة'],
            ['en' => 'Aqabat Jaber Camp', 'ar' => 'مخيم عقبة جبر'],
            ['en' => 'Fawwar Camp', 'ar' => 'مخيم الفوار'],
            ['en' => 'Arroub Camp', 'ar' => 'مخيم العروب'],
            ['en' => 'Ein Beit al-Ma Camp', 'ar' => 'مخيم عين بيت الماء'],
            ['en' => 'Nur Shams Camp', 'ar' => 'مخيم نور شمس'],
        ];

        $count = 0;
        foreach ($cityList as $city) {
            cities::create(['name' => $city, 'country_id' => $country->id]);
            $count++;
        }

        echo "{$count} cities created for Palestine with Arabic + English names\n";
    }
}
