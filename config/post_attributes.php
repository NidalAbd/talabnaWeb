<?php

/*
 * Structured details per category, shown on the post and usable as filters (Release C 2026-10-07; more fields and
 * search options with counts 2026-10-10).
 * type: int (min/max), enum (values, optional aliases: other spellings that mean a value), bool, text (max).
 * filter: how a feed can filter on it: range | min | max | equals. facet: offered as options with counts in search.
 * Keep the app's lib/data/models/post_details.dart in step (the server drops a field it does not know).
 */
return [
    // Cars
    4 => [
        'make' => ['type' => 'text', 'max' => 30, 'filter' => 'equals', 'facet' => true],
        'model' => ['type' => 'text', 'max' => 40, 'filter' => 'equals', 'facet' => true],
        'year' => ['type' => 'int', 'min' => 1950, 'max' => 2030, 'filter' => 'range', 'facet' => true],
        'mileage_km' => ['type' => 'int', 'min' => 0, 'max' => 2000000, 'filter' => 'max'],
        'transmission' => ['type' => 'enum', 'values' => ['automatic', 'manual'], 'filter' => 'equals', 'facet' => true],
        'fuel' => ['type' => 'enum', 'values' => ['petrol', 'diesel', 'hybrid', 'electric', 'gas'], 'filter' => 'equals', 'facet' => true],
        'condition' => ['type' => 'enum', 'values' => ['new', 'used'], 'filter' => 'equals', 'facet' => true],
        'color' => ['type' => 'enum', 'values' => ['black', 'white', 'silver', 'gray', 'red', 'blue', 'green', 'yellow', 'orange', 'brown', 'gold', 'beige', 'other'], 'filter' => 'equals', 'facet' => true],
    ],
    // Real estate
    3 => [
        'listing' => ['type' => 'enum', 'values' => ['sale', 'rent'], 'filter' => 'equals', 'facet' => true],
        'property_type' => ['type' => 'enum', 'values' => ['apartment', 'house', 'villa', 'land', 'shop', 'office', 'building', 'farm', 'other'], 'filter' => 'equals', 'facet' => true],
        'rooms' => ['type' => 'int', 'min' => 0, 'max' => 50, 'filter' => 'min', 'facet' => true],
        'bathrooms' => ['type' => 'int', 'min' => 0, 'max' => 30, 'filter' => 'min'],
        'area_m2' => ['type' => 'int', 'min' => 1, 'max' => 1000000, 'filter' => 'range'],
        'furnished' => ['type' => 'bool', 'filter' => 'equals', 'facet' => true],
    ],
    // Devices
    2 => [
        'device_type' => ['type' => 'enum', 'values' => ['phone', 'tablet', 'laptop', 'desktop', 'watch', 'tv', 'console', 'camera', 'headphones', 'accessory', 'other'], 'filter' => 'equals', 'facet' => true],
        'brand' => ['type' => 'enum', 'filter' => 'equals', 'facet' => true,
            'values' => ['apple', 'samsung', 'xiaomi', 'huawei', 'oppo', 'vivo', 'realme', 'honor', 'google', 'oneplus', 'nokia', 'motorola', 'sony', 'lg', 'lenovo', 'hp', 'dell', 'asus', 'acer', 'microsoft', 'infinix', 'tecno', 'other'],
            // Earlier posts typed the brand freely: these spellings mean the value
            'aliases' => ['iphone' => 'apple', 'ipad' => 'apple', 'macbook' => 'apple', 'ابل' => 'apple', 'آبل' => 'apple', 'أبل' => 'apple', 'ايفون' => 'apple', 'آيفون' => 'apple',
                'سامسونج' => 'samsung', 'سامسونغ' => 'samsung', 'galaxy' => 'samsung', 'شاومي' => 'xiaomi', 'redmi' => 'xiaomi', 'poco' => 'xiaomi', 'هواوي' => 'huawei',
                'اوبو' => 'oppo', 'أوبو' => 'oppo', 'فيفو' => 'vivo', 'ريلمي' => 'realme', 'هونر' => 'honor', 'جوجل' => 'google', 'pixel' => 'google', 'نوكيا' => 'nokia',
                'سوني' => 'sony', 'playstation' => 'sony', 'لينوفو' => 'lenovo', 'ديل' => 'dell', 'اسوس' => 'asus', 'أسوس' => 'asus', 'ايسر' => 'acer', 'xbox' => 'microsoft',
                'انفينكس' => 'infinix', 'تكنو' => 'tecno']],
        'model' => ['type' => 'text', 'max' => 40, 'filter' => 'equals', 'facet' => true],
        'storage_gb' => ['type' => 'int', 'min' => 1, 'max' => 100000, 'filter' => 'equals', 'facet' => true],
        'ram_gb' => ['type' => 'int', 'min' => 1, 'max' => 1024, 'filter' => 'equals', 'facet' => true],
        'color' => ['type' => 'enum', 'values' => ['black', 'white', 'silver', 'gray', 'gold', 'blue', 'red', 'green', 'purple', 'pink', 'yellow', 'orange', 'titanium', 'other'], 'filter' => 'equals', 'facet' => true],
        'condition' => ['type' => 'enum', 'values' => ['new', 'like_new', 'used', 'for_parts'], 'filter' => 'equals', 'facet' => true],
    ],
];
