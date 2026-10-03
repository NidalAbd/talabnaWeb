<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** The app language each user last used, so pushes arrive in it (2026-10-03). */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'locale')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('locale', 8)->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'locale')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('locale'));
        }
    }
};
