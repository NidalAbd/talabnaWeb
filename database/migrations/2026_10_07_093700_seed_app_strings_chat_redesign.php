<?php

use Database\Seeders\TalabnaAppStringsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Loads the app texts added on 2026-10-06 (chat redesign)
 * in English and Arabic, and asks the scheduler to translate them into the other languages in the background
 * (see Kernel: translate:all runs when the i18n:translate-pending flag is set). Safe to run again: the seeder only
 * fills keys that have no value.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new TalabnaAppStringsSeeder())->run();
        Cache::put('i18n:translate-pending', now()->timestamp, now()->addDays(2));
    }

    public function down(): void
    {
    }
};
