<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('badge_types', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // {"ar": "ماسي", "en": "Diamond"}
            $table->string('slug')->unique(); // diamond, gold, silver, normal
            $table->string('color', 20)->default('#808080'); // Hex color
            $table->string('icon', 50)->default('mdi-tag'); // MDI icon name
            $table->integer('points_per_day')->default(0); // Cost in points per day
            $table->integer('priority')->default(99); // Lower = higher priority (1 shows first)
            $table->integer('view_boost_percent')->default(0); // View boost percentage
            $table->boolean('is_default')->default(false); // Only one should be true (normal)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add badge_type_id to service_posts table
        Schema::table('service_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('badge_type_id')->nullable()->after('have_badge');
            $table->foreign('badge_type_id')->references('id')->on('badge_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropForeign(['badge_type_id']);
            $table->dropColumn('badge_type_id');
        });

        Schema::dropIfExists('badge_types');
    }
};
