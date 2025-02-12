<?php

namespace Database\Seeders;

use App\Models\Photos;
use App\Models\sub_categories;
use Carbon\Carbon;

use Illuminate\Database\Seeder;


class subcategoriessss extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    function run()
    {
        $subCategories = [
            ['id' => '1', 'categories_id' => '3', 'name' => [
                'ar' => "أراضي",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/earth.png',],
            ['id' => '2', 'categories_id' => '3', 'name' => [
                'ar' => "استديو",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Studiohouse.png',],
            ['id' => '3', 'categories_id' => '3', 'name' => [
                'ar' => "بيت شعبي",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/housepopular.png',],
            ['id' => '4', 'categories_id' => '3', 'name' => [
                'ar' => "سكن عمال",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/accommodation.png',],
            ['id' => '5', 'categories_id' => '3', 'name' => [
                'ar' => "شاليهات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/chalets.png',],
            ['id' => '7', 'categories_id' => '3', 'name' => [
                'ar' => "شقق",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Apartments.png',],
            ['id' => '217', 'categories_id' => '3', 'name' => [
                'ar' => "شقق مفروشة",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/apartmentground.jpg',],
            ['id' => '8', 'categories_id' => '3', 'name' => [
                'ar' => "دولية",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Internationalestate.png',],
            ['id' => '9', 'categories_id' => '3', 'name' => [
                'ar' => "عمارات ومباني",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/condominium.png',],
            ['id' => '10', 'categories_id' => '3', 'name' => [
                'ar' => "فلل ومنازل",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/villa.png',],
            ['id' => '11', 'categories_id' => '3', 'name' => [
                'ar' => "فنادق ومنتجعات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/hotel.png',],
            ['id' => '12', 'categories_id' => '3', 'name' => [
                'ar' => "قاعات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Eventhalls.png',],
            ['id' => '13', 'categories_id' => '3', 'name' => [
                'ar' => "قيد الانشاء",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Excavatorconstruction.png',],
            ['id' => '14', 'categories_id' => '3', 'name' => [
                'ar' => "مجمعات تجارية",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/shoppingmall.jpg',],
            ['id' => '15', 'categories_id' => '3', 'name' => [
                'ar' => "محلات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/retailstore.png',],
            ['id' => '16', 'categories_id' => '3', 'name' => [
                'ar' => "مزارع",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/farmanimals.png',],
            ['id' => '17', 'categories_id' => '3', 'name' => [
                'ar' => "مستودعات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/warehouses.png',],
            ['id' => '18', 'categories_id' => '3', 'name' => [
                'ar' => "مشاركة سكن",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/housingsharing.png',],
            ['id' => '19', 'categories_id' => '3', 'name' => [
                'ar' => "مصانع",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Factories.jpg',],
            ['id' => '20', 'categories_id' => '3', 'name' => [
                'ar' => "مطاعم ومقاهي",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Restaurants.png',],
            ['id' => '21', 'categories_id' => '3', 'name' => [
                'ar' => "مكاتب",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/offices.png',],
            ['id' => '22', 'categories_id' => '3', 'name' => [
                'ar' => "متفرقات",
                'en' => 'new value'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/houseapartment.png',],
            ['id' => '23', 'categories_id' => '4', 'name' => [
                'ar' => "استيراد سيارات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/job.png',],
            ['id' => '24', 'categories_id' => '4', 'name' => [
                'ar' => "قطاع غيار",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '25', 'categories_id' => '4', 'name' => [
                'ar' => "خدمات السيارات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/carrepair.png',],
            ['id' => '26', 'categories_id' => '4', 'name' => [
                'ar' => "اكسسورات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '27', 'categories_id' => '4', 'name' => [
                'ar' => "تاجير",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/carrent.png',],
            ['id' => '28', 'categories_id' => '4', 'name' => [
                'ar' => "ارقام",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/racingnumber.png',],
            ['id' => '160', 'categories_id' => '4', 'name' => [
                'ar' => "دراجات نارية",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/Motorcycles.png',],
            ['id' => '29', 'categories_id' => '4', 'name' => [
                'ar' => "استون مارتن",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/aston_martin.png',],
            ['id' => '30', 'categories_id' => '4', 'name' => [
                'ar' => "اسيا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/asia.png',],
            ['id' => '31', 'categories_id' => '4', 'name' => [
                'ar' => "الفا روميو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/alpha_Romeo.png',],
            ['id' => '32', 'categories_id' => '4', 'name' => [
                'ar' => "ام جي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mg.png',],
            ['id' => '33', 'categories_id' => '4', 'name' => [
                'ar' => "انفينيتي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/infiniti.png',],
            ['id' => '34', 'categories_id' => '4', 'name' => [
                'ar' => "اوبل",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/opel.png',],
            ['id' => '35', 'categories_id' => '4', 'name' => [
                'ar' => "اودي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/audi.png',],
            ['id' => '36', 'categories_id' => '4', 'name' => [
                'ar' => "اوستن",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Austin.png',],
            ['id' => '37', 'categories_id' => '4', 'name' => [
                'ar' => "اولدس موبيل",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Oldsmobile.png',],
            ['id' => '38', 'categories_id' => '4', 'name' => [
                'ar' => "ايسوزي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/isuzu.png',],
            ['id' => '39', 'categories_id' => '4', 'name' => [
                'ar' => "بروتن",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/proton.png',],
            ['id' => '40', 'categories_id' => '4', 'name' => [
                'ar' => "بليموث",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Plymouth.png',],
            ['id' => '41', 'categories_id' => '4', 'name' => [
                'ar' => "بنتلي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/bentley.png',],
            ['id' => '42', 'categories_id' => '4', 'name' => [
                'ar' => "بورش",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/porsche.png',],
            ['id' => '43', 'categories_id' => '4', 'name' => [
                'ar' => "بوغاتي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Bugatti.png',],
            ['id' => '44', 'categories_id' => '4', 'name' => [
                'ar' => "بويك",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/buick.png',],
            ['id' => '45', 'categories_id' => '4', 'name' => [
                'ar' => "بي ام دبليو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/bmw.png',],
            ['id' => '46', 'categories_id' => '4', 'name' => [
                'ar' => "بيجو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Peugeot.png',],
            ['id' => '47', 'categories_id' => '4', 'name' => [
                'ar' => "تاتا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Tata.png',],
            ['id' => '48', 'categories_id' => '4', 'name' => [
                'ar' => "تويوتا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/toyota.png',],
            ['id' => '49', 'categories_id' => '4', 'name' => [
                'ar' => "جاك",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/JAC.png',],
            ['id' => '50', 'categories_id' => '4', 'name' => [
                'ar' => "جاكوار",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/jaguar.png',],
            ['id' => '51', 'categories_id' => '4', 'name' => [
                'ar' => "جي ام سي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/gmc.png',],
            ['id' => '52', 'categories_id' => '4', 'name' => [
                'ar' => "جيب",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/jeep.png',],
            ['id' => '53', 'categories_id' => '4', 'name' => [
                'ar' => "داتسون",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/datsun.png',],
            ['id' => '54', 'categories_id' => '4', 'name' => [
                'ar' => "داسيا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Dacia.png',],
            ['id' => '55', 'categories_id' => '4', 'name' => [
                'ar' => "دايهاتسو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/daihatsu.png',],
            ['id' => '56', 'categories_id' => '4', 'name' => [
                'ar' => "دايوو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/daewoo.png',],
            ['id' => '57', 'categories_id' => '4', 'name' => [
                'ar' => "دفسك",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/dfsk.png',],
            ['id' => '58', 'categories_id' => '4', 'name' => [
                'ar' => "دودج",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/dodge.png',],
            ['id' => '59', 'categories_id' => '4', 'name' => [
                'ar' => "روفر",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/rover.png',],
            ['id' => '60', 'categories_id' => '4', 'name' => [
                'ar' => "رولز رويس",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/rollsroyce.png',],
            ['id' => '61', 'categories_id' => '4', 'name' => [
                'ar' => "رينو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/renault.png',],
            ['id' => '62', 'categories_id' => '4', 'name' => [
                'ar' => "ساب",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Saab.png',],
            ['id' => '63', 'categories_id' => '4', 'name' => [
                'ar' => "سابا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/saba.png',],
            ['id' => '64', 'categories_id' => '4', 'name' => [
                'ar' => "سانغيونغ",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ssangyong.png',],
            ['id' => '65', 'categories_id' => '4', 'name' => [
                'ar' => "ستروين",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/citroen.png',],
            ['id' => '66', 'categories_id' => '4', 'name' => [
                'ar' => "سكودا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/skoda.png',],
            ['id' => '67', 'categories_id' => '4', 'name' => [
                'ar' => "سمارت",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/smart.png',],
            ['id' => '68', 'categories_id' => '4', 'name' => [
                'ar' => "سوبارو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/subaru.png',],
            ['id' => '69', 'categories_id' => '4', 'name' => [
                'ar' => "سوزوكي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/suzuki.png',],
            ['id' => '70', 'categories_id' => '4', 'name' => [
                'ar' => "سيات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/seat.png',],
            ['id' => '71', 'categories_id' => '4', 'name' => [
                'ar' => "سيمكا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/simca.png',],
            ['id' => '72', 'categories_id' => '4', 'name' => [
                'ar' => "شفروليه",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chevrolet.png',],
            ['id' => '73', 'categories_id' => '4', 'name' => [
                'ar' => "شيري",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chery.png',],
            ['id' => '74', 'categories_id' => '4', 'name' => [
                'ar' => "فراري",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ferrari.png',],
            ['id' => '75', 'categories_id' => '4', 'name' => [
                'ar' => "فورد",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ford.png',],
            ['id' => '76', 'categories_id' => '4', 'name' => [
                'ar' => "فولفو",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Volvo.png',],
            ['id' => '77', 'categories_id' => '4', 'name' => [
                'ar' => "فولكس واجن",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/volkswagen.png',],
            ['id' => '78', 'categories_id' => '4', 'name' => [
                'ar' => "فيات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/fiat.png',],
            ['id' => '79', 'categories_id' => '4', 'name' => [
                'ar' => "كاديلاك",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/cadillac.png',],
            ['id' => '80', 'categories_id' => '4', 'name' => [
                'ar' => "كرايسلر",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chrysler.png',],
            ['id' => '81', 'categories_id' => '4', 'name' => [
                'ar' => "كلاسيكية",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/classic.png',],
            ['id' => '82', 'categories_id' => '4', 'name' => [
                'ar' => "كيا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/kia.png',],
            ['id' => '83', 'categories_id' => '4', 'name' => [
                'ar' => "لادا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lada.png',],
            ['id' => '84', 'categories_id' => '4', 'name' => [
                'ar' => "لامبرغيتي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lamborghini.png',],
            ['id' => '85', 'categories_id' => '4', 'name' => [
                'ar' => "لاند روفر",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/landrover.png',],
            ['id' => '86', 'categories_id' => '4', 'name' => [
                'ar' => "لانسيا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lancia.png',],
            ['id' => '87', 'categories_id' => '4', 'name' => [
                'ar' => "لكزس",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lexus.png',],
            ['id' => '88', 'categories_id' => '4', 'name' => [
                'ar' => "لينكولن",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lincoln.png',],
            ['id' => '89', 'categories_id' => '4', 'name' => [
                'ar' => "مازدا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mazda.png',],
            ['id' => '90', 'categories_id' => '4', 'name' => [
                'ar' => "مازيراتي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/maserati.png',],
            ['id' => '91', 'categories_id' => '4', 'name' => [
                'ar' => "ماكلارين",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mclaren.png',],
            ['id' => '92', 'categories_id' => '4', 'name' => [
                'ar' => "مرسيدس",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mercedes.png',],
            ['id' => '93', 'categories_id' => '4', 'name' => [
                'ar' => "مورغان",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/morgan.png',],
            ['id' => '94', 'categories_id' => '4', 'name' => [
                'ar' => "ميتسوبيشي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mitsubishi.png',],
            ['id' => '95', 'categories_id' => '4', 'name' => [
                'ar' => "ميركيري",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mercury.png',],
            ['id' => '96', 'categories_id' => '4', 'name' => [
                'ar' => "ميني كوبر",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/minicooper.png',],
            ['id' => '97', 'categories_id' => '4', 'name' => [
                'ar' => "نيسان",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/nissan.png',],
            ['id' => '98', 'categories_id' => '4', 'name' => [
                'ar' => "همر",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Hummer.png',],
            ['id' => '99', 'categories_id' => '4', 'name' => [
                'ar' => "هوندا",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/honda.png',],
            ['id' => '100', 'categories_id' => '4', 'name' => [
                'ar' => "هيونداي",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/hyundai.png',],
            ['id' => '101', 'categories_id' => '4', 'name' => [
                'ar' => "متفرقات",
                'en' => 'new value'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/general.png',],
            ['id' => '102', 'categories_id' => '1', 'name' => [
                'ar' => "إدارة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/businessperson.png',],
            ['id' => '103', 'categories_id' => '1', 'name' => [
                'ar' => "أزياء",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/fashion.png',],
            ['id' => '104', 'categories_id' => '1', 'name' => [
                'ar' => "برمجة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/programmer.png',],
            ['id' => '105', 'categories_id' => '1', 'name' => [
                'ar' => "تزيين وتجميل",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/salon.png',],
            ['id' => '106', 'categories_id' => '1', 'name' => [
                'ar' => "تصميم",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Design.png',],
            ['id' => '107', 'categories_id' => '1', 'name' => [
                'ar' => "تعليم وتدريس",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/learning.png',],
            ['id' => '108', 'categories_id' => '1', 'name' => [
                'ar' => "تقني",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/techengineering.png',],
            ['id' => '109', 'categories_id' => '1', 'name' => [
                'ar' => "حدائق ومناظر",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Garden.png',],
            ['id' => '110', 'categories_id' => '1', 'name' => [
                'ar' => "حراسة وامن",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/securityguard.png',],
            ['id' => '111', 'categories_id' => '1', 'name' => [
                'ar' => "حرفيين",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/profession.png',],
            ['id' => '112', 'categories_id' => '1', 'name' => [
                'ar' => "محاسبة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/accounting.png',],
            ['id' => '113', 'categories_id' => '1', 'name' => [
                'ar' => "حضانة أطفال",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/childcare.png',],
            ['id' => '114', 'categories_id' => '1', 'name' => [
                'ar' => "خدمة الزبائن",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/customercare.png',],
            ['id' => '115', 'categories_id' => '1', 'name' => [
                'ar' => "خياطين",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Tailors.png',],
            ['id' => '116', 'categories_id' => '1', 'name' => [
                'ar' => "سائق",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/partybus.png',],
            ['id' => '117', 'categories_id' => '1', 'name' => [
                'ar' => "سكرتارية",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/secretary.png',],
            ['id' => '118', 'categories_id' => '1', 'name' => [
                'ar' => "سياحة وسفر",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/plane.png',],
            ['id' => '119', 'categories_id' => '1', 'name' => [
                'ar' => "سياحة ومطاعم",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Restaurant.png',],
            ['id' => '120', 'categories_id' => '1', 'name' => [
                'ar' => "شراكة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/copartnership.png',],
            ['id' => '121', 'categories_id' => '1', 'name' => [
                'ar' => "طب وتمريض",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Medicine.png',],
            ['id' => '122', 'categories_id' => '1', 'name' => [
                'ar' => "علاقات عامة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/publicrelations.png',],
            ['id' => '123', 'categories_id' => '1', 'name' => [
                'ar' => "عمال",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Workers.png',],
            ['id' => '124', 'categories_id' => '1', 'name' => [
                'ar' => "عمال تنظيف",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/cleaning.png',],
            ['id' => '125', 'categories_id' => '1', 'name' => [
                'ar' => "عمال ديلفري",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/delivery.png',],
            ['id' => '126', 'categories_id' => '1', 'name' => [
                'ar' => "عمالة منزلية",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/homecleaning.png',],
            ['id' => '127', 'categories_id' => '1', 'name' => [
                'ar' => "فنون جميلة",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/paintbrush.png',],
            ['id' => '128', 'categories_id' => '1', 'name' => [
                'ar' => "كمبيوتر وشبكات",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/network.png',],
            ['id' => '129', 'categories_id' => '1', 'name' => [
                'ar' => "لياقة بدنية",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/gym.png',],
            ['id' => '130', 'categories_id' => '1', 'name' => [
                'ar' => "مبيعات وتسويق",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/marketing.png',],
            ['id' => '131', 'categories_id' => '1', 'name' => [
                'ar' => "مترجمين",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Translation.png',],
            ['id' => '132', 'categories_id' => '1', 'name' => [
                'ar' => "محاماه وقانون",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/lawyer.png',],
            ['id' => '133', 'categories_id' => '1', 'name' => [
                'ar' => "محررين",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/editing.png',],
            ['id' => '134', 'categories_id' => '1', 'name' => [
                'ar' => "مدخل بيانات",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/dataentry.png',],
            ['id' => '135', 'categories_id' => '1', 'name' => [
                'ar' => "مقاولات",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Construction.png',],
            ['id' => '136', 'categories_id' => '1', 'name' => [
                'ar' => "مهندس",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Engineer.png',],
            ['id' => '137', 'categories_id' => '1', 'name' => [
                'ar' => "موظفين",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/employees.png',],
            ['id' => '138', 'categories_id' => '1', 'name' => [
                'ar' => "مونتاج وإخراج",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/videography.png',],
            ['id' => '139', 'categories_id' => '1', 'name' => [
                'ar' => "متفرقات",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/job.png',],
            ['id' => '140', 'categories_id' => '5', 'name' => [
                'ar' => "اتصالات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/communication.png',],
            ['id' => '141', 'categories_id' => '5', 'name' => [
                'ar' => "ارقام مميزة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/numberstar.png',],
            ['id' => '142', 'categories_id' => '5', 'name' => [
                'ar' => "اعمال واستثمار",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/investor.png',],
            ['id' => '143', 'categories_id' => '5', 'name' => [
                'ar' => "اكسسورات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/queen.png',],
            ['id' => '218', 'categories_id' => '5', 'name' => [
                'ar' => "صرافة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/investor.png',],
            ['id' => '219', 'categories_id' => '5', 'name' => [
                'ar' => "سياحة وسفر",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/plane.png',],
            ['id' => '144', 'categories_id' => '5', 'name' => [
                'ar' => "العاب",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/games.png',],
            ['id' => '145', 'categories_id' => '5', 'name' => [
                'ar' => "الكترونيات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/electronics.png',],
            ['id' => '146', 'categories_id' => '5', 'name' => [
                'ar' => "اليات ثقيلة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/heavy.png',],
            ['id' => '147', 'categories_id' => '5', 'name' => [
                'ar' => "أنشطة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/activity.png',],
            ['id' => '148', 'categories_id' => '5', 'name' => [
                'ar' => "امن وحماية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/camera.png',],
            ['id' => '149', 'categories_id' => '5', 'name' => [
                'ar' => "بيت متنقل",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/pngtree.png',],
            ['id' => '150', 'categories_id' => '5', 'name' => [
                'ar' => "تجهيز وتشطيب",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/silhouette.png',],
            ['id' => '151', 'categories_id' => '5', 'name' => [
                'ar' => "تحف ومقتنيات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/decoration.png',],
            ['id' => '152', 'categories_id' => '5', 'name' => [
                'ar' => "تخليص معاملات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/businessman.png',],
            ['id' => '153', 'categories_id' => '5', 'name' => [
                'ar' => "تراخيص",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/license.png',],
            ['id' => '154', 'categories_id' => '5', 'name' => [
                'ar' => "توصيل ونقل",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/truck.png',],
            ['id' => '155', 'categories_id' => '5', 'name' => [
                'ar' => "حواسيب وصيانة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maintenance.png',],
            ['id' => '156', 'categories_id' => '5', 'name' => [
                'ar' => "حيوانات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Pets.png',],
            ['id' => '157', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات الصيانة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '158', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات زراعية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/agriculture.png',],
            ['id' => '159', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات عقارية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/building.png',],
            ['id' => '161', 'categories_id' => '5', 'name' => [
                'ar' => "ساعات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/watch.png',],
            ['id' => '162', 'categories_id' => '5', 'name' => [
                'ar' => "شحن",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/shipping.png',],
            ['id' => '163', 'categories_id' => '5', 'name' => [
                'ar' => "صيد سمك",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/fishing.png',],
            ['id' => '164', 'categories_id' => '5', 'name' => [
                'ar' => "طبخ منزلي",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/cooking.png',],
            ['id' => '165', 'categories_id' => '5', 'name' => [
                'ar' => "عروض بالجملة",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/offers.png',],
            ['id' => '166', 'categories_id' => '5', 'name' => [
                'ar' => "عطورات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/parfume.png',],
            ['id' => '167', 'categories_id' => '5', 'name' => [
                'ar' => "كتب ومجلات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Books.png',],
            ['id' => '168', 'categories_id' => '5', 'name' => [
                'ar' => "مستحضرات تجميل",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/skincare.png',],
            ['id' => '169', 'categories_id' => '5', 'name' => [
                'ar' => "معدات بحرية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maritime.png',],
            ['id' => '170', 'categories_id' => '5', 'name' => [
                'ar' => "معدات رياضية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/sportds.png',],
            ['id' => '171', 'categories_id' => '5', 'name' => [
                'ar' => "مفروشات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Furnitures.png',],
            ['id' => '172', 'categories_id' => '5', 'name' => [
                'ar' => "مفقودات",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/miscellaneous.png',],
            ['id' => '173', 'categories_id' => '5', 'name' => [
                'ar' => "ملابس",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/clothes.png',],
            ['id' => '174', 'categories_id' => '5', 'name' => [
                'ar' => "مواد بناء",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/renovation.png',],
            ['id' => '175', 'categories_id' => '5', 'name' => [
                'ar' => "مواد غذائية",
                'en' => 'new value'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Goodfood.png',],
            ['id' => '176', 'categories_id' => '2', 'name' => [
                'ar' => "مولدات كهرباء",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/Generators.jpeg',],
            ['id' => '177', 'categories_id' => '2', 'name' => [
                'ar' => "هواتف ذكية",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/phones.png',],
            ['id' => '178', 'categories_id' => '2', 'name' => [
                'ar' => "كاميرات",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/camerapencil.png',],
            ['id' => '179', 'categories_id' => '2', 'name' => [
                'ar' => "تلفزيون",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/televisions.png',],
            ['id' => '180', 'categories_id' => '2', 'name' => [
                'ar' => "غسالة",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/dishwasher.png',],
            ['id' => '181', 'categories_id' => '2', 'name' => [
                'ar' => "ثلاجة",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Refrigerator.png',],
            ['id' => '182', 'categories_id' => '2', 'name' => [
                'ar' => "عجانة",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Mixer.png',],
            ['id' => '183', 'categories_id' => '2', 'name' => [
                'ar' => "فرن الغاز",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/stovegas.png',],
            ['id' => '184', 'categories_id' => '2', 'name' => [
                'ar' => "ميكرويف",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Microwave.png',],
            ['id' => '185', 'categories_id' => '2', 'name' => [
                'ar' => "مكواه",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/steamiron.png',],
            ['id' => '186', 'categories_id' => '2', 'name' => [
                'ar' => "مروحة",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Fan.png',],
            ['id' => '187', 'categories_id' => '2', 'name' => [
                'ar' => "المكيفات",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/evaporative.png',],
            ['id' => '188', 'categories_id' => '2', 'name' => [
                'ar' => "الخلاط",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Blender-Mixe.png',],
            ['id' => '189', 'categories_id' => '2', 'name' => [
                'ar' => "مكنسة كهربائية",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/vacuum-cleaner.png',],
            ['id' => '190', 'categories_id' => '2', 'name' => [
                'ar' => "الطابعات",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/printer.png',],
            ['id' => '191', 'categories_id' => '2', 'name' => [
                'ar' => "السماعات",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/headphones.png',],
            ['id' => '192', 'categories_id' => '2', 'name' => [
                'ar' => "أجهزة طبية",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/medicaldevices.png',],
            ['id' => '193', 'categories_id' => '2', 'name' => [
                'ar' => "أجهزة منزلية",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/appliances.png',],
            ['id' => '194', 'categories_id' => '2', 'name' => [
                'ar' => "اجهزة اخرى",
                'en' => 'new value'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/appliances.png',],
            ['id' => '195', 'categories_id' => '1', 'name' => [
                'ar' => "موارد بشرية",
                'en' => 'new value'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Humanresources.png',],
            ['id' => '196', 'categories_id' => '7', 'name' => [
                'ar' => "عاجل",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/uragent.jpg',],
            ['id' => '197', 'categories_id' => '7', 'name' => [
                'ar' => "غزة",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/gaza.png',],
            ['id' => '198', 'categories_id' => '7', 'name' => [
                'ar' => "الضفة",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/west_bank.png',],
            ['id' => '199', 'categories_id' => '7', 'name' => [
                'ar' => "القدس",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/jorasalim.png',],
            ['id' => '200', 'categories_id' => '7', 'name' => [
                'ar' => "اراضي ال 48",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/land48.png',],
            ['id' => '201', 'categories_id' => '7', 'name' => [
                'ar' => "في الخارج",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/outside.png',],
            ['id' => '202', 'categories_id' => '7', 'name' => [
                'ar' => "سياسة",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/politcal.png',],
            ['id' => '203', 'categories_id' => '7', 'name' => [
                'ar' => "اقتصاد",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/economy.jpg',],
            ['id' => '204', 'categories_id' => '7', 'name' => [
                'ar' => "اسرائيليات",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/isreal.png',],
            ['id' => '205', 'categories_id' => '7', 'name' => [
                'ar' => "الصحة",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/Health.png',],
            ['id' => '206', 'categories_id' => '7', 'name' => [
                'ar' => "التعليم",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/education.png',],
            ['id' => '207', 'categories_id' => '7', 'name' => [
                'ar' => "التكنولجيا",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/technology.jpg',],
            ['id' => '208', 'categories_id' => '7', 'name' => [
                'ar' => "محلي",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/breaking.jpg',],
            ['id' => '209', 'categories_id' => '7', 'name' => [
                'ar' => "عربي",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/arabic.png',],
            ['id' => '210', 'categories_id' => '7', 'name' => [
                'ar' => "دولي",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/glob.png',],
            ['id' => '211', 'categories_id' => '7', 'name' => [
                'ar' => "المجتمع",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/society.png',],
            ['id' => '212', 'categories_id' => '7', 'name' => [
                'ar' => "اعمال وشركات",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/marketing.png',],
            ['id' => '213', 'categories_id' => '7', 'name' => [
                'ar' => "سفر وسياحة",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/airplane.jpg',],
            ['id' => '214', 'categories_id' => '7', 'name' => [
                'ar' => "ثقافة وفنون",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/camerapencil.png',],
            ['id' => '215', 'categories_id' => '7', 'name' => [
                'ar' => "ترفيه",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/celebration.png',],
            ['id' => '216', 'categories_id' => '7', 'name' => [
                'ar' => "منوعات",
                'en' => 'new value'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/news.png',],
        ];
    }
}
