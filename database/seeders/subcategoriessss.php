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

            ['sub_categories_id' => '1', 'categories_id' => '3', 'sub_category' => 'أراضي', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '2', 'categories_id' => '3', 'sub_category' => 'استديو', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '3', 'categories_id' => '3', 'sub_category' => 'بيت شعبي', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '4', 'categories_id' => '3', 'sub_category' => 'سكن عمال', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '5', 'categories_id' => '3', 'sub_category' => 'شاليهات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '6', 'categories_id' => '3', 'sub_category' => 'شقق', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '7', 'categories_id' => '3', 'sub_category' => 'شقق مفروشة', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '8', 'categories_id' => '3', 'sub_category' => 'دولية', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '9', 'categories_id' => '3', 'sub_category' => 'عمارات ومباني', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '10', 'categories_id' => '3', 'sub_category' => 'فلل ومنازل', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '11', 'categories_id' => '3', 'sub_category' => 'فنادق ومنتجعات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '12', 'categories_id' => '3', 'sub_category' => 'قاعات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '13', 'categories_id' => '3', 'sub_category' => 'قيد الانشاء', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '14', 'categories_id' => '3', 'sub_category' => 'مجمعات تجارية', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '15', 'categories_id' => '3', 'sub_category' => 'محلات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '16', 'categories_id' => '3', 'sub_category' => 'مزارع', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '17', 'categories_id' => '3', 'sub_category' => 'مستودعات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '18', 'categories_id' => '3', 'sub_category' => 'مشاركة سكن', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '19', 'categories_id' => '3', 'sub_category' => 'مصانع', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '20', 'categories_id' => '3', 'sub_category' => 'مطاعم ومقاهي', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '21', 'categories_id' => '3', 'sub_category' => 'مكاتب', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '22', 'categories_id' => '3', 'sub_category' => 'متفرقات', 'category' => 'عقارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '23', 'categories_id' => '4', 'sub_category' => 'استيراد سيارات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '24', 'categories_id' => '4', 'sub_category' => 'قطاع غيار', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '25', 'categories_id' => '4', 'sub_category' => 'خدمات السيارات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '26', 'categories_id' => '4', 'sub_category' => 'اكسسورات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '27', 'categories_id' => '4', 'sub_category' => 'تاجير سيارات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '28', 'categories_id' => '4', 'sub_category' => 'ارقام سيارات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '29', 'categories_id' => '4', 'sub_category' => 'استون مارتن', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '30', 'categories_id' => '4', 'sub_category' => 'اسيا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '31', 'categories_id' => '4', 'sub_category' => 'الفا روميو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '32', 'categories_id' => '4', 'sub_category' => 'ام جي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '33', 'categories_id' => '4', 'sub_category' => 'انفينيتي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '34', 'categories_id' => '4', 'sub_category' => 'اوبل', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '35', 'categories_id' => '4', 'sub_category' => 'اودي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '36', 'categories_id' => '4', 'sub_category' => 'اوستن', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '37', 'categories_id' => '4', 'sub_category' => 'اولدس موبيل', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '38', 'categories_id' => '4', 'sub_category' => 'ايسوزي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '39', 'categories_id' => '4', 'sub_category' => 'بروتن', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '40', 'categories_id' => '4', 'sub_category' => 'بليموث', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '41', 'categories_id' => '4', 'sub_category' => 'بنتلي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '42', 'categories_id' => '4', 'sub_category' => 'بورش', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '43', 'categories_id' => '4', 'sub_category' => 'بوغاتي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '44', 'categories_id' => '4', 'sub_category' => 'بويك', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '45', 'categories_id' => '4', 'sub_category' => 'بي ام دبليو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '46', 'categories_id' => '4', 'sub_category' => 'بيجو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '47', 'categories_id' => '4', 'sub_category' => 'تاتا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '48', 'categories_id' => '4', 'sub_category' => 'تويوتا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '49', 'categories_id' => '4', 'sub_category' => 'جاك', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '50', 'categories_id' => '4', 'sub_category' => 'جاكوار', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '51', 'categories_id' => '4', 'sub_category' => 'جي ام سي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '52', 'categories_id' => '4', 'sub_category' => 'جيب', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '53', 'categories_id' => '4', 'sub_category' => 'داتسون', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '54', 'categories_id' => '4', 'sub_category' => 'داسيا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '55', 'categories_id' => '4', 'sub_category' => 'دايهاتسو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '56', 'categories_id' => '4', 'sub_category' => 'دايوو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '57', 'categories_id' => '4', 'sub_category' => 'دفسك', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '58', 'categories_id' => '4', 'sub_category' => 'دودج', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '59', 'categories_id' => '4', 'sub_category' => 'روفر', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '60', 'categories_id' => '4', 'sub_category' => 'رولز رويس', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '61', 'categories_id' => '4', 'sub_category' => 'رينو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '62', 'categories_id' => '4', 'sub_category' => 'ساب', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '63', 'categories_id' => '4', 'sub_category' => 'سابا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '64', 'categories_id' => '4', 'sub_category' => 'سانغيونغ', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '65', 'categories_id' => '4', 'sub_category' => 'ستروين', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '66', 'categories_id' => '4', 'sub_category' => 'سكودا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '67', 'categories_id' => '4', 'sub_category' => 'سمارت', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '68', 'categories_id' => '4', 'sub_category' => 'سوبارو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '69', 'categories_id' => '4', 'sub_category' => 'سوزوكي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '70', 'categories_id' => '4', 'sub_category' => 'سيات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '71', 'categories_id' => '4', 'sub_category' => 'سيمكا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '72', 'categories_id' => '4', 'sub_category' => 'شفروليه', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '73', 'categories_id' => '4', 'sub_category' => 'شيري', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '74', 'categories_id' => '4', 'sub_category' => 'فراري', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '75', 'categories_id' => '4', 'sub_category' => 'فورد', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '76', 'categories_id' => '4', 'sub_category' => 'فولفو', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '77', 'categories_id' => '4', 'sub_category' => 'فولكس واجن', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '78', 'categories_id' => '4', 'sub_category' => 'فيات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '79', 'categories_id' => '4', 'sub_category' => 'كاديلاك', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '80', 'categories_id' => '4', 'sub_category' => 'كرايسلر', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '81', 'categories_id' => '4', 'sub_category' => 'كلاسيكية', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '82', 'categories_id' => '4', 'sub_category' => 'كيا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '83', 'categories_id' => '4', 'sub_category' => 'لادا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '84', 'categories_id' => '4', 'sub_category' => 'لامبرغيتي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '85', 'categories_id' => '4', 'sub_category' => 'لاند روفر', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '86', 'categories_id' => '4', 'sub_category' => 'لانسيا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '87', 'categories_id' => '4', 'sub_category' => 'لكزس', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '88', 'categories_id' => '4', 'sub_category' => 'لينكولن', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '89', 'categories_id' => '4', 'sub_category' => 'مازدا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '90', 'categories_id' => '4', 'sub_category' => 'مازيراتي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '91', 'categories_id' => '4', 'sub_category' => 'ماكلارين', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '92', 'categories_id' => '4', 'sub_category' => 'مرسيدس', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '93', 'categories_id' => '4', 'sub_category' => 'مورغان', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '94', 'categories_id' => '4', 'sub_category' => 'ميتسوبيشي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '95', 'categories_id' => '4', 'sub_category' => 'ميركيري', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '96', 'categories_id' => '4', 'sub_category' => 'ميني كوبر', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '97', 'categories_id' => '4', 'sub_category' => 'نيسان', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '98', 'categories_id' => '4', 'sub_category' => 'همر', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '99', 'categories_id' => '4', 'sub_category' => 'هوندا', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '100', 'categories_id' => '4', 'sub_category' => 'هيونداي', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '101', 'categories_id' => '4', 'sub_category' => 'متفرقات', 'category' => 'سيارات', "title" => "", "description" => "",],
            ['sub_categories_id' => '102', 'categories_id' => '1', 'sub_category' => 'إدارة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '103', 'categories_id' => '1', 'sub_category' => 'أزياء', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '104', 'categories_id' => '1', 'sub_category' => 'برمجة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '105', 'categories_id' => '1', 'sub_category' => 'تزيين وتجميل', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '106', 'categories_id' => '1', 'sub_category' => 'تصميم', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '107', 'categories_id' => '1', 'sub_category' => 'تعليم وتدريس', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '108', 'categories_id' => '1', 'sub_category' => 'تقني', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '109', 'categories_id' => '1', 'sub_category' => 'حدائق ومناظر', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '110', 'categories_id' => '1', 'sub_category' => 'حراسة وامن', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '111', 'categories_id' => '1', 'sub_category' => 'حرفيين', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '112', 'categories_id' => '1', 'sub_category' => 'محاسبة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '113', 'categories_id' => '1', 'sub_category' => 'حضانة أطفال', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '114', 'categories_id' => '1', 'sub_category' => 'خدمة الزبائن', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '115', 'categories_id' => '1', 'sub_category' => 'خياطين', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '116', 'categories_id' => '1', 'sub_category' => 'سائق', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '117', 'categories_id' => '1', 'sub_category' => 'سكرتارية', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '118', 'categories_id' => '1', 'sub_category' => 'سياحة وسفر', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '119', 'categories_id' => '1', 'sub_category' => 'سياحة ومطاعم', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '120', 'categories_id' => '1', 'sub_category' => 'شراكة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '121', 'categories_id' => '1', 'sub_category' => 'طب وتمريض', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '122', 'categories_id' => '1', 'sub_category' => 'علاقات عامة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '123', 'categories_id' => '1', 'sub_category' => 'عمال', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '124', 'categories_id' => '1', 'sub_category' => 'عمال تنظيف', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '125', 'categories_id' => '1', 'sub_category' => 'عمال ديلفري', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '126', 'categories_id' => '1', 'sub_category' => 'عمالة منزلية', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '127', 'categories_id' => '1', 'sub_category' => 'فنون جميلة', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '128', 'categories_id' => '1', 'sub_category' => 'كمبيوتر وشبكات', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '129', 'categories_id' => '1', 'sub_category' => 'لياقة بدنية', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '130', 'categories_id' => '1', 'sub_category' => 'مبيعات وتسويق', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '131', 'categories_id' => '1', 'sub_category' => 'مترجمين', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '132', 'categories_id' => '1', 'sub_category' => 'محاماه وقانون', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '133', 'categories_id' => '1', 'sub_category' => 'محررين', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '134', 'categories_id' => '1', 'sub_category' => 'مدخل بيانات', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '135', 'categories_id' => '1', 'sub_category' => 'مقاولات', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '136', 'categories_id' => '1', 'sub_category' => 'مهندس', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '136', 'categories_id' => '1', 'sub_category' => 'موارد بشرية', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '137', 'categories_id' => '1', 'sub_category' => 'موظفين', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '138', 'categories_id' => '1', 'sub_category' => 'مونتاج وإخراج', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '139', 'categories_id' => '1', 'sub_category' => 'متفرقات', 'category' => 'وظائف', "title" => "", "description" => "",],
            ['sub_categories_id' => '140', 'categories_id' => '5', 'sub_category' => 'اتصالات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '141', 'categories_id' => '5', 'sub_category' => 'ارقام مميزة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '142', 'categories_id' => '5', 'sub_category' => 'اعمال واستثمار', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '143', 'categories_id' => '5', 'sub_category' => 'اكسسورات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '144', 'categories_id' => '5', 'sub_category' => 'العاب', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '145', 'categories_id' => '5', 'sub_category' => 'الكترونيات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '146', 'categories_id' => '5', 'sub_category' => 'اليات ثقيلة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '147', 'categories_id' => '5', 'sub_category' => 'أنشطة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '148', 'categories_id' => '5', 'sub_category' => 'أنظمة امن وحماية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '149', 'categories_id' => '5', 'sub_category' => 'بيت متنقل', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '150', 'categories_id' => '5', 'sub_category' => 'تجهيزات وتشطيبات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '151', 'categories_id' => '5', 'sub_category' => 'تحف ومقتنيات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '152', 'categories_id' => '5', 'sub_category' => 'تخليص معاملات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '153', 'categories_id' => '5', 'sub_category' => 'تراخيص', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '154', 'categories_id' => '5', 'sub_category' => 'توصيل ونقل', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '155', 'categories_id' => '5', 'sub_category' => 'حواسيب وصيانة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '156', 'categories_id' => '5', 'sub_category' => 'حيوانات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '157', 'categories_id' => '5', 'sub_category' => 'الصيانة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '158', 'categories_id' => '5', 'sub_category' => 'زراعية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '159', 'categories_id' => '5', 'sub_category' => 'عقارية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '160', 'categories_id' => '5', 'sub_category' => 'دراجات نارية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '161', 'categories_id' => '5', 'sub_category' => 'ساعات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '162', 'categories_id' => '5', 'sub_category' => 'شحن', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '163', 'categories_id' => '5', 'sub_category' => 'صيد سمك', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '164', 'categories_id' => '5', 'sub_category' => 'طبخ منزلي', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '165', 'categories_id' => '5', 'sub_category' => 'عروض بالجملة', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '166', 'categories_id' => '5', 'sub_category' => 'عطورات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '167', 'categories_id' => '5', 'sub_category' => 'كتب ومجلات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '168', 'categories_id' => '5', 'sub_category' => 'مستحضرات تجميل', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '169', 'categories_id' => '5', 'sub_category' => 'معدات بحرية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '170', 'categories_id' => '5', 'sub_category' => 'معدات رياضية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '171', 'categories_id' => '5', 'sub_category' => 'مفروشات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '172', 'categories_id' => '5', 'sub_category' => 'مفقودات', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '173', 'categories_id' => '5', 'sub_category' => 'ملابس', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '174', 'categories_id' => '5', 'sub_category' => 'مواد بناء', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '175', 'categories_id' => '5', 'sub_category' => 'مواد غذائية', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '176', 'categories_id' => '5', 'sub_category' => 'مولدات كهرباء', 'category' => 'خدمات', "title" => "", "description" => "",],
            ['sub_categories_id' => '177', 'categories_id' => '5', 'sub_category' => 'هواتف ذكية', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '178', 'categories_id' => '5', 'sub_category' => 'كاميرات', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '179', 'categories_id' => '2', 'sub_category' => 'تلفزيون', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '180', 'categories_id' => '2', 'sub_category' => 'غسالة', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '181', 'categories_id' => '2', 'sub_category' => 'ثلاجة', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '182', 'categories_id' => '2', 'sub_category' => 'عجانة', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '183', 'categories_id' => '2', 'sub_category' => 'فرن الغاز', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '184', 'categories_id' => '2', 'sub_category' => 'ميكرويف', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '185', 'categories_id' => '2', 'sub_category' => 'مكواه', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '186', 'categories_id' => '2', 'sub_category' => 'مروحة', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '187', 'categories_id' => '2', 'sub_category' => 'المكيفات', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '188', 'categories_id' => '2', 'sub_category' => 'الخلاط', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '189', 'categories_id' => '2', 'sub_category' => 'مكنسة كهربائية', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '190', 'categories_id' => '2', 'sub_category' => 'الطابعات', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '191', 'categories_id' => '2', 'sub_category' => 'السماعات', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '192', 'categories_id' => '2', 'sub_category' => 'طبية', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '193', 'categories_id' => '2', 'sub_category' => 'منزلية', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '194', 'categories_id' => '2', 'sub_category' => 'اخرى', 'category' => 'اجهزة', "title" => "", "description" => "",],
            ['sub_categories_id' => '195', 'categories_id' => '6', 'sub_category' => 'اخرى', 'category' => 'اخبار', "title" => "", "description" => "",],
            ['sub_categories_id' => '195', 'categories_id' => '6', 'sub_category' => 'اخرى', 'category' => 'اخبار', "title" => "", "description" => "",],
            ['sub_categories_id' => '196','categories_id' => '6','sub_category' => 'عاجل', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '197','categories_id' => '6','sub_category' => 'غزة', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '198','categories_id' => '6','sub_category' => 'الضفة', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '199','categories_id' => '6','sub_category' => 'القدس', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '200','categories_id' => '6','sub_category' => 'اراضي ال 48', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '201','categories_id' => '6','sub_category' => 'في الخارج', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '202','categories_id' => '6','sub_category' => 'سياسة', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '203','categories_id' => '6','sub_category' => 'اقتصاد', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '204','categories_id' => '6','sub_category' => 'اسرائيليات', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '205','categories_id' => '6','sub_category' => 'الصحة', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '206','categories_id' => '6','sub_category' => 'التعليم', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '207','categories_id' => '6','sub_category' => 'التكنولجيا', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '208','categories_id' => '6','sub_category' => 'محلي', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '209','categories_id' => '6','sub_category' => 'عربي', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '210','categories_id' => '6','sub_category' => 'دولي', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '211','categories_id' => '6','sub_category' => 'المجتمع', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '212','categories_id' => '6','sub_category' => 'اعمال وشركات', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '213','categories_id' => '6','sub_category' => 'سفر وسياحة', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '214','categories_id' => '6','sub_category' => 'ثقافة وفنون', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '215','categories_id' => '6','sub_category' => 'ترفيه', 'category' => 'اخبار',"title" => "", "description" => "",],
            ['sub_categories_id' => '216','categories_id' => '6','sub_category' => 'منوعات', 'category' => 'اخبار',"title" => "", "description" => "",],

        ];
    }
}
