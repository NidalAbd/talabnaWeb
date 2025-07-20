<?php

// Export all migration files for server deployment
$migrationsPath = 'database/migrations/';
$exportPath = 'migrations_export/';

// Create export directory
if (!is_dir($exportPath)) {
    mkdir($exportPath, 0755, true);
}

// Copy all migration files
$migrationFiles = glob($migrationsPath . '*.php');
$copiedFiles = [];

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    $destination = $exportPath . $filename;
    
    if (copy($file, $destination)) {
        $copiedFiles[] = $filename;
    }
}

echo "✅ Exported " . count($copiedFiles) . " migration files to {$exportPath}\n";
echo "📁 Files exported:\n";
foreach ($copiedFiles as $file) {
    echo "   - {$file}\n";
}

echo "\n📋 Instructions for server deployment:\n";
echo "1. Upload the 'migrations_export' folder to your server\n";
echo "2. Copy migration files to 'database/migrations/' on server\n";
echo "3. Run: php artisan migrate --force\n";
echo "4. This will create all tables without affecting existing data\n"; 