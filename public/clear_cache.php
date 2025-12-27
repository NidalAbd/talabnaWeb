<?php
/**
 * Cache Clearing Script
 * Access via: https://talbna.cloud/clear_cache.php?key=talabna2025
 * This script clears all Laravel caches without using shell_exec
 */

// Security key - change this in production
$securityKey = 'talabna2025';

if (!isset($_GET['key']) || $_GET['key'] !== $securityKey) {
    http_response_code(403);
    die('Forbidden - Invalid security key');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Clear Cache</title>';
echo '<style>body{font-family:monospace;background:#1a1a1a;color:#0f0;padding:20px;line-height:1.6;}';
echo '.error{color:#f00;}.success{color:#0f0;}.warning{color:#ff0;}</style></head><body>';
echo '<h1>Laravel Cache Clearing</h1><pre>';

// Bootstrap Laravel
$laravelPath = dirname(__DIR__);
echo "Laravel path: $laravelPath\n\n";

// Load Laravel's autoload
require $laravelPath . '/vendor/autoload.php';

// Bootstrap the application
$app = require_once $laravelPath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = [];

// 1. Clear config cache
echo "1. Clearing config cache...\n";
try {
    Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "   ✅ Config cache cleared\n";
    $results['config'] = true;
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['config'] = false;
}

// 2. Clear route cache
echo "2. Clearing route cache...\n";
try {
    Illuminate\Support\Facades\Artisan::call('route:clear');
    echo "   ✅ Route cache cleared\n";
    $results['route'] = true;
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['route'] = false;
}

// 3. Clear view cache
echo "3. Clearing view cache...\n";
try {
    Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "   ✅ View cache cleared\n";
    $results['view'] = true;
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['view'] = false;
}

// 4. Clear application cache
echo "4. Clearing application cache...\n";
try {
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "   ✅ Application cache cleared\n";
    $results['cache'] = true;
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['cache'] = false;
}

// 5. Clear optimize cache
echo "5. Clearing optimize cache...\n";
try {
    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo "   ✅ Optimize cache cleared\n";
    $results['optimize'] = true;
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    $results['optimize'] = false;
}

echo "\n========================================\n";
echo "Summary:\n";
$allSuccess = true;
foreach ($results as $key => $success) {
    $status = $success ? '✅' : '❌';
    echo "  $status $key\n";
    if (!$success) $allSuccess = false;
}

if ($allSuccess) {
    echo "\n✅ All caches cleared successfully!\n";
    echo "\n🔗 Now test the API:\n";
    echo "   https://talbna.cloud/api/purchase/packages\n";
} else {
    echo "\n⚠️ Some caches could not be cleared.\n";
}

echo "\n</pre></body></html>";

// Optionally self-delete after successful run
// if ($allSuccess) {
//     @unlink(__FILE__);
//     echo "<p>This script has been deleted for security.</p>";
// }
