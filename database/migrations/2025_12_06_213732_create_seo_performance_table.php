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
        Schema::create('seo_performance', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('page_type', 50)->default('other'); // home, category, subcategory, post, city, country
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the related entity
            $table->string('entity_type')->nullable(); // Model name (Category, Sub_categories, etc.)
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('ctr', 5, 2)->default(0);
            $table->decimal('position', 6, 2)->default(0);
            $table->json('top_keywords')->nullable(); // Top 5 keywords driving traffic
            $table->json('countries')->nullable(); // Traffic by country
            $table->json('devices')->nullable(); // Traffic by device
            $table->enum('trend', ['up', 'down', 'stable'])->default('stable');
            $table->decimal('trend_change', 8, 2)->default(0);
            $table->date('date');
            $table->timestamps();

            $table->index(['url', 'date']);
            $table->index('page_type');
            $table->index(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_performance');
    }
};
