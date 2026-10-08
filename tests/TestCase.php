<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Tests run on SQLite, which has no MySQL FIELD(); the app orders by it (post states, nearest countries).
        $pdo = DB::connection()->getDriverName() === 'sqlite' ? DB::connection()->getPdo() : null;
        $pdo?->sqliteCreateFunction('FIELD', function ($value, ...$list) {
            $i = array_search((string) $value, array_map('strval', $list), true);

            return $i === false ? 0 : $i + 1;
        });
    }
}
