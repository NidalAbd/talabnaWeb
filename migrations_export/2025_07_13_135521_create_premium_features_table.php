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
        Schema::create('premium_features', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Multilingual name
            $table->json('description')->nullable(); // Multilingual description
            $table->integer('point_cost');
            $table->boolean('is_active')->default(true);
            $table->string('feature_type')->default('post_enhancement'); // post_enhancement, user_benefit, system_feature
            $table->string('icon')->nullable();
            $table->string('color')->default('#6c757d');
            $table->json('benefits')->nullable(); // Feature benefits
            $table->integer('display_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium_features');
    }
}; 