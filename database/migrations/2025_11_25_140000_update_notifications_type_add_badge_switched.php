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
        // Add 'badge_switched' to the enum (include all existing types)
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'new_servicepost',
            'update_servicepost',
            'new_comment',
            'approved_servicepost',
            'unapproved_servicepost',
            'banned',
            'unbanned',
            'points_approved',
            'points_rejected',
            'badge_applied',
            'badge_expired',
            'badge_switched',
            'user',
            'pointIn',
            'login',
            'post',
            'follower',
            'badge',
            'sub_category',
            'report',
            'photo',
            'pointOut'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'new_servicepost',
            'update_servicepost',
            'new_comment',
            'approved_servicepost',
            'unapproved_servicepost',
            'banned',
            'unbanned',
            'points_approved',
            'points_rejected',
            'badge_applied',
            'badge_expired'
        ) NOT NULL");
    }
};
