<?php

namespace Tests\Concerns;

use Illuminate\Support\Str;

/**
 * Builds the schema in in-memory SQLite. Historic migrations contain MySQL-only statements
 * (ENUM alterations, DB::statement, ...) that SQLite rejects; those are skipped, which is fine
 * for tests that only touch the tables/columns that do migrate.
 */
trait MigratesTolerantly
{
    protected function migrateTolerantly(): void
    {
        $files = array_merge(
            glob(base_path('vendor/laravel/passport/database/migrations/*.php')) ?: [],
            glob(database_path('migrations/*.php')) ?: []
        );
        usort($files, fn ($a, $b) => strcmp(basename($a), basename($b)));

        foreach ($files as $file) {
            $migration = MigrationInstances::$byFile[$file] ??= $this->loadMigration($file);
            if (! is_object($migration)) {
                continue;
            }
            try {
                $migration->up();
            } catch (\Throwable $e) {
                // MySQL-only statement.
            }
        }
    }

    /** Anonymous-class migrations return an object; named ones declare a class (loadable once per process). */
    private function loadMigration(string $file): ?object
    {
        $result = require_once $file;
        if (is_object($result)) {
            return $result;
        }
        $class = Str::studly(implode('_', array_slice(explode('_', basename($file, '.php')), 4)));

        return class_exists($class) ? new $class : null;
    }
}

/** Process-wide cache: an anonymous-class migration file can only be require()d once per process. */
final class MigrationInstances
{
    public static array $byFile = [];
}
