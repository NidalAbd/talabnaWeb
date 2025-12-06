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
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('language', 10)->default('ar');
            $table->string('country', 10)->default('SA');
            $table->string('device', 20)->default('all'); // mobile, desktop, tablet, all
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('ctr', 5, 2)->default(0); // Click-through rate
            $table->decimal('position', 6, 2)->default(0); // Average position
            $table->string('top_page')->nullable(); // Best performing page for this keyword
            $table->enum('trend', ['up', 'down', 'stable'])->default('stable');
            $table->decimal('trend_change', 8, 2)->default(0); // Percentage change
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->boolean('is_tracked')->default(true);
            $table->date('date')->nullable(); // Date of data
            $table->timestamps();

            $table->index(['keyword', 'date']);
            $table->index(['language', 'country']);
            $table->index('priority');
            $table->index('is_tracked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
    }
};
