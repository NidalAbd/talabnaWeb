<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chat translation is paid with points (2026-10-05): one point buys a bundle of
 * translated messages; this keeps how many of the bundle are left per reader.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chat_translation_quotas')) return;
        Schema::create('chat_translation_quotas', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->unsignedInteger('remaining')->default(0);
            $t->unsignedInteger('points_spent')->default(0);
            $t->unsignedInteger('translated')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_translation_quotas');
    }
};
