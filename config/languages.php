<?php

return [
    // Currently active languages (managed via DB, this is fallback)
    'active' => ['ar', 'en'],

    // All supported languages with metadata
    'supported' => [
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'rtl' => true],
        'en' => ['name' => 'English', 'native' => 'English', 'rtl' => false],
        'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'rtl' => false],
        'fr' => ['name' => 'French', 'native' => 'Français', 'rtl' => false],
        'es' => ['name' => 'Spanish', 'native' => 'Español', 'rtl' => false],
        'de' => ['name' => 'German', 'native' => 'Deutsch', 'rtl' => false],
        'ku' => ['name' => 'Kurdish', 'native' => 'کوردی', 'rtl' => true],
        'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'rtl' => true],
        'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'rtl' => true],
        'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'rtl' => false],
        'zh' => ['name' => 'Chinese', 'native' => '中文', 'rtl' => false],
        'ru' => ['name' => 'Russian', 'native' => 'Русский', 'rtl' => false],
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
