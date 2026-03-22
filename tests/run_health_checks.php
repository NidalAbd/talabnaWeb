<?php
/**
 * Talabna Health Check Suite — Main Runner
 *
 * Runs all check modules and prints a combined summary.
 * Usage: php tests/run_health_checks.php
 *
 * Run a specific check: php tests/checks/field_mapping.php
 */

echo "╔══════════════════════════════════════════╗\n";
echo "║   Talabna Health Check Suite             ║\n";
echo "║   " . date('Y-m-d H:i:s') . "                    ║\n";
echo "╚══════════════════════════════════════════╝\n";

$startTime = microtime(true);

// Bootstrap Laravel once
require_once __DIR__ . '/checks/bootstrap.php';

// Run each check module
$modules = [
    'Data Integrity'       => __DIR__ . '/checks/data_integrity.php',
    'Photo URLs'           => __DIR__ . '/checks/photo_urls.php',
    'Field Mapping'        => __DIR__ . '/checks/field_mapping.php',
    'Admin API Endpoints'  => __DIR__ . '/checks/api_admin_endpoints.php',
    'Public API Endpoints' => __DIR__ . '/checks/api_public_endpoints.php',
];

foreach ($modules as $name => $file) {
    if (!file_exists($file)) {
        echo "\n  SKIP: {$name} — file not found: {$file}\n";
        continue;
    }
    require $file;
}

// Final summary
$r = hc_results();
$elapsed = round(microtime(true) - $startTime, 2);

echo "\n╔══════════════════════════════════════════╗\n";
echo "║   RESULTS                                ║\n";
echo "╚══════════════════════════════════════════╝\n";
echo "  Total: " . ($r['pass'] + $r['fail']) . " tests, {$r['pass']} passed, {$r['fail']} failed\n";
echo "  Time:  {$elapsed}s\n";

if (!empty($r['failures'])) {
    echo "\n  FAILED:\n";
    foreach ($r['failures'] as $f) {
        echo "    ✗ {$f}\n";
    }
}

if (!empty($r['warnings'])) {
    echo "\n  WARNINGS:\n";
    foreach ($r['warnings'] as $w) {
        echo "    ⚠ {$w}\n";
    }
}

echo "\n";
exit($r['fail'] > 0 ? 1 : 0);
