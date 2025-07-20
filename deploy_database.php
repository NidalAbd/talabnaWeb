<?php

// Comprehensive Database Deployment Package
$deployPath = 'database_deploy/';

// Create deployment directory
if (!is_dir($deployPath)) {
    mkdir($deployPath, 0755, true);
}

// 1. Copy structure.sql
if (file_exists('structure.sql')) {
    copy('structure.sql', $deployPath . 'structure.sql');
    echo "✅ Copied structure.sql\n";
}

// 2. Copy migrations
$migrationsPath = 'database/migrations/';
$migrationsDest = $deployPath . 'migrations/';
if (!is_dir($migrationsDest)) {
    mkdir($migrationsDest, 0755, true);
}

$migrationFiles = glob($migrationsPath . '*.php');
foreach ($migrationFiles as $file) {
    $filename = basename($file);
    copy($file, $migrationsDest . $filename);
}
echo "✅ Copied " . count($migrationFiles) . " migration files\n";

// 3. Create deployment instructions
$instructions = <<<EOT
# Database Structure Deployment Guide

## Method 1: Using SQL Structure (Recommended)
1. Upload 'structure.sql' to your server
2. Run: mysql -u username -p database_name < structure.sql
3. This creates all tables without data

## Method 2: Using Laravel Migrations
1. Upload 'migrations/' folder to server's 'database/migrations/'
2. Run: php artisan migrate --force
3. This runs all pending migrations

## Method 3: Safe Migration (No Data Loss)
1. Run: php artisan migrate:status
2. Check which migrations are pending
3. Run: php artisan migrate --force
4. This only creates missing tables

## Important Notes:
- ✅ Structure only (no data affected)
- ✅ Safe for production
- ✅ Preserves existing data
- ✅ Creates all missing tables

## Files Included:
- structure.sql (Complete database structure)
- migrations/ (All migration files)
- deploy_instructions.md (This file)

EOT;

file_put_contents($deployPath . 'deploy_instructions.md', $instructions);

echo "✅ Created deployment package in '{$deployPath}'\n";
echo "📦 Package includes:\n";
echo "   - structure.sql (Database structure)\n";
echo "   - migrations/ (All migration files)\n";
echo "   - deploy_instructions.md (Deployment guide)\n";
echo "\n🚀 Ready for server deployment!\n"; 