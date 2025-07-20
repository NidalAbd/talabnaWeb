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
        Schema::create('point_packages', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Multilingual name
            $table->json('description')->nullable(); // Multilingual description
            $table->integer('points_amount');
            $table->decimal('price', 10, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->json('currency_name')->nullable(); // Multilingual currency name
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->integer('display_order')->default(0);
            $table->json('features')->nullable(); // Package features
            $table->integer('validity_days')->nullable(); // How long points are valid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_packages');
    }
}; 