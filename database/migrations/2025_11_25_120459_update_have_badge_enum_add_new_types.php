<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'فضي' and 'فلسطين' to have_badge enum
     */
    public function up(): void
    {
        // MySQL doesn't allow direct ALTER for ENUM, so we use raw SQL
        DB::statement("ALTER TABLE service_posts MODIFY COLUMN have_badge ENUM('عادي', 'فضي', 'ذهبي', 'ماسي', 'فلسطين') DEFAULT 'عادي'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original 3 values
        // WARNING: This will truncate any 'فضي' or 'فلسطين' values to 'عادي'
        DB::statement("UPDATE service_posts SET have_badge = 'عادي' WHERE have_badge NOT IN ('عادي', 'ذهبي', 'ماسي')");
        DB::statement("ALTER TABLE service_posts MODIFY COLUMN have_badge ENUM('عادي', 'ذهبي', 'ماسي') DEFAULT 'عادي'");
    }
};
