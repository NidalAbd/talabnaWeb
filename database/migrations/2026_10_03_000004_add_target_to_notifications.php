<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where tapping a notification leads (2026-10-03): target_type is one of
 * post | user | points | password | email | notifications, target_id the
 * post/user id. Rows without it get one derived from type + message.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $t) {
            if (!Schema::hasColumn('notifications', 'target_type')) $t->string('target_type', 20)->nullable();
            if (!Schema::hasColumn('notifications', 'target_id')) $t->unsignedBigInteger('target_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $t) => $t->dropColumn(['target_type', 'target_id']));
    }
};
