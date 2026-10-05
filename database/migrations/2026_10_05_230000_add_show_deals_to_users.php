<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Privacy (2026-10-05): a user can hide their confirmed sold/bought counts from others. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'show_deals')) {
            Schema::table('users', fn (Blueprint $t) => $t->boolean('show_deals')->default(true));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'show_deals')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('show_deals'));
        }
    }
};
