<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class SeoTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'tr' => [
                'home_title' => 'Talabna - Arap Dünyasının En Büyük İlan Pazarı',
                'home_description' => 'Araba, emlak, iş ilanı, elektronik ve daha fazlasında binlerce sınıflandırılmış ilan keşfedin. Talabna ile kolayca ve güvenle alın satın.',
                'home_keywords' => 'sınıflandırılmış ilanlar, satılık arabalar, emlak, iş ilanları, telefonlar, alım satım',
            ],
            'fr' => [
                'home_title' => 'Talabna - La Plus Grande Place de Marché du Monde Arabe',
                'home_description' => 'Découvrez des milliers de petites annonces : voitures, immobilier, emplois, électronique et plus. Achetez et vendez facilement sur Talabna.',
                'home_keywords' => 'petites annonces, voitures à vendre, immobilier, emplois, téléphones, achat vente',
            ],
            'es' => [
                'home_title' => 'Talabna - El Mayor Mercado de Clasificados del Mundo Árabe',
                'home_description' => 'Descubre miles de anuncios clasificados en coches, inmuebles, empleos, electrónica y más. Compra y vende fácilmente en Talabna.',
                'home_keywords' => 'anuncios clasificados, coches en venta, inmuebles, empleos, teléfonos, compra venta',
            ],
            'hi' => [
                'home_title' => 'Talabna - अरब दुनिया का सबसे बड़ा क्लासीफाइड मार्केटप्लेस',
                'home_description' => 'कार, रियल एस्टेट, नौकरी, इलेक्ट्रॉनिक्स और अन्य में हजारों क्लासीफाइड विज्ञापन खोजें। Talabna पर आसानी और सुरक्षा से खरीदें और बेचें।',
                'home_keywords' => 'क्लासीफाइड विज्ञापन, बिक्री के लिए कार, रियल एस्टेट, नौकरियां, फोन, खरीद बिक्री',
            ],
            'ur' => [
                'home_title' => 'طلبنا - عرب دنیا کا سب سے بڑا کلاسیفائیڈ مارکیٹ پلیس',
                'home_description' => 'گاڑیاں، جائیداد، نوکریاں، الیکٹرانکس اور مزید میں ہزاروں کلاسیفائیڈ اشتہارات دریافت کریں۔ طلبنا پر آسانی اور محفوظ طریقے سے خریدیں اور بیچیں۔',
                'home_keywords' => 'کلاسیفائیڈ اشتہارات, فروخت کے لیے گاڑیاں, جائیداد, نوکریاں, فون, خرید و فروخت',
            ],
            'bn' => [
                'home_title' => 'Talabna - আরব বিশ্বের সবচেয়ে বড় ক্লাসিফাইড মার্কেটপ্লেস',
                'home_description' => 'গাড়ি, রিয়েল এস্টেট, চাকরি, ইলেকট্রনিক্স এবং আরও অনেক কিছুতে হাজার হাজার ক্লাসিফাইড বিজ্ঞাপন আবিষ্কার করুন।',
                'home_keywords' => 'ক্লাসিফাইড বিজ্ঞাপন, বিক্রির জন্য গাড়ি, রিয়েল এস্টেট, চাকরি, ফোন',
            ],
            'pt' => [
                'home_title' => 'Talabna - O Maior Marketplace de Classificados do Mundo Árabe',
                'home_description' => 'Descubra milhares de anúncios classificados em carros, imóveis, empregos, eletrônicos e mais. Compre e venda com facilidade no Talabna.',
                'home_keywords' => 'anúncios classificados, carros à venda, imóveis, empregos, telefones, compra e venda',
            ],
            'ru' => [
                'home_title' => 'Talabna - Крупнейшая Площадка Объявлений Арабского Мира',
                'home_description' => 'Откройте тысячи объявлений: автомобили, недвижимость, вакансии, электроника и многое другое. Покупайте и продавайте легко на Talabna.',
                'home_keywords' => 'объявления, продажа автомобилей, недвижимость, вакансии, телефоны, купля продажа',
            ],
            'id' => [
                'home_title' => 'Talabna - Pasar Iklan Baris Terbesar di Dunia Arab',
                'home_description' => 'Temukan ribuan iklan baris untuk mobil, properti, lowongan kerja, elektronik dan lainnya. Jual beli dengan mudah dan aman di Talabna.',
                'home_keywords' => 'iklan baris, mobil dijual, properti, lowongan kerja, telepon, jual beli',
            ],
            'de' => [
                'home_title' => 'Talabna - Der Größte Kleinanzeigenmarkt der Arabischen Welt',
                'home_description' => 'Entdecken Sie Tausende von Kleinanzeigen für Autos, Immobilien, Jobs, Elektronik und mehr. Kaufen und verkaufen Sie einfach auf Talabna.',
                'home_keywords' => 'Kleinanzeigen, Autos kaufen, Immobilien, Stellenangebote, Handys, Kaufen Verkaufen',
            ],
            'zh' => [
                'home_title' => 'Talabna - 阿拉伯世界最大的分类广告市场',
                'home_description' => '发现数千条分类广告：汽车、房地产、招聘、电子产品等。在Talabna上轻松安全地买卖。',
                'home_keywords' => '分类广告, 二手车, 房地产, 招聘, 手机, 买卖',
            ],
            'ku' => [
                'home_title' => 'Talabna - مەزنترین بازاڕی ڕیکلامی جیھانی عەرەبی',
                'home_description' => 'بە ھەزاران ڕیکلامی پۆلێنکراو لە ئۆتۆمبێل، خانووبەرە، کار، ئەلیکترۆنیات و زۆرتر بگەڕێ. بە ئاسانی لە طلبنا بکڕە و بفرۆشە.',
                'home_keywords' => 'ڕیکلامی پۆلێنکراو, ئۆتۆمبێلی فرۆشتن, خانووبەرە, کار, مۆبایل',
            ],
            'fa' => [
                'home_title' => 'طلبنا - بزرگترین بازار آگهی‌های طبقه‌بندی شده جهان عرب',
                'home_description' => 'هزاران آگهی طبقه‌بندی شده در خودرو، املاک، مشاغل، الکترونیک و بیشتر را کشف کنید. به راحتی و با امنیت در طلبنا خرید و فروش کنید.',
                'home_keywords' => 'آگهی طبقه‌بندی, فروش خودرو, املاک, مشاغل, گوشی, خرید فروش',
            ],
            'sw' => [
                'home_title' => 'Talabna - Soko Kubwa la Matangazo ya Ulimwengu wa Kiarabu',
                'home_description' => 'Gundua maelfu ya matangazo ya magari, mali isiyohamishika, kazi, elektroniki na zaidi. Nunua na uuze kwa urahisi na usalama kwenye Talabna.',
                'home_keywords' => 'matangazo, magari ya kuuza, mali isiyohamishika, kazi, simu, kununua na kuuza',
            ],
            'ms' => [
                'home_title' => 'Talabna - Pasaran Iklan Terklasifikasi Terbesar di Dunia Arab',
                'home_description' => 'Temui beribu-ribu iklan terklasifikasi untuk kereta, hartanah, pekerjaan, elektronik dan banyak lagi. Beli dan jual dengan mudah di Talabna.',
                'home_keywords' => 'iklan terklasifikasi, kereta untuk dijual, hartanah, pekerjaan, telefon, jual beli',
            ],
        ];

        foreach ($translations as $locale => $keys) {
            foreach ($keys as $key => $value) {
                Translation::updateOrCreate(
                    ['locale' => $locale, 'group' => 'seo', 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        $this->command->info('SEO translations seeded for ' . count($translations) . ' languages.');
    }
}
