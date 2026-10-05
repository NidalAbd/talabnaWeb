<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Chat auto-translation (2026-10-05): the language a message was written in and its cached translations. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $t) {
            if (!Schema::hasColumn('messages', 'lang')) $t->string('lang', 8)->nullable();
            if (!Schema::hasColumn('messages', 'translations')) $t->text('translations')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $t) {
            if (Schema::hasColumn('messages', 'translations')) $t->dropColumn('translations');
            if (Schema::hasColumn('messages', 'lang')) $t->dropColumn('lang');
        });
    }
};
