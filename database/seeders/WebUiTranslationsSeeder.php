<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class WebUiTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        // All web UI keys that t() uses, with translations for each language
        $translations = [
            'tr' => [
                // Nav
                'nav' => [
                    'home' => 'Ana Sayfa', 'browse' => 'Göz At', 'categories' => 'Kategoriler',
                    'search' => 'Ara...', 'login' => 'Giriş Yap', 'logout' => 'Çıkış Yap',
                    'dashboard' => 'Kontrol Paneli', 'countries' => 'Ülkeler',
                    'quick_links' => 'Hızlı Bağlantılar', 'about' => 'Hakkımızda',
                    'contact' => 'İletişim', 'privacy' => 'Gizlilik Politikası',
                    'terms' => 'Kullanım Şartları', 'legal' => 'Yasal', 'sitemap' => 'Site Haritası',
                ],
                'app' => ['name' => 'Talabna', 'tagline' => 'İlan Pazarı Platformu'],
                'home' => [
                    'hero_title' => 'Hizmetleri ve İlanları Keşfedin', 'hero_subtitle' => 'Binlerce ilan sizi bekliyor',
                    'featured' => 'Öne Çıkan İlanlar', 'latest' => 'En Yeni İlanlar', 'popular' => 'En Popüler',
                    'all_categories' => 'Tüm Kategoriler', 'view_all' => 'Tümünü Gör', 'no_results' => 'Sonuç bulunamadı',
                    'search_placeholder' => 'Araba, daire, iş ara...', 'trusted' => 'Güvenilir İlan Platformu',
                    'find' => 'İhtiyacınızı Bulun', 'easily' => 'Kolayca ve Güvenle',
                    'hero_desc' => 'İş, araba, emlak, elektronik ve hizmetler. 22+ Arap ülkesinde binlerce kullanıcıyla bağlantı kurun.',
                    'search_btn' => 'Ara', 'get_app' => 'Uygulamayı İndir',
                    'active_services' => 'Aktif Hizmetler', 'registered_users' => 'Kayıtlı Kullanıcılar',
                    'premium_services' => 'Premium Hizmetler', 'transactions' => 'İşlemler',
                    'browse_categories' => 'Kategorilere Göz At', 'find_exactly' => 'Tam aradığınızı bulun',
                    'featured_listings' => 'Öne Çıkan İlanlar', 'handpicked' => 'Kullanıcılarımızdan seçilmiş ilanlar',
                    'latest_listings' => 'En Yeni İlanlar', 'recently_added' => 'Platforma yeni eklenenler',
                    'browse_location' => 'Konuma Göre Göz At', 'find_area' => 'Bölgenizdeki hizmetleri bulun',
                    'most_viewed' => 'En Çok Görüntülenen', 'most_popular' => 'En popüler ilanlar',
                    'download_app' => 'Talabna Uygulamasını İndir',
                    'download_desc' => 'İlan verin, kullanıcılarla bağlantı kurun ve hareket halindeyken bildirim alın.',
                    'free' => 'Ücretsiz', 'easy_use' => 'Kullanımı kolay', 'instant_notif' => 'Anlık bildirimler',
                ],
                'listing' => [
                    'price' => 'Fiyat', 'location' => 'Konum', 'contact' => 'İletişim', 'share' => 'Paylaş',
                    'report' => 'Şikayet', 'related' => 'Benzer İlanlar', 'views' => 'görüntüleme',
                    'description' => 'Açıklama', 'listings' => 'ilan', 'member_since' => 'Üye tarihi',
                    'call_now' => 'Şimdi Ara', 'whatsapp' => 'WhatsApp', 'save' => 'İlanı Kaydet',
                ],
                'footer' => [
                    'rights' => 'Tüm hakları saklıdır', 'follow' => 'Bizi Takip Edin',
                    'download' => 'Uygulamayı İndir',
                    'about_desc' => 'En büyük ilan pazarı. Kolayca ve güvenle alın satın.',
                ],
                'browse' => [
                    'title' => 'İlanlara Göz At', 'available' => 'ilan mevcut', 'filters' => 'Filtreler',
                    'clear' => 'Temizle', 'category' => 'Kategori', 'subcategory' => 'Alt Kategori',
                    'country' => 'Ülke', 'city' => 'Şehir', 'apply' => 'Uygula', 'all' => 'Tümü',
                    'loading' => 'Yükleniyor...', 'load_more' => 'Daha Fazla Yükle',
                    'no_results' => 'Sonuç bulunamadı', 'sort_newest' => 'En Yeni',
                ],
                'search' => [
                    'title' => 'Arama Sonuçları', 'results_for' => 'sonuç', 'no_results' => 'Sonuç bulunamadı',
                    'placeholder' => 'Ara...',
                ],
                'about' => [
                    'title' => 'Hakkımızda', 'subtitle' => 'Talabna hikayesini öğrenin', 'mission' => 'Misyonumuz',
                    'values' => 'Değerlerimiz', 'by_numbers' => 'Rakamlarla', 'ready' => 'Başlamaya hazır mısınız?',
                    'download_app' => 'Uygulamayı İndir',
                    'value_security' => 'Güvenlik', 'value_security_desc' => 'Verilerinizi en son güvenlik teknolojileriyle koruyoruz',
                    'value_speed' => 'Hız', 'value_speed_desc' => 'Akıcı deneyimle hızlı ve kolay platform',
                    'value_trust' => 'Güven', 'value_trust_desc' => 'Doğrulanmış kullanıcılardan güvenilir topluluk',
                    'value_coverage' => 'Kapsam', 'value_coverage_desc' => 'Arap bölgesinde 22\'den fazla ülkeye hizmet',
                    'stat_countries' => 'Ülke', 'stat_users' => 'Kullanıcı', 'stat_listings' => 'İlan', 'stat_categories' => 'Kategori',
                ],
                'contact' => ['title' => 'İletişim', 'subtitle' => 'Size yardımcı olmak için buradayız'],
                'privacy' => ['title' => 'Gizlilik Politikası'],
                'terms' => ['title' => 'Kullanım Şartları'],
                'notfound' => ['title' => 'Sayfa Bulunamadı', 'desc' => 'Aradığınız sayfa mevcut değil veya taşınmış.', 'back' => 'Ana Sayfaya Dön'],
                'category' => ['subcategories' => 'Alt Kategoriler', 'in' => 'içinde'],
                'services' => ['title' => 'Hizmetler', 'loading' => 'Yükleniyor...', 'no_listings' => 'İlan bulunamadı'],
            ],
            'fr' => [
                'nav' => [
                    'home' => 'Accueil', 'browse' => 'Parcourir', 'categories' => 'Catégories',
                    'search' => 'Rechercher...', 'login' => 'Connexion', 'logout' => 'Déconnexion',
                    'dashboard' => 'Tableau de bord', 'countries' => 'Pays',
                    'quick_links' => 'Liens rapides', 'about' => 'À propos',
                    'contact' => 'Contact', 'privacy' => 'Politique de confidentialité',
                    'terms' => "Conditions d'utilisation", 'legal' => 'Mentions légales', 'sitemap' => 'Plan du site',
                ],
                'app' => ['name' => 'Talabna', 'tagline' => 'Plateforme de petites annonces'],
                'home' => [
                    'hero_title' => 'Découvrez les services et annonces', 'hero_subtitle' => "Des milliers d'annonces vous attendent",
                    'featured' => 'Annonces en vedette', 'latest' => 'Dernières annonces', 'popular' => 'Les plus populaires',
                    'all_categories' => 'Toutes les catégories', 'view_all' => 'Voir tout', 'no_results' => 'Aucun résultat',
                    'hero_desc' => 'Emplois, voitures, immobilier, électronique et services. Connectez-vous avec des milliers d\'utilisateurs dans plus de 22 pays arabes.',
                    'browse_categories' => 'Parcourir les catégories', 'download_app' => "Télécharger l'application Talabna",
                    'free' => 'Gratuit', 'easy_use' => 'Facile à utiliser', 'instant_notif' => 'Notifications instantanées',
                ],
                'listing' => ['price' => 'Prix', 'location' => 'Lieu', 'contact' => 'Contact', 'share' => 'Partager', 'report' => 'Signaler', 'related' => 'Annonces similaires', 'views' => 'vues', 'listings' => 'annonces'],
                'footer' => ['rights' => 'Tous droits réservés', 'download' => "Télécharger l'app", 'about_desc' => 'La plus grande plateforme de petites annonces. Achetez et vendez facilement.'],
                'browse' => ['title' => 'Parcourir les annonces', 'filters' => 'Filtres', 'no_results' => 'Aucun résultat', 'loading' => 'Chargement...'],
                'search' => ['title' => 'Résultats de recherche', 'results_for' => 'résultats pour', 'no_results' => 'Aucun résultat'],
                'about' => ['title' => 'À propos', 'values' => 'Nos valeurs', 'by_numbers' => 'En chiffres', 'ready' => 'Prêt à commencer?', 'value_security' => 'Sécurité', 'value_speed' => 'Rapidité', 'value_trust' => 'Confiance', 'value_coverage' => 'Couverture', 'stat_countries' => 'Pays', 'stat_users' => 'Utilisateurs', 'stat_listings' => 'Annonces', 'stat_categories' => 'Catégories'],
                'contact' => ['title' => 'Contactez-nous', 'subtitle' => 'Nous sommes là pour vous aider'],
                'privacy' => ['title' => 'Politique de confidentialité'], 'terms' => ['title' => "Conditions d'utilisation"],
                'notfound' => ['title' => 'Page non trouvée', 'desc' => 'La page recherchée n\'existe pas.', 'back' => "Retour à l'accueil"],
            ],
            'es' => [
                'nav' => ['home' => 'Inicio', 'browse' => 'Explorar', 'categories' => 'Categorías', 'search' => 'Buscar...', 'login' => 'Iniciar sesión', 'logout' => 'Cerrar sesión', 'dashboard' => 'Panel', 'countries' => 'Países', 'quick_links' => 'Enlaces rápidos', 'about' => 'Acerca de', 'contact' => 'Contacto', 'privacy' => 'Política de privacidad', 'terms' => 'Términos de servicio', 'legal' => 'Legal', 'sitemap' => 'Mapa del sitio'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Plataforma de anuncios clasificados'],
                'home' => ['hero_title' => 'Descubre servicios y anuncios', 'featured' => 'Anuncios destacados', 'latest' => 'Últimos anuncios', 'popular' => 'Más populares', 'all_categories' => 'Todas las categorías', 'view_all' => 'Ver todo', 'no_results' => 'Sin resultados', 'hero_desc' => 'Empleos, coches, inmuebles, electrónica y servicios. Conéctate con miles de usuarios en más de 22 países árabes.', 'free' => 'Gratis', 'download_app' => 'Descargar la app Talabna'],
                'listing' => ['price' => 'Precio', 'location' => 'Ubicación', 'contact' => 'Contacto', 'share' => 'Compartir', 'report' => 'Reportar', 'related' => 'Anuncios similares', 'listings' => 'anuncios'],
                'footer' => ['rights' => 'Todos los derechos reservados', 'download' => 'Descargar app', 'about_desc' => 'La mayor plataforma de anuncios clasificados.'],
                'browse' => ['title' => 'Explorar anuncios', 'filters' => 'Filtros', 'no_results' => 'Sin resultados'],
                'search' => ['title' => 'Resultados de búsqueda', 'no_results' => 'Sin resultados'],
                'about' => ['title' => 'Acerca de', 'stat_countries' => 'Países', 'stat_users' => 'Usuarios', 'stat_listings' => 'Anuncios', 'stat_categories' => 'Categorías'],
                'contact' => ['title' => 'Contáctanos'], 'privacy' => ['title' => 'Política de privacidad'], 'terms' => ['title' => 'Términos de servicio'],
                'notfound' => ['title' => 'Página no encontrada', 'back' => 'Volver al inicio'],
            ],
            'hi' => [
                'nav' => ['home' => 'होम', 'browse' => 'ब्राउज़ करें', 'categories' => 'श्रेणियाँ', 'search' => 'खोजें...', 'logout' => 'लॉग आउट', 'countries' => 'देश', 'about' => 'हमारे बारे में', 'contact' => 'संपर्क', 'privacy' => 'गोपनीयता नीति', 'terms' => 'सेवा की शर्तें', 'legal' => 'कानूनी'],
                'app' => ['name' => 'Talabna', 'tagline' => 'क्लासीफाइड विज्ञापन प्लेटफॉर्म'],
                'home' => ['hero_title' => 'सेवाएं और विज्ञापन खोजें', 'featured' => 'विशेष विज्ञापन', 'latest' => 'नवीनतम विज्ञापन', 'popular' => 'सबसे लोकप्रिय', 'view_all' => 'सभी देखें', 'no_results' => 'कोई परिणाम नहीं'],
                'listing' => ['price' => 'कीमत', 'contact' => 'संपर्क', 'share' => 'शेयर', 'listings' => 'विज्ञापन'],
                'footer' => ['rights' => 'सर्वाधिकार सुरक्षित', 'download' => 'ऐप डाउनलोड करें'],
                'browse' => ['title' => 'विज्ञापन ब्राउज़ करें', 'no_results' => 'कोई परिणाम नहीं'],
                'about' => ['title' => 'हमारे बारे में', 'stat_countries' => 'देश', 'stat_users' => 'उपयोगकर्ता', 'stat_listings' => 'विज्ञापन'],
                'notfound' => ['title' => 'पेज नहीं मिला', 'back' => 'होम पर वापस जाएं'],
            ],
            'ur' => [
                'nav' => ['home' => 'ہوم', 'browse' => 'براؤز کریں', 'categories' => 'زمرے', 'search' => 'تلاش کریں...', 'logout' => 'لاگ آؤٹ', 'countries' => 'ممالک', 'about' => 'ہمارے بارے میں', 'contact' => 'رابطہ', 'privacy' => 'رازداری کی پالیسی', 'terms' => 'استعمال کی شرائط', 'legal' => 'قانونی'],
                'app' => ['name' => 'طلبنا', 'tagline' => 'درجہ بند اشتہارات کا پلیٹ فارم'],
                'home' => ['hero_title' => 'خدمات اور اشتہارات دریافت کریں', 'featured' => 'نمایاں اشتہارات', 'latest' => 'تازہ ترین اشتہارات', 'popular' => 'سب سے مقبول', 'view_all' => 'سب دیکھیں', 'no_results' => 'کوئی نتیجہ نہیں ملا'],
                'listing' => ['price' => 'قیمت', 'contact' => 'رابطہ', 'share' => 'شیئر', 'listings' => 'اشتہارات'],
                'footer' => ['rights' => 'جملہ حقوق محفوظ ہیں', 'download' => 'ایپ ڈاؤن لوڈ کریں'],
                'notfound' => ['title' => 'صفحہ نہیں ملا', 'back' => 'ہوم پر واپس جائیں'],
            ],
            'ru' => [
                'nav' => ['home' => 'Главная', 'browse' => 'Обзор', 'categories' => 'Категории', 'search' => 'Поиск...', 'logout' => 'Выход', 'countries' => 'Страны', 'about' => 'О нас', 'contact' => 'Контакты', 'privacy' => 'Политика конфиденциальности', 'terms' => 'Условия использования', 'legal' => 'Правовая информация'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Площадка объявлений'],
                'home' => ['hero_title' => 'Откройте услуги и объявления', 'featured' => 'Избранные объявления', 'latest' => 'Новые объявления', 'popular' => 'Популярные', 'view_all' => 'Смотреть все', 'no_results' => 'Ничего не найдено', 'hero_desc' => 'Работа, авто, недвижимость, электроника и услуги.', 'free' => 'Бесплатно', 'download_app' => 'Скачать приложение Talabna'],
                'listing' => ['price' => 'Цена', 'contact' => 'Связаться', 'share' => 'Поделиться', 'listings' => 'объявлений'],
                'footer' => ['rights' => 'Все права защищены', 'download' => 'Скачать приложение'],
                'browse' => ['title' => 'Обзор объявлений', 'no_results' => 'Ничего не найдено'],
                'about' => ['title' => 'О нас', 'stat_countries' => 'Стран', 'stat_users' => 'Пользователей', 'stat_listings' => 'Объявлений'],
                'notfound' => ['title' => 'Страница не найдена', 'back' => 'На главную'],
            ],
            'de' => [
                'nav' => ['home' => 'Startseite', 'browse' => 'Durchsuchen', 'categories' => 'Kategorien', 'search' => 'Suchen...', 'logout' => 'Abmelden', 'countries' => 'Länder', 'about' => 'Über uns', 'contact' => 'Kontakt', 'privacy' => 'Datenschutz', 'terms' => 'Nutzungsbedingungen', 'legal' => 'Rechtliches'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Kleinanzeigen-Plattform'],
                'home' => ['hero_title' => 'Entdecken Sie Dienste und Anzeigen', 'featured' => 'Empfohlene Anzeigen', 'latest' => 'Neueste Anzeigen', 'popular' => 'Beliebteste', 'view_all' => 'Alle anzeigen', 'no_results' => 'Keine Ergebnisse'],
                'listing' => ['price' => 'Preis', 'contact' => 'Kontakt', 'share' => 'Teilen', 'listings' => 'Anzeigen'],
                'footer' => ['rights' => 'Alle Rechte vorbehalten', 'download' => 'App herunterladen'],
                'notfound' => ['title' => 'Seite nicht gefunden', 'back' => 'Zurück zur Startseite'],
            ],
            'zh' => [
                'nav' => ['home' => '首页', 'browse' => '浏览', 'categories' => '分类', 'search' => '搜索...', 'logout' => '退出', 'countries' => '国家', 'about' => '关于我们', 'contact' => '联系我们', 'privacy' => '隐私政策', 'terms' => '服务条款', 'legal' => '法律'],
                'app' => ['name' => 'Talabna', 'tagline' => '分类广告平台'],
                'home' => ['hero_title' => '发现服务和广告', 'featured' => '精选广告', 'latest' => '最新广告', 'popular' => '最受欢迎', 'view_all' => '查看全部', 'no_results' => '没有结果'],
                'listing' => ['price' => '价格', 'contact' => '联系', 'share' => '分享', 'listings' => '条广告'],
                'footer' => ['rights' => '版权所有', 'download' => '下载应用'],
                'notfound' => ['title' => '页面未找到', 'back' => '返回首页'],
            ],
            'pt' => [
                'nav' => ['home' => 'Início', 'browse' => 'Explorar', 'categories' => 'Categorias', 'search' => 'Pesquisar...', 'logout' => 'Sair', 'countries' => 'Países', 'about' => 'Sobre', 'contact' => 'Contato', 'privacy' => 'Política de Privacidade', 'terms' => 'Termos de Serviço', 'legal' => 'Legal'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Plataforma de classificados'],
                'home' => ['hero_title' => 'Descubra serviços e anúncios', 'featured' => 'Anúncios em destaque', 'latest' => 'Anúncios recentes', 'popular' => 'Mais populares', 'view_all' => 'Ver tudo', 'no_results' => 'Nenhum resultado'],
                'listing' => ['price' => 'Preço', 'contact' => 'Contato', 'share' => 'Compartilhar', 'listings' => 'anúncios'],
                'footer' => ['rights' => 'Todos os direitos reservados', 'download' => 'Baixar app'],
                'notfound' => ['title' => 'Página não encontrada', 'back' => 'Voltar ao início'],
            ],
            'id' => [
                'nav' => ['home' => 'Beranda', 'browse' => 'Jelajahi', 'categories' => 'Kategori', 'search' => 'Cari...', 'logout' => 'Keluar', 'countries' => 'Negara', 'about' => 'Tentang Kami', 'contact' => 'Kontak', 'privacy' => 'Kebijakan Privasi', 'terms' => 'Ketentuan Layanan'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Platform iklan baris'],
                'home' => ['hero_title' => 'Temukan layanan dan iklan', 'featured' => 'Iklan unggulan', 'latest' => 'Iklan terbaru', 'popular' => 'Terpopuler', 'view_all' => 'Lihat semua', 'no_results' => 'Tidak ada hasil'],
                'listing' => ['price' => 'Harga', 'contact' => 'Kontak', 'share' => 'Bagikan', 'listings' => 'iklan'],
                'footer' => ['rights' => 'Hak cipta dilindungi', 'download' => 'Unduh aplikasi'],
                'notfound' => ['title' => 'Halaman tidak ditemukan', 'back' => 'Kembali ke beranda'],
            ],
            'bn' => [
                'nav' => ['home' => 'হোম', 'browse' => 'ব্রাউজ করুন', 'categories' => 'বিভাগ', 'search' => 'খুঁজুন...', 'about' => 'আমাদের সম্পর্কে', 'contact' => 'যোগাযোগ'],
                'app' => ['name' => 'Talabna', 'tagline' => 'শ্রেণীবদ্ধ বিজ্ঞাপন প্ল্যাটফর্ম'],
                'home' => ['featured' => 'বৈশিষ্ট্যযুক্ত বিজ্ঞাপন', 'latest' => 'সর্বশেষ বিজ্ঞাপন', 'view_all' => 'সব দেখুন', 'no_results' => 'কোন ফলাফল নেই'],
                'listing' => ['price' => 'মূল্য', 'listings' => 'বিজ্ঞাপন'],
                'footer' => ['rights' => 'সর্বস্বত্ব সংরক্ষিত'],
                'notfound' => ['title' => 'পৃষ্ঠা পাওয়া যায়নি'],
            ],
            'ku' => [
                'nav' => ['home' => 'سەرەتا', 'browse' => 'گەڕان', 'categories' => 'پۆلەکان', 'search' => 'گەڕان...', 'about' => 'دەربارەی ئێمە', 'contact' => 'پەیوەندی'],
                'app' => ['name' => 'طلبنا', 'tagline' => 'بازاڕی ڕیکلام'],
                'home' => ['featured' => 'ڕیکلامە تایبەتەکان', 'latest' => 'نوێترین ڕیکلامەکان', 'view_all' => 'هەموو ببینە'],
                'listing' => ['price' => 'نرخ', 'listings' => 'ڕیکلام'],
                'footer' => ['rights' => 'هەموو مافەکان پارێزراون'],
                'notfound' => ['title' => 'پەڕە نەدۆزرایەوە'],
            ],
            'fa' => [
                'nav' => ['home' => 'خانه', 'browse' => 'مرور', 'categories' => 'دسته‌بندی‌ها', 'search' => 'جستجو...', 'about' => 'درباره ما', 'contact' => 'تماس با ما', 'privacy' => 'حریم خصوصی', 'terms' => 'شرایط استفاده'],
                'app' => ['name' => 'طلبنا', 'tagline' => 'پلتفرم آگهی‌های طبقه‌بندی شده'],
                'home' => ['featured' => 'آگهی‌های ویژه', 'latest' => 'جدیدترین آگهی‌ها', 'view_all' => 'مشاهده همه', 'no_results' => 'نتیجه‌ای یافت نشد'],
                'listing' => ['price' => 'قیمت', 'contact' => 'تماس', 'listings' => 'آگهی'],
                'footer' => ['rights' => 'تمامی حقوق محفوظ است'],
                'notfound' => ['title' => 'صفحه یافت نشد'],
            ],
            'sw' => [
                'nav' => ['home' => 'Nyumbani', 'browse' => 'Vinjari', 'categories' => 'Kategoria', 'search' => 'Tafuta...', 'about' => 'Kuhusu Sisi', 'contact' => 'Wasiliana Nasi'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Jukwaa la matangazo'],
                'home' => ['featured' => 'Matangazo maalum', 'latest' => 'Matangazo mapya', 'view_all' => 'Ona yote'],
                'listing' => ['price' => 'Bei', 'listings' => 'matangazo'],
                'footer' => ['rights' => 'Haki zote zimehifadhiwa'],
                'notfound' => ['title' => 'Ukurasa haupatikani'],
            ],
            'ms' => [
                'nav' => ['home' => 'Utama', 'browse' => 'Layari', 'categories' => 'Kategori', 'search' => 'Cari...', 'about' => 'Tentang Kami', 'contact' => 'Hubungi Kami'],
                'app' => ['name' => 'Talabna', 'tagline' => 'Platform iklan terklasifikasi'],
                'home' => ['featured' => 'Iklan pilihan', 'latest' => 'Iklan terbaru', 'view_all' => 'Lihat semua'],
                'listing' => ['price' => 'Harga', 'listings' => 'iklan'],
                'footer' => ['rights' => 'Hak cipta terpelihara'],
                'notfound' => ['title' => 'Halaman tidak dijumpai'],
            ],
        ];

        $count = 0;
        foreach ($translations as $locale => $groups) {
            foreach ($groups as $group => $keys) {
                foreach ($keys as $key => $value) {
                    Translation::updateOrCreate(
                        ['locale' => $locale, 'group' => $group, 'key' => $key],
                        ['value' => $value]
                    );
                    $count++;
                }
            }
        }

        $this->command->info("Seeded {$count} web UI translations for " . count($translations) . " languages.");
    }
}
