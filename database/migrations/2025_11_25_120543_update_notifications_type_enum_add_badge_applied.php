<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'badge_applied', 'badge_removed', 'badge_expired' to notifications type enum
     */
    public function up(): void
    {
        // Add new notification types for badge operations
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'user',
            'follower',
            'login',
            'register',
            'post',
            'badge',
            'badge_applied',
            'badge_removed',
            'badge_expired',
            'password',
            'email',
            'report',
            'photo',
            'pointIn',
            'pointOut',
            'sub_category'
        ) DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert badge-related notifications to 'badge'
        DB::statement("UPDATE notifications SET type = 'badge' WHERE type IN ('badge_applied', 'badge_removed', 'badge_expired')");

        // Remove new types
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'user',
            'follower',
            'login',
            'register',
            'post',
            'badge',
            'password',
            'email',
            'report',
            'photo',
            'pointIn',
            'pointOut',
            'sub_category'
        ) DEFAULT 'user'");
    }
};
