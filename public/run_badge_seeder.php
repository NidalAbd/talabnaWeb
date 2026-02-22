<?php
/**
 * Temporary script to run BadgeTypeSeeder and LanguageSeeder on production.
 * Self-deletes after successful execution.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');

echo "=== Running Seeders ===\n\n";

$laravelPath = dirname(__DIR__);
chdir($laravelPath);

// Bootstrap Laravel
require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Run BadgeTypeSeeder
    echo "1. Running BadgeTypeSeeder...\n";
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\BadgeTypeSeeder',
        '--force' => true,
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();

    // 2. Run LanguageSeeder
    echo "\n2. Running LanguageSeeder...\n";
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\LanguageSeeder',
        '--force' => true,
    ]);
    echo \Illuminate\Support\Facades\Artisan::output();

    // 3. Clear all caches
    echo "\n3. Clearing caches...\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "  " . trim(\Illuminate\Support\Facades\Artisan::output()) . "\n";
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "  " . trim(\Illuminate\Support\Facades\Artisan::output()) . "\n";

    // 4. Verify badge_types
    echo "\n4. Verifying badge_types:\n";
    $badges = \App\Models\BadgeType::all(['slug', 'color', 'secondary_color']);
    foreach ($badges as $b) {
        echo "  {$b->slug}: color={$b->color}, secondary_color={$b->secondary_color}\n";
    }

    echo "\nAll done! Self-deleting this script...\n";
    @unlink(__FILE__);
    echo "Deleted.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
