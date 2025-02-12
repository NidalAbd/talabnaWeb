<?php

namespace Database\Seeders;

use App\Models\Photos;
use App\Models\sub_categories;
use Carbon\Carbon;
use Illuminate\Database\Seeder;


class subcategories extends Seeder
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
                'en' => 'Lands'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/earth.png',],
            ['id' => '2', 'categories_id' => '3', 'name' => [
                'ar' => "استديو",
                'en' => 'Studio'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Studiohouse.png',],
            ['id' => '3', 'categories_id' => '3', 'name' => [
                'ar' => "بيت شعبي",
                'en' => 'Traditional House'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/housepopular.png',],
            ['id' => '4', 'categories_id' => '3', 'name' => [
                'ar' => "سكن عمال",
                'en' => 'Worker Accommodation'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/accommodation.png',],
            ['id' => '5', 'categories_id' => '3', 'name' => [
                'ar' => "Chalets",
                'en' => 'new'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/chalets.png',],
            ['id' => '7', 'categories_id' => '3', 'name' => [
                'ar' => "شقق",
                'en' => 'Apartments'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Apartments.png',],
            ['id' => '217', 'categories_id' => '3', 'name' => [
                'ar' => "شقق مفروشة",
                'en' => 'Furnished Apartments'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/apartmentground.jpg',],
            ['id' => '8', 'categories_id' => '3', 'name' => [
                'ar' => "دولية",
                'en' => 'International'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Internationalestate.png',],
            ['id' => '9', 'categories_id' => '3', 'name' => [
                'ar' => "عمارات ومباني",
                'en' => 'Condominiums and Buildings'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/condominium.png',],
            ['id' => '10', 'categories_id' => '3', 'name' => [
                'ar' => "فلل ومنازل",
                'en' => 'Villas and Houses'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/villa.png',],
            ['id' => '11', 'categories_id' => '3', 'name' => [
                'ar' => "فنادق ومنتجعات",
                'en' => 'Hotels and Resorts'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/hotel.png',],
            ['id' => '12', 'categories_id' => '3', 'name' => [
                'ar' => "قاعات",
                'en' => 'Event Halls'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Eventhalls.png',],
            ['id' => '13', 'categories_id' => '3', 'name' => [
                'ar' => "قيد الانشاء",
                'en' => 'Under Construction'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Excavatorconstruction.png',],
            ['id' => '14', 'categories_id' => '3', 'name' => [
                'ar' => "مجمعات تجارية",
                'en' => 'Commercial Complexes'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/shoppingmall.jpg',],
            ['id' => '15', 'categories_id' => '3', 'name' => [
                'ar' => "محلات",
                'en' => 'Shops'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/retailstore.png',],
            ['id' => '16', 'categories_id' => '3', 'name' => [
                'ar' => "مزارع",
                'en' => 'Farms'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/farmanimals.png',],
            ['id' => '17', 'categories_id' => '3', 'name' => [
                'ar' => "مستودعات",
                'en' => 'Warehouses'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/warehouses.png',],
            ['id' => '18', 'categories_id' => '3', 'name' => [
                'ar' => "مشاركة سكن",
                'en' => 'Shared Housing'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/housingsharing.png',],
            ['id' => '19', 'categories_id' => '3', 'name' => [
                'ar' => "مصانع",
                'en' => 'Factories'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Factories.jpg',],
            ['id' => '20', 'categories_id' => '3', 'name' => [
                'ar' => "مطاعم ومقاهي",
                'en' => 'Restaurants and Cafes'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/Restaurants.png',],
            ['id' => '21', 'categories_id' => '3', 'name' => [
                'ar' => "مكاتب",
                'en' => 'Offices'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/offices.png',],
            ['id' => '22', 'categories_id' => '3', 'name' => [
                'ar' => "متفرقات",
                'en' => 'Miscellaneous'
            ], 'category_name' => 'عقارات', 'src' => 'storage/subcategory/عقارات/houseapartment.png',],
            ['id' => '23', 'categories_id' => '4', 'name' => [
                'ar' => "استيراد سيارات",
                'en' => 'Car Imports'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/job.png',],
            ['id' => '24', 'categories_id' => '4', 'name' => [
                'ar' => "قطاع غيار",
                'en' => 'Spare Parts'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '25', 'categories_id' => '4', 'name' => [
                'ar' => "خدمات السيارات",
                'en' => 'Car Services'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/carrepair.png',],
            ['id' => '26', 'categories_id' => '4', 'name' => [
                'ar' => "اكسسورات",
                'en' => 'Accessories'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '27', 'categories_id' => '4', 'name' => [
                'ar' => "تاجير",
                'en' => 'Rental'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/carrent.png',],
            ['id' => '28', 'categories_id' => '4', 'name' => [
                'ar' => "ارقام",
                'en' => 'Numbers'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/racingnumber.png',],
            ['id' => '160', 'categories_id' => '4', 'name' => [
                'ar' => "دراجات نارية",
                'en' => 'Motorcycles'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/خدمات/Motorcycles.png',],
            ['id' => '29', 'categories_id' => '4', 'name' => [
                'ar' => "استون مارتن",
                'en' => 'Aston Martin'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/aston_martin.png',],
            ['id' => '30', 'categories_id' => '4', 'name' => [
                'ar' => "اسيا",
                'en' => 'Asia'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/asia.png',],
            ['id' => '31', 'categories_id' => '4', 'name' => [
                'ar' => "الفا روميو",
                'en' => 'Alpha Romeo'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/alpha_Romeo.png',],
            ['id' => '32', 'categories_id' => '4', 'name' => [
                'ar' => "ام جي",
                'en' => 'MG'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mg.png',],
            ['id' => '33', 'categories_id' => '4', 'name' => [
                'ar' => "انفينيتي",
                'en' => 'Infiniti'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/infiniti.png',],
            ['id' => '34', 'categories_id' => '4', 'name' => [
                'ar' => "اوبل",
                'en' => 'Opel'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/opel.png',],
            ['id' => '35', 'categories_id' => '4', 'name' => [
                'ar' => "اودي",
                'en' => 'Audi'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/audi.png',],
            ['id' => '36', 'categories_id' => '4', 'name' => [
                'ar' => "اوستن",
                'en' => 'Austin'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Austin.png',],
            ['id' => '37', 'categories_id' => '4', 'name' => [
                'ar' => "اولدس موبيل",
                'en' => 'Oldsmobile'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Oldsmobile.png',],
            ['id' => '38', 'categories_id' => '4', 'name' => [
                'ar' => "ايسوزي",
                'en' => 'Isuzu'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/isuzu.png',],
            ['id' => '39', 'categories_id' => '4', 'name' => [
                'ar' => "بروتن",
                'en' => 'Proton'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/proton.png',],
            ['id' => '40', 'categories_id' => '4', 'name' => [
                'ar' => "بليموث",
                'en' => 'Plymouth'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Plymouth.png',],
            ['id' => '41', 'categories_id' => '4', 'name' => [
                'ar' => "بنتلي",
                'en' => 'Bentley'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/bentley.png',],
            ['id' => '42', 'categories_id' => '4', 'name' => [
                'ar' => "بورش",
                'en' => 'Porsche'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/porsche.png',],
            ['id' => '43', 'categories_id' => '4', 'name' => [
                'ar' => "بوغاتي",
                'en' => 'Bugatti'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Bugatti.png',],
            ['id' => '44', 'categories_id' => '4', 'name' => [
                'ar' => "بويك",
                'en' => 'Buick'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/buick.png',],
            ['id' => '45', 'categories_id' => '4', 'name' => [
                'ar' => "بي ام دبليو",
                'en' => 'BMW'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/bmw.png',],
            ['id' => '46', 'categories_id' => '4', 'name' => [
                'ar' => "بيجو",
                'en' => 'Peugeot'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Peugeot.png',],
            ['id' => '47', 'categories_id' => '4', 'name' => [
                'ar' => "تاتا",
                'en' => 'Tata'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Tata.png',],
            ['id' => '48', 'categories_id' => '4', 'name' => [
                'ar' => "تويوتا",
                'en' => 'Toyota'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/toyota.png',],
            ['id' => '49', 'categories_id' => '4', 'name' => [
                'ar' => "جاك",
                'en' => 'JAC'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/JAC.png',],
            ['id' => '50', 'categories_id' => '4', 'name' => [
                'ar' => "جاكوار",
                'en' => 'Jaguar'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/jaguar.png',],
            ['id' => '51', 'categories_id' => '4', 'name' => [
                'ar' => "جي ام سي",
                'en' => 'GMC'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/gmc.png',],
            ['id' => '52', 'categories_id' => '4', 'name' => [
                'ar' => "جيب",
                'en' => 'Jeep'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/jeep.png',],
            ['id' => '53', 'categories_id' => '4', 'name' => [
                'ar' => "داتسون",
                'en' => 'Datsun'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/datsun.png',],
            ['id' => '54', 'categories_id' => '4', 'name' => [
                'ar' => "داسيا",
                'en' => 'Dacia'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Dacia.png',],
            ['id' => '55', 'categories_id' => '4', 'name' => [
                'ar' => "دايهاتسو",
                'en' => 'Daihatsu'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/daihatsu.png',],
            ['id' => '56', 'categories_id' => '4', 'name' => [
                'ar' => "دايوو",
                'en' => 'Daewoo'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/daewoo.png',],
            ['id' => '57', 'categories_id' => '4', 'name' => [
                'ar' => "دفسك",
                'en' => 'DFSK'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/dfsk.png',],
            ['id' => '58', 'categories_id' => '4', 'name' => [
                'ar' => "دودج",
                'en' => 'Dodge'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/dodge.png',],
            ['id' => '59', 'categories_id' => '4', 'name' => [
                'ar' => "روفر",
                'en' => 'Rover'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/rover.png',],
            ['id' => '60', 'categories_id' => '4', 'name' => [
                'ar' => "رولز رويس",
                'en' => 'Rolls-Royce'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/rollsroyce.png',],
            ['id' => '61', 'categories_id' => '4', 'name' => [
                'ar' => "رينو",
                'en' => 'Renault'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/renault.png',],
            ['id' => '62', 'categories_id' => '4', 'name' => [
                'ar' => "ساب",
                'en' => 'Saab'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Saab.png',],
            ['id' => '63', 'categories_id' => '4', 'name' => [
                'ar' => "سابا",
                'en' => 'Saba'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/saba.png',],
            ['id' => '64', 'categories_id' => '4', 'name' => [
                'ar' => "سانغيونغ",
                'en' => 'SsangYong'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ssangyong.png',],
            ['id' => '65', 'categories_id' => '4', 'name' => [
                'ar' => "ستروين",
                'en' => 'Citroen'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/citroen.png',],
            ['id' => '66', 'categories_id' => '4', 'name' => [
                'ar' => "سكودا",
                'en' => 'Skoda'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/skoda.png',],
            ['id' => '67', 'categories_id' => '4', 'name' => [
                'ar' => "سمارت",
                'en' => 'Smart'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/smart.png',],
            ['id' => '68', 'categories_id' => '4', 'name' => [
                'ar' => "سوبارو",
                'en' => 'Subaru'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/subaru.png',],
            ['id' => '69', 'categories_id' => '4', 'name' => [
                'ar' => "سوزوكي",
                'en' => 'Suzuki'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/suzuki.png',],
            ['id' => '70', 'categories_id' => '4', 'name' => [
                'ar' => "سيات",
                'en' => 'Seat'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/seat.png',],
            ['id' => '71', 'categories_id' => '4', 'name' => [
                'ar' => "سيمكا",
                'en' => 'Simca'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/simca.png',],
            ['id' => '72', 'categories_id' => '4', 'name' => [
                'ar' => "شفروليه",
                'en' => 'Chevrolet'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chevrolet.png',],
            ['id' => '73', 'categories_id' => '4', 'name' => [
                'ar' => "شيري",
                'en' => 'Chery'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chery.png',],
            ['id' => '74', 'categories_id' => '4', 'name' => [
                'ar' => "فراري",
                'en' => 'Ferrari'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ferrari.png',],
            ['id' => '75', 'categories_id' => '4', 'name' => [
                'ar' => "فورد",
                'en' => 'Ford'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/ford.png',],
            ['id' => '76', 'categories_id' => '4', 'name' => [
                'ar' => "فولفو",
                'en' => 'Volvo'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Volvo.png',],
            ['id' => '77', 'categories_id' => '4', 'name' => [
                'ar' => "فولكس واجن",
                'en' => 'Volkswagen'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/volkswagen.png',],
            ['id' => '78', 'categories_id' => '4', 'name' => [
                'ar' => "فيات",
                'en' => 'Fiat'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/fiat.png',],
            ['id' => '79', 'categories_id' => '4', 'name' => [
                'ar' => "كاديلاك",
                'en' => 'Cadillac'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/cadillac.png',],
            ['id' => '80', 'categories_id' => '4', 'name' => [
                'ar' => "كرايسلر",
                'en' => 'Chrysler'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/chrysler.png',],
            ['id' => '81', 'categories_id' => '4', 'name' => [
                'ar' => "كلاسيكية",
                'en' => 'Classic'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/classic.png',],
            ['id' => '82', 'categories_id' => '4', 'name' => [
                'ar' => "كيا",
                'en' => 'Kia'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/kia.png',],
            ['id' => '83', 'categories_id' => '4', 'name' => [
                'ar' => "لادا",
                'en' => 'Lada'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lada.png',],
            ['id' => '84', 'categories_id' => '4', 'name' => [
                'ar' => "لامبرغيتي",
                'en' => 'Lamborghini'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lamborghini.png',],
            ['id' => '85', 'categories_id' => '4', 'name' => [
                'ar' => "لاند روفر",
                'en' => 'Landrover'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/landrover.png',],
            ['id' => '86', 'categories_id' => '4', 'name' => [
                'ar' => "لانسيا",
                'en' => 'Lancia'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lancia.png',],
            ['id' => '87', 'categories_id' => '4', 'name' => [
                'ar' => "لكزس",
                'en' => 'Lexus'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lexus.png',],
            ['id' => '88', 'categories_id' => '4', 'name' => [
                'ar' => "لينكولن",
                'en' => 'Lincoln'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/lincoln.png',],
            ['id' => '89', 'categories_id' => '4', 'name' => [
                'ar' => "مازدا",
                'en' => 'Mazda'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mazda.png',],
            ['id' => '90', 'categories_id' => '4', 'name' => [
                'ar' => "مازيراتي",
                'en' => 'Maserati'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/maserati.png',],
            ['id' => '91', 'categories_id' => '4', 'name' => [
                'ar' => "ماكلارين",
                'en' => 'McLaren'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mclaren.png',],
            ['id' => '92', 'categories_id' => '4', 'name' => [
                'ar' => "مرسيدس",
                'en' => 'Mercedes'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mercedes.png',],
            ['id' => '93', 'categories_id' => '4', 'name' => [
                'ar' => "مورغان",
                'en' => 'Morgan'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/morgan.png',],
            ['id' => '94', 'categories_id' => '4', 'name' => [
                'ar' => "ميتسوبيشي",
                'en' => 'Mitsubishi'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/mitsubishi.png',],
            ['id' => '95', 'categories_id' => '4', 'name' => [
                'ar' => "ميركيري",
                'en' => 'Mercury'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Mercury.png',],
            ['id' => '96', 'categories_id' => '4', 'name' => [
                'ar' => "ميني كوبر",
                'en' => 'Mini Cooper'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/minicooper.png',],
            ['id' => '97', 'categories_id' => '4', 'name' => [
                'ar' => "نيسان",
                'en' => 'Nissan'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/nissan.png',],
            ['id' => '98', 'categories_id' => '4', 'name' => [
                'ar' => "همر",
                'en' => 'Hummer'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/Hummer.png',],
            ['id' => '99', 'categories_id' => '4', 'name' => [
                'ar' => "هوندا",
                'en' => 'Honda'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/honda.png',],
            ['id' => '100', 'categories_id' => '4', 'name' => [
                'ar' => "هيونداي",
                'en' => 'Hyundai'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/hyundai.png',],
            ['id' => '101', 'categories_id' => '4', 'name' => [
                'ar' => "متفرقات",
                'en' => 'Miscellaneous'
            ], 'category_name' => 'سيارات', 'src' => 'storage/subcategory/سيارات/general.png',],
            ['id' => '102', 'categories_id' => '1', 'name' => [
                'ar' => "إدارة",
                'en' => 'Management'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/businessperson.png',],
            ['id' => '103', 'categories_id' => '1', 'name' => [
                'ar' => "أزياء",
                'en' => 'Fashion'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/fashion.png',],
            ['id' => '104', 'categories_id' => '1', 'name' => [
                'ar' => "برمجة",
                'en' => 'Programming'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/programmer.png',],
            ['id' => '105', 'categories_id' => '1', 'name' => [
                'ar' => "تزيين وتجميل",
                'en' => 'Decoration and Beauty'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/salon.png',],
            ['id' => '106', 'categories_id' => '1', 'name' => [
                'ar' => "تصميم",
                'en' => 'Design'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Design.png',],
            ['id' => '107', 'categories_id' => '1', 'name' => [
                'ar' => "تعليم وتدريس",
                'en' => 'Education and Teaching'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/learning.png',],
            ['id' => '108', 'categories_id' => '1', 'name' => [
                'ar' => "تقني",
                'en' => 'Technical'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/techengineering.png',],
            ['id' => '109', 'categories_id' => '1', 'name' => [
                'ar' => "حدائق ومناظر",
                'en' => 'Gardens and Landscapes'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Garden.png',],
            ['id' => '110', 'categories_id' => '1', 'name' => [
                'ar' => "حراسة وامن",
                'en' => 'Guard and Security'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/securityguard.png',],
            ['id' => '111', 'categories_id' => '1', 'name' => [
                'ar' => "حرفيين",
                'en' => 'Craftsmen'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/profession.png',],
            ['id' => '112', 'categories_id' => '1', 'name' => [
                'ar' => "محاسبة",
                'en' => 'Accounting'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/accounting.png',],
            ['id' => '113', 'categories_id' => '1', 'name' => [
                'ar' => "حضانة أطفال",
                'en' => 'Childcare'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/childcare.png',],
            ['id' => '114', 'categories_id' => '1', 'name' => [
                'ar' => "خدمة الزبائن",
                'en' => 'Customer Service'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/customercare.png',],
            ['id' => '115', 'categories_id' => '1', 'name' => [
                'ar' => "خياطين",
                'en' => 'Tailors'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Tailors.png',],
            ['id' => '116', 'categories_id' => '1', 'name' => [
                'ar' => "سائق",
                'en' => 'Driver'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/partybus.png',],
            ['id' => '117', 'categories_id' => '1', 'name' => [
                'ar' => "سكرتارية",
                'en' => 'Secretarial'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/secretary.png',],
            ['id' => '118', 'categories_id' => '1', 'name' => [
                'ar' => "سياحة وسفر",
                'en' => 'Tourism and Travel'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/plane.png',],
            ['id' => '119', 'categories_id' => '1', 'name' => [
                'ar' => "سياحة ومطاعم",
                'en' => 'Tourism and Restaurants'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Restaurant.png',],
            ['id' => '120', 'categories_id' => '1', 'name' => [
                'ar' => "شراكة",
                'en' => 'Partnership'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/copartnership.png',],
            ['id' => '121', 'categories_id' => '1', 'name' => [
                'ar' => "طب وتمريض",
                'en' => 'Medicine and Nursing'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Medicine.png',],
            ['id' => '122', 'categories_id' => '1', 'name' => [
                'ar' => "علاقات عامة",
                'en' => 'Public Relations'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/publicrelations.png',],
            ['id' => '123', 'categories_id' => '1', 'name' => [
                'ar' => "عمال",
                'en' => 'Laborers'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Workers.png',],
            ['id' => '124', 'categories_id' => '1', 'name' => [
                'ar' => "عمال تنظيف",
                'en' => 'Cleaning Workers'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/cleaning.png',],
            ['id' => '125', 'categories_id' => '1', 'name' => [
                'ar' => "عمال ديلفري",
                'en' => 'Delivery Workers'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/delivery.png',],
            ['id' => '126', 'categories_id' => '1', 'name' => [
                'ar' => "عمالة منزلية",
                'en' => 'Household Workers'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/homecleaning.png',],
            ['id' => '127', 'categories_id' => '1', 'name' => [
                'ar' => "فنون جميلة",
                'en' => 'Fine Arts'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/paintbrush.png',],
            ['id' => '128', 'categories_id' => '1', 'name' => [
                'ar' => "كمبيوتر وشبكات",
                'en' => 'Computer and Networks'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/network.png',],
            ['id' => '129', 'categories_id' => '1', 'name' => [
                'ar' => "لياقة بدنية",
                'en' => 'Fitness'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/gym.png',],
            ['id' => '130', 'categories_id' => '1', 'name' => [
                'ar' => "مبيعات وتسويق",
                'en' => 'Sales and Marketing'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/marketing.png',],
            ['id' => '131', 'categories_id' => '1', 'name' => [
                'ar' => "مترجمين",
                'en' => 'Translators'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Translation.png',],
            ['id' => '132', 'categories_id' => '1', 'name' => [
                'ar' => "محاماه وقانون",
                'en' => 'Law and Legal'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/lawyer.png',],
            ['id' => '133', 'categories_id' => '1', 'name' => [
                'ar' => "محررين",
                'en' => 'Editors'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/editing.png',],
            ['id' => '134', 'categories_id' => '1', 'name' => [
                'ar' => "مدخل بيانات",
                'en' => 'Data Entry'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/dataentry.png',],
            ['id' => '135', 'categories_id' => '1', 'name' => [
                'ar' => "مقاولات",
                'en' => 'Construction'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Construction.png',],
            ['id' => '136', 'categories_id' => '1', 'name' => [
                'ar' => "مهندس",
                'en' => 'Engineer'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Engineer.png',],
            ['id' => '137', 'categories_id' => '1', 'name' => [
                'ar' => "موظفين",
                'en' => 'Employees'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/employees.png',],
            ['id' => '138', 'categories_id' => '1', 'name' => [
                'ar' => "مونتاج وإخراج",
                'en' => 'Montage and Editing'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/videography.png',],
            ['id' => '139', 'categories_id' => '1', 'name' => [
                'ar' => "متفرقات",
                'en' => 'Miscellaneous'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/job.png',],
            ['id' => '140', 'categories_id' => '5', 'name' => [
                'ar' => "اتصالات",
                'en' => 'Communications'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/communication.png',],
            ['id' => '141', 'categories_id' => '5', 'name' => [
                'ar' => "ارقام مميزة",
                'en' => 'Special Numbers'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/numberstar.png',],
            ['id' => '142', 'categories_id' => '5', 'name' => [
                'ar' => "اعمال واستثمار",
                'en' => 'Business and Investment'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/investor.png',],
            ['id' => '143', 'categories_id' => '5', 'name' => [
                'ar' => "اكسسورات",
                'en' => 'Accessories'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/queen.png',],
            ['id' => '218', 'categories_id' => '5', 'name' => [
                'ar' => "صرافة",
                'en' => 'Currency Exchange'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/investor.png',],
            ['id' => '219', 'categories_id' => '5', 'name' => [
                'ar' => "سياحة وسفر",
                'en' => 'Tourism and Travel'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/plane.png',],
            ['id' => '144', 'categories_id' => '5', 'name' => [
                'ar' => "العاب",
                'en' => 'Games'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/games.png',],
            ['id' => '145', 'categories_id' => '5', 'name' => [
                'ar' => "الكترونيات",
                'en' => 'Electronics'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/electronics.png',],
            ['id' => '146', 'categories_id' => '5', 'name' => [
                'ar' => "اليات ثقيلة",
                'en' => 'Heavy Equipment'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/heavy.png',],
            ['id' => '147', 'categories_id' => '5', 'name' => [
                'ar' => "أنشطة",
                'en' => 'Activities'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/activity.png',],
            ['id' => '148', 'categories_id' => '5', 'name' => [
                'ar' => "امن وحماية",
                'en' => 'Security and Protection'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/camera.png',],
            ['id' => '149', 'categories_id' => '5', 'name' => [
                'ar' => "بيت متنقل",
                'en' => 'Mobile Homes'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/pngtree.png',],
            ['id' => '150', 'categories_id' => '5', 'name' => [
                'ar' => "تجهيز وتشطيب",
                'en' => 'Furnishing and Finishing'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/silhouette.png',],
            ['id' => '151', 'categories_id' => '5', 'name' => [
                'ar' => "تحف ومقتنيات",
                'en' => 'Antiques and Collectibles'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/decoration.png',],
            ['id' => '152', 'categories_id' => '5', 'name' => [
                'ar' => "تخليص معاملات",
                'en' => 'Clearance and Transactions'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/businessman.png',],
            ['id' => '153', 'categories_id' => '5', 'name' => [
                'ar' => "تراخيص",
                'en' => 'Licenses'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/license.png',],
            ['id' => '154', 'categories_id' => '5', 'name' => [
                'ar' => "توصيل ونقل",
                'en' => 'Delivery and Transportation'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/truck.png',],
            ['id' => '155', 'categories_id' => '5', 'name' => [
                'ar' => "حواسيب وصيانة",
                'en' => 'Computers and Maintenance'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maintenance.png',],
            ['id' => '156', 'categories_id' => '5', 'name' => [
                'ar' => "حيوانات",
                'en' => 'Pets'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Pets.png',],
            ['id' => '157', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات الصيانة",
                'en' => 'Maintenance Services'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maintenancess.png',],
            ['id' => '158', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات زراعية",
                'en' => 'Agricultural Services'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/agriculture.png',],
            ['id' => '159', 'categories_id' => '5', 'name' => [
                'ar' => "خدمات عقارية",
                'en' => 'Real Estate Services'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/building.png',],
            ['id' => '161', 'categories_id' => '5', 'name' => [
                'ar' => "ساعات",
                'en' => 'Watches'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/watch.png',],
            ['id' => '162', 'categories_id' => '5', 'name' => [
                'ar' => "شحن",
                'en' => 'Shipping'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/shipping.png',],
            ['id' => '163', 'categories_id' => '5', 'name' => [
                'ar' => "صيد سمك",
                'en' => 'Fishing'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/fishing.png',],
            ['id' => '164', 'categories_id' => '5', 'name' => [
                'ar' => "طبخ منزلي",
                'en' => 'Home Cooking'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/cooking.png',],
            ['id' => '165', 'categories_id' => '5', 'name' => [
                'ar' => "عروض بالجملة",
                'en' => 'Wholesale Offers'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/offers.png',],
            ['id' => '166', 'categories_id' => '5', 'name' => [
                'ar' => "عطورات",
                'en' => 'Perfumes'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/parfume.png',],
            ['id' => '167', 'categories_id' => '5', 'name' => [
                'ar' => "كتب ومجلات",
                'en' => 'Books and Magazines'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Books.png',],
            ['id' => '168', 'categories_id' => '5', 'name' => [
                'ar' => "مستحضرات تجميل",
                'en' => 'Beauty Products'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/skincare.png',],
            ['id' => '169', 'categories_id' => '5', 'name' => [
                'ar' => "معدات بحرية",
                'en' => 'Maritime Equipment'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/maritime.png',],
            ['id' => '170', 'categories_id' => '5', 'name' => [
                'ar' => "معدات رياضية",
                'en' => 'Sporting Equipment'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/sportds.png',],
            ['id' => '171', 'categories_id' => '5', 'name' => [
                'ar' => "مفروشات",
                'en' => 'Furniture'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Furnitures.png',],
            ['id' => '172', 'categories_id' => '5', 'name' => [
                'ar' => "مفقودات",
                'en' => 'Lost and Found'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/miscellaneous.png',],
            ['id' => '173', 'categories_id' => '5', 'name' => [
                'ar' => "ملابس",
                'en' => 'Clothing'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/clothes.png',],
            ['id' => '174', 'categories_id' => '5', 'name' => [
                'ar' => "مواد بناء",
                'en' => 'Building Materials'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/renovation.png',],
            ['id' => '175', 'categories_id' => '5', 'name' => [
                'ar' => "مواد غذائية",
                'en' => 'Food Products'
            ], 'category_name' => 'خدمات', 'src' => 'storage/subcategory/خدمات/Goodfood.png',],
            ['id' => '176', 'categories_id' => '2', 'name' => [
                'ar' => "مولدات كهرباء",
                'en' => 'Generators'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/Generators.jpeg',],
            ['id' => '177', 'categories_id' => '2', 'name' => [
                'ar' => "هواتف ذكية",
                'en' => 'Smartphones'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/phones.png',],
            ['id' => '178', 'categories_id' => '2', 'name' => [
                'ar' => "كاميرات",
                'en' => 'Cameras'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/camerapencil.png',],
            ['id' => '179', 'categories_id' => '2', 'name' => [
                'ar' => "تلفزيون",
                'en' => 'Televisions'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/televisions.png',],
            ['id' => '180', 'categories_id' => '2', 'name' => [
                'ar' => "غسالة",
                'en' => 'Washing Machine'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/dishwasher.png',],
            ['id' => '181', 'categories_id' => '2', 'name' => [
                'ar' => "ثلاجة",
                'en' => 'Refrigerator'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Refrigerator.png',],
            ['id' => '182', 'categories_id' => '2', 'name' => [
                'ar' => "عجانة",
                'en' => 'Mixer'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Mixer.png',],
            ['id' => '183', 'categories_id' => '2', 'name' => [
                'ar' => "فرن الغاز",
                'en' => 'Gas Stove'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/stovegas.png',],
            ['id' => '184', 'categories_id' => '2', 'name' => [
                'ar' => "ميكرويف",
                'en' => 'Microwave'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Microwave.png',],
            ['id' => '185', 'categories_id' => '2', 'name' => [
                'ar' => "مكواه",
                'en' => 'Iron'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/steamiron.png',],
            ['id' => '186', 'categories_id' => '2', 'name' => [
                'ar' => "مروحة",
                'en' => 'Fan'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Fan.png',],
            ['id' => '187', 'categories_id' => '2', 'name' => [
                'ar' => "المكيفات",
                'en' => 'Air Conditioners'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/evaporative.png',],
            ['id' => '188', 'categories_id' => '2', 'name' => [
                'ar' => "الخلاط",
                'en' => 'Blender'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/Blender-Mixe.png',],
            ['id' => '189', 'categories_id' => '2', 'name' => [
                'ar' => "مكنسة كهربائية",
                'en' => 'Vacuum Cleaner'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/vacuum-cleaner.png',],
            ['id' => '190', 'categories_id' => '2', 'name' => [
                'ar' => "الطابعات",
                'en' => 'Printers'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/printer.png',],
            ['id' => '191', 'categories_id' => '2', 'name' => [
                'ar' => "السماعات",
                'en' => 'Headphones'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/headphones.png',],
            ['id' => '192', 'categories_id' => '2', 'name' => [
                'ar' => "أجهزة طبية",
                'en' => 'Medical Devices'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/medicaldevices.png',],
            ['id' => '193', 'categories_id' => '2', 'name' => [
                'ar' => "أجهزة منزلية",
                'en' => 'Home Appliances'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/خدمات/appliances.png',],
            ['id' => '194', 'categories_id' => '2', 'name' => [
                'ar' => "اجهزة اخرى",
                'en' => 'Other Devices'
            ], 'category_name' => 'اجهزة', 'src' => 'storage/subcategory/اجهزة/appliances.png',],
            ['id' => '195', 'categories_id' => '1', 'name' => [
                'ar' => "موارد بشرية",
                'en' => 'Human Resources'
            ], 'category_name' => 'وظائف', 'src' => 'storage/subcategory/وظائف/Humanresources.png',],
            ['id' => '196', 'categories_id' => '7', 'name' => [
                'ar' => "عاجل",
                'en' => 'Urgent News'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/uragent.jpg',],
            ['id' => '197', 'categories_id' => '7', 'name' => [
                'ar' => "غزة",
                'en' => 'Gaza'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/gaza.png',],
            ['id' => '198', 'categories_id' => '7', 'name' => [
                'ar' => "الضفة",
                'en' => 'West Bank'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/west_bank.png',],
            ['id' => '199', 'categories_id' => '7', 'name' => [
                'ar' => "القدس",
                'en' => 'Jerusalem'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/jorasalim.png',],
            ['id' => '200', 'categories_id' => '7', 'name' => [
                'ar' => "اراضي ال 48",
                'en' => '48 Territories'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/land48.png',],
            ['id' => '201', 'categories_id' => '7', 'name' => [
                'ar' => "في الخارج",
                'en' => 'International News'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/outside.png',],
            ['id' => '202', 'categories_id' => '7', 'name' => [
                'ar' => "سياسة",
                'en' => 'Politics'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/politcal.png',],
            ['id' => '203', 'categories_id' => '7', 'name' => [
                'ar' => "اقتصاد",
                'en' => 'Economy'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/economy.jpg',],
            ['id' => '204', 'categories_id' => '7', 'name' => [
                'ar' => "اسرائيليات",
                'en' => 'Israeli Affairs'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/isreal.png',],
            ['id' => '205', 'categories_id' => '7', 'name' => [
                'ar' => "الصحة",
                'en' => 'Health'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/Health.png',],
            ['id' => '206', 'categories_id' => '7', 'name' => [
                'ar' => "التعليم",
                'en' => 'Education'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/education.png',],
            ['id' => '207', 'categories_id' => '7', 'name' => [
                'ar' => "التكنولجيا",
                'en' => 'Technology'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/technology.jpg',],
            ['id' => '208', 'categories_id' => '7', 'name' => [
                'ar' => "محلي",
                'en' => 'Local News'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/breaking.jpg',],
            ['id' => '209', 'categories_id' => '7', 'name' => [
                'ar' => "عربي",
                'en' => 'Arabic News'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/arabic.png',],
            ['id' => '210', 'categories_id' => '7', 'name' => [
                'ar' => "دولي",
                'en' => 'International'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/glob.png',],
            ['id' => '211', 'categories_id' => '7', 'name' => [
                'ar' => "المجتمع",
                'en' => 'Society'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/society.png',],
            ['id' => '212', 'categories_id' => '7', 'name' => [
                'ar' => "اعمال وشركات",
                'en' => 'Business and Companies'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/marketing.png',],
            ['id' => '213', 'categories_id' => '7', 'name' => [
                'ar' => "سفر وسياحة",
                'en' => 'Travel and Tourism'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/airplane.jpg',],
            ['id' => '214', 'categories_id' => '7', 'name' => [
                'ar' => "ثقافة وفنون",
                'en' => 'Culture and Arts'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/camerapencil.png',],
            ['id' => '215', 'categories_id' => '7', 'name' => [
                'ar' => "ترفيه",
                'en' => 'Entertainment'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/celebration.png',],
            ['id' => '216', 'categories_id' => '7', 'name' => [
                'ar' => "منوعات",
                'en' => 'Miscellaneous'
            ], 'category_name' => 'اخبار', 'src' => 'storage/subcategory/اخبار/news.png',],
        ];

        foreach ($subCategories as $subCategory) {
            $subCategoryData = Sub_categories::create([
                'id' => $subCategory['id'],
                'name' => $subCategory['name'],
                'categories_id' => $subCategory['categories_id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $photo = new Photos([
                'src' => $subCategory['src'],
            ]);
            $subCategoryData->photos()->save($photo);
        }
    }
}
