<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Admin-editable settings (2026-10-03) — first used for photo/video slot pricing. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $t) {
                $t->id();
                $t->string('key', 100)->unique();
                $t->text('value')->nullable();
                $t->timestamps();
            });
        }
        foreach (['media.free' => config('ai.media.free', 4), 'media.max' => config('ai.media.max', 10),
                  'media.extra_points' => config('ai.media.extra_points', 1)] as $k => $v) {
            DB::table('app_settings')->insertOrIgnore(['key' => $k, 'value' => (string) $v, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
