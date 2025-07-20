<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing route conflicts...\n";

// Clear all caches
echo "Clearing caches...\n";
\Artisan::call('config:clear');
\Artisan::call('cache:clear');
\Artisan::call('view:clear');
\Artisan::call('route:clear');

// Remove cached route files
$cacheFiles = [
    'bootstrap/cache/routes.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/routes-v8.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Removed cached file: {$file}\n";
    }
}

// Cache config only (skip route caching)
echo "Caching config...\n";
\Artisan::call('config:cache');

echo "Route conflict fix completed.\n";
echo "You can now run: php artisan about\n"; 