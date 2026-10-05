<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Language a just-sent message is waiting to be translated into (batched with everyone else's). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $t) {
            if (!Schema::hasColumn('messages', 'translate_to')) {
                $t->string('translate_to', 8)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $t) {
            if (Schema::hasColumn('messages', 'translate_to')) $t->dropColumn('translate_to');
        });
    }
};
