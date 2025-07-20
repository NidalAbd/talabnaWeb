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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Multilingual name
            $table->json('description')->nullable(); // Multilingual description
            $table->string('icon')->nullable(); // Icon class name
            $table->string('color')->default('#6c757d'); // Badge color
            $table->integer('points_per_day')->default(0); // Points cost per day
            $table->integer('view_boost_percentage')->default(0); // View boost percentage
            $table->integer('display_order')->default(0); // Order for display
            $table->boolean('is_active')->default(true); // Whether level is active
            $table->boolean('is_premium')->default(false); // Whether it's a premium level
            $table->json('features')->nullable(); // JSON array of features
            $table->timestamps();
        });

        // Add level_id to service_posts table
        Schema::table('service_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('level_id')->nullable()->after('have_badge');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_posts', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        });
        
        Schema::dropIfExists('levels');
    }
}; 