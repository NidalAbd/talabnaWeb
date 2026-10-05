<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reports get a status so the reporter can follow them (and withdraw while pending). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reports', 'status')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->after('reason'); // pending | reviewed | dismissed
            });
        }
    }

    public function down(): void
    {
        Schema::table('reports', fn (Blueprint $t) => $t->dropColumn('status'));
    }
};
