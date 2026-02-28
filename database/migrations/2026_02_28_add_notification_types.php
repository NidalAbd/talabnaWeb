<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change enum type column to string to support any notification type
        // This avoids having to alter enum every time we add a new type
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type', 50)->default('user')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum if needed
        // Not reverting since string is more flexible
    }
};
