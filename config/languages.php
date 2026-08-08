<?php

return [
    // Currently active languages (managed via DB, this is fallback)
    'active' => ['ar', 'en'],

    // All supported languages with metadata. Mirrors database/seeders/
    // AllLanguagesSeeder.php and android/.../res/xml/locale_config.xml.
    // 71 unique base languages (regional variants fall back to base at runtime).
    'supported' => [
        'ar'  => ['name' => 'Arabic', 'native' => 'العربية', 'rtl' => true],
        'en'  => ['name' => 'English', 'native' => 'English', 'rtl' => false],
        'af'  => ['name' => 'Afrikaans', 'native' => 'Afrikaans', 'rtl' => false],
        'am'  => ['name' => 'Amharic', 'native' => 'አማርኛ', 'rtl' => false],
        'az'  => ['name' => 'Azerbaijani', 'native' => 'Azərbaycan', 'rtl' => false],
        'be'  => ['name' => 'Belarusian', 'native' => 'Беларуская', 'rtl' => false],
        'bg'  => ['name' => 'Bulgarian', 'native' => 'Български', 'rtl' => false],
        'bn'  => ['name' => 'Bengali', 'native' => 'বাংলা', 'rtl' => false],
        'ca'  => ['name' => 'Catalan', 'native' => 'Català', 'rtl' => false],
        'cs'  => ['name' => 'Czech', 'native' => 'Čeština', 'rtl' => false],
        'da'  => ['name' => 'Danish', 'native' => 'Dansk', 'rtl' => false],
        'de'  => ['name' => 'German', 'native' => 'Deutsch', 'rtl' => false],
        'el'  => ['name' => 'Greek', 'native' => 'Ελληνικά', 'rtl' => false],
        'es'  => ['name' => 'Spanish', 'native' => 'Español', 'rtl' => false],
        'et'  => ['name' => 'Estonian', 'native' => 'Eesti', 'rtl' => false],
        'eu'  => ['name' => 'Basque', 'native' => 'Euskara', 'rtl' => false],
        'fa'  => ['name' => 'Persian', 'native' => 'فارسی', 'rtl' => true],
        'fi'  => ['name' => 'Finnish', 'native' => 'Suomi', 'rtl' => false],
        'fil' => ['name' => 'Filipino', 'native' => 'Filipino', 'rtl' => false],
        'fr'  => ['name' => 'French', 'native' => 'Français', 'rtl' => false],
        'gl'  => ['name' => 'Galician', 'native' => 'Galego', 'rtl' => false],
        'gu'  => ['name' => 'Gujarati', 'native' => 'ગુજરાતી', 'rtl' => false],
        'he'  => ['name' => 'Hebrew', 'native' => 'עברית', 'rtl' => true],
        'hi'  => ['name' => 'Hindi', 'native' => 'हिन्दी', 'rtl' => false],
        'hr'  => ['name' => 'Croatian', 'native' => 'Hrvatski', 'rtl' => false],
        'hu'  => ['name' => 'Hungarian', 'native' => 'Magyar', 'rtl' => false],
        'hy'  => ['name' => 'Armenian', 'native' => 'Հայերեն', 'rtl' => false],
        'id'  => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'rtl' => false],
        'is'  => ['name' => 'Icelandic', 'native' => 'Íslenska', 'rtl' => false],
        'it'  => ['name' => 'Italian', 'native' => 'Italiano', 'rtl' => false],
        'ja'  => ['name' => 'Japanese', 'native' => '日本語', 'rtl' => false],
        'ka'  => ['name' => 'Georgian', 'native' => 'ქართული', 'rtl' => false],
        'kk'  => ['name' => 'Kazakh', 'native' => 'Қазақ', 'rtl' => false],
        'km'  => ['name' => 'Khmer', 'native' => 'ខ្មែរ', 'rtl' => false],
        'kn'  => ['name' => 'Kannada', 'native' => 'ಕನ್ನಡ', 'rtl' => false],
        'ko'  => ['name' => 'Korean', 'native' => '한국어', 'rtl' => false],
        'ku'  => ['name' => 'Kurdish', 'native' => 'کوردی', 'rtl' => true],
        'ky'  => ['name' => 'Kyrgyz', 'native' => 'Кыргызча', 'rtl' => false],
        'lo'  => ['name' => 'Lao', 'native' => 'ລາວ', 'rtl' => false],
        'lt'  => ['name' => 'Lithuanian', 'native' => 'Lietuvių', 'rtl' => false],
        'lv'  => ['name' => 'Latvian', 'native' => 'Latviešu', 'rtl' => false],
        'mk'  => ['name' => 'Macedonian', 'native' => 'Македонски', 'rtl' => false],
        'ml'  => ['name' => 'Malayalam', 'native' => 'മലയാളം', 'rtl' => false],
        'mn'  => ['name' => 'Mongolian', 'native' => 'Монгол', 'rtl' => false],
        'mr'  => ['name' => 'Marathi', 'native' => 'मराठी', 'rtl' => false],
        'ms'  => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'rtl' => false],
        'my'  => ['name' => 'Burmese', 'native' => 'မြန်မာ', 'rtl' => false],
        'ne'  => ['name' => 'Nepali', 'native' => 'नेपाली', 'rtl' => false],
        'nl'  => ['name' => 'Dutch', 'native' => 'Nederlands', 'rtl' => false],
        'no'  => ['name' => 'Norwegian', 'native' => 'Norsk', 'rtl' => false],
        'pa'  => ['name' => 'Punjabi', 'native' => 'ਪੰਜਾਬੀ', 'rtl' => false],
        'pl'  => ['name' => 'Polish', 'native' => 'Polski', 'rtl' => false],
        'pt'  => ['name' => 'Portuguese', 'native' => 'Português', 'rtl' => false],
        'rm'  => ['name' => 'Romansh', 'native' => 'Rumantsch', 'rtl' => false],
        'ro'  => ['name' => 'Romanian', 'native' => 'Română', 'rtl' => false],
        'ru'  => ['name' => 'Russian', 'native' => 'Русский', 'rtl' => false],
        'si'  => ['name' => 'Sinhala', 'native' => 'සිංහල', 'rtl' => false],
        'sk'  => ['name' => 'Slovak', 'native' => 'Slovenčina', 'rtl' => false],
        'sl'  => ['name' => 'Slovenian', 'native' => 'Slovenščina', 'rtl' => false],
        'sq'  => ['name' => 'Albanian', 'native' => 'Shqip', 'rtl' => false],
        'sr'  => ['name' => 'Serbian', 'native' => 'Српски', 'rtl' => false],
        'sv'  => ['name' => 'Swedish', 'native' => 'Svenska', 'rtl' => false],
        'sw'  => ['name' => 'Swahili', 'native' => 'Kiswahili', 'rtl' => false],
        'ta'  => ['name' => 'Tamil', 'native' => 'தமிழ்', 'rtl' => false],
        'te'  => ['name' => 'Telugu', 'native' => 'తెలుగు', 'rtl' => false],
        'th'  => ['name' => 'Thai', 'native' => 'ไทย', 'rtl' => false],
        'tr'  => ['name' => 'Turkish', 'native' => 'Türkçe', 'rtl' => false],
        'uk'  => ['name' => 'Ukrainian', 'native' => 'Українська', 'rtl' => false],
        'ur'  => ['name' => 'Urdu', 'native' => 'اردو', 'rtl' => true],
        'vi'  => ['name' => 'Vietnamese', 'native' => 'Tiếng Việt', 'rtl' => false],
        'zh'  => ['name' => 'Chinese', 'native' => '中文', 'rtl' => false],
        'zu'  => ['name' => 'Zulu', 'native' => 'isiZulu', 'rtl' => false],
    ],

    // Fields that need to be translated per model
    'required_fields' => [
        'service_posts' => ['title', 'description'],
        'categories' => ['name'],
        'sub_categories' => ['name'],
        'badge_types' => ['name'],
        'subscription_plans' => ['name', 'description'],
    ],

    // 3-Tier translation system
    // Tier 1: UI strings (translations table) - fast, activates language
    // Tier 2: Core content - categories, subcategories, badge types (small, structured)
    // Tier 3: Dynamic content - service posts (large, translated progressively)
    'tiers' => [
        1 => ['ui_strings'],
        2 => ['categories', 'sub_categories', 'badge_types', 'subscription_plans'],
        3 => ['service_posts'],
    ],

    // Tier required for language activation (1 = UI strings only)
    'activation_tier' => 1,

    // Priority order for Tier 3 background translation
    'tier3_priority' => [
        'service_posts' => ['order_by' => 'id', 'direction' => 'desc', 'batch_size' => 50],
    ],
];
