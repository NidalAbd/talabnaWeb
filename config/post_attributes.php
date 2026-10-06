<?php

/*
 * Release C (2026-10-07): structured details per category, shown on the post and usable as filters.
 * type: int (min/max), enum (values), bool, text (max). filter: how the category feed can filter on it.
 */
return [
    // Cars
    4 => [
        'year' => ['type' => 'int', 'min' => 1950, 'max' => 2030, 'filter' => 'range'],
        'mileage_km' => ['type' => 'int', 'min' => 0, 'max' => 2000000, 'filter' => 'max'],
        'transmission' => ['type' => 'enum', 'values' => ['automatic', 'manual'], 'filter' => 'equals'],
        'fuel' => ['type' => 'enum', 'values' => ['petrol', 'diesel', 'hybrid', 'electric', 'gas'], 'filter' => 'equals'],
        'condition' => ['type' => 'enum', 'values' => ['new', 'used'], 'filter' => 'equals'],
    ],
    // Real estate
    3 => [
        'listing' => ['type' => 'enum', 'values' => ['sale', 'rent'], 'filter' => 'equals'],
        'rooms' => ['type' => 'int', 'min' => 0, 'max' => 50, 'filter' => 'min'],
        'bathrooms' => ['type' => 'int', 'min' => 0, 'max' => 30, 'filter' => 'min'],
        'area_m2' => ['type' => 'int', 'min' => 1, 'max' => 1000000, 'filter' => 'range'],
        'furnished' => ['type' => 'bool', 'filter' => 'equals'],
    ],
    // Devices
    2 => [
        'condition' => ['type' => 'enum', 'values' => ['new', 'like_new', 'used', 'for_parts'], 'filter' => 'equals'],
        'storage_gb' => ['type' => 'int', 'min' => 1, 'max' => 100000, 'filter' => 'min'],
        'brand' => ['type' => 'text', 'max' => 40],
    ],
];
