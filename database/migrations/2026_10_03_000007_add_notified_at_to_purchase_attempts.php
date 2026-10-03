<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** When a "finish your purchase" reminder was sent for this attempt (2026-10-03). */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchase_attempts', 'notified_at')) {
            Schema::table('purchase_attempts', fn (Blueprint $t) => $t->timestamp('notified_at')->nullable()->index());
        }
    }

    public function down(): void
    {
        Schema::table('purchase_attempts', fn (Blueprint $t) => $t->dropColumn('notified_at'));
    }
};
