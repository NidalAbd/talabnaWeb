<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Search Console Configuration
    |--------------------------------------------------------------------------
    */
    'google_search_console' => [
        'enabled' => env('SEO_GSC_ENABLED', false),
        'site_url' => env('SEO_GSC_SITE_URL', env('APP_URL')),
        'credentials_path' => env('SEO_GSC_CREDENTIALS_PATH', storage_path('app/google-service-account.json')),
        'api_version' => 'v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention Settings
    |--------------------------------------------------------------------------
    */
    'data_retention' => [
        'keywords_days' => env('SEO_KEYWORDS_RETENTION_DAYS', 90),
        'performance_days' => env('SEO_PERFORMANCE_RETENTION_DAYS', 90),
        'logs_days' => env('SEO_LOGS_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'default_date_range' => env('SEO_DEFAULT_DATE_RANGE', 28), // Last 28 days
        'max_rows_per_request' => env('SEO_MAX_ROWS', 25000),
        'batch_size' => env('SEO_BATCH_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'position_drop_threshold' => env('SEO_POSITION_DROP_THRESHOLD', 5), // Alert if position drops by more than 5
        'ctr_drop_threshold' => env('SEO_CTR_DROP_THRESHOLD', 20), // Alert if CTR drops by more than 20%
        'impressions_drop_threshold' => env('SEO_IMPRESSIONS_DROP_THRESHOLD', 30), // Alert if impressions drop by more than 30%
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Countries
    |--------------------------------------------------------------------------
    */
    'countries' => [
        'SA' => 'Saudi Arabia',
        'AE' => 'UAE',
        'EG' => 'Egypt',
        'JO' => 'Jordan',
        'KW' => 'Kuwait',
        'QA' => 'Qatar',
        'BH' => 'Bahrain',
        'OM' => 'Oman',
        'IQ' => 'Iraq',
        'LB' => 'Lebanon',
        'SY' => 'Syria',
        'PS' => 'Palestine',
        'YE' => 'Yemen',
        'LY' => 'Libya',
        'TN' => 'Tunisia',
        'MA' => 'Morocco',
        'DZ' => 'Algeria',
        'SD' => 'Sudan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Types
    |--------------------------------------------------------------------------
    */
    'page_types' => [
        'home' => 'Home Page',
        'category' => 'Category Page',
        'subcategory' => 'Subcategory Page',
        'post' => 'Service Post',
        'city' => 'City Page',
        'country' => 'Country Page',
        'user' => 'User Profile',
        'other' => 'Other',
    ],
];
