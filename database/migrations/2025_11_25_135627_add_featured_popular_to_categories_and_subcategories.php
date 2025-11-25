<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add is_featured and is_popular flags to categories and sub_categories
     */
    public function up(): void
    {
        // Add to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('isSuspended');
            $table->boolean('is_popular')->default(false)->after('is_featured');
        });

        // Add to sub_categories table
        Schema::table('sub_categories', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('isSuspended');
            $table->boolean('is_popular')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_popular']);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_popular']);
        });
    }
};
