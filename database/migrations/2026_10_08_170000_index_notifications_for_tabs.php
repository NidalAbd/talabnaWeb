<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Notification tabs (2026-10-08): the list is read per user, newest first, by type and by read state. */
return new class extends Migration
{
    public function up(): void
    {
        $have = collect(DB::select('SHOW INDEX FROM notifications'))->pluck('Key_name')->unique();
        Schema::table('notifications', function (Blueprint $t) use ($have) {
            if (! $have->contains('notifications_user_created_idx')) {
                $t->index(['user_id', 'created_at'], 'notifications_user_created_idx');
            }
            if (! $have->contains('notifications_user_type_idx')) {
                $t->index(['user_id', 'type'], 'notifications_user_type_idx');
            }
            if (! $have->contains('notifications_user_read_idx')) {
                $t->index(['user_id', 'read'], 'notifications_user_read_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $t) {
            $t->dropIndex('notifications_user_created_idx');
            $t->dropIndex('notifications_user_type_idx');
            $t->dropIndex('notifications_user_read_idx');
        });
    }
};
