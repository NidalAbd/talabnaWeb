<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportDatabaseStructure extends Command
{
    protected $signature = 'db:export-structure {--output=structure.sql}';
    protected $description = 'Export database structure without data';

    public function handle()
    {
        $outputFile = $this->option('output');
        $this->info("Exporting database structure to {$outputFile}...");

        // Get database name and all table names
        $databaseName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $sql = [];

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            
            if ($tableName === 'migrations') continue; // Skip migrations table
            
            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $createStatement = $createTable->{'Create Table'};
            
            $sql[] = "\n-- Table structure for table `{$tableName}`\n";
            $sql[] = "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql[] = $createStatement . ";\n";
        }

        // Write to file
        file_put_contents($outputFile, implode('', $sql));
        
        $this->info("Database structure exported to {$outputFile}");
        $this->info("Total tables: " . (count($tables) - 1)); // Exclude migrations table
        
        return 0;
    }
} 