<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One row per platform, checked by the app on launch to decide whether
     * to show a blocking "update required" screen. `minimum_build_number`
     * is the floor — any installed build below it is forced to update when
     * `is_mandatory` is true; `latest_build_number` is just informational.
     */
    public function up(): void
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique(); // 'android' | 'ios'
            $table->string('latest_version');
            $table->unsignedInteger('latest_build_number');
            $table->unsignedInteger('minimum_build_number')->default(1);
            $table->boolean('is_mandatory')->default(false);
            $table->string('update_url');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        // Seeded to match what's live right now, with is_mandatory off — a
        // no-op until the developer bumps minimum_build_number and flips it
        // on for an actual forced update.
        DB::table('app_versions')->insert([
            'platform' => 'android',
            'latest_version' => '1.5.0',
            'latest_build_number' => 90,
            'minimum_build_number' => 1,
            'is_mandatory' => false,
            'update_url' => 'https://play.google.com/store/apps/details?id=com.talabna.talabna',
            'message' => 'A new version of Talabna is available. Please update to continue.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
