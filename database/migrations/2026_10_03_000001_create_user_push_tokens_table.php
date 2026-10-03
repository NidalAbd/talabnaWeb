<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-device push (2026-10-03): one row per device token. users.fcm_token
 * stays as "the latest device" so existing checks keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_push_tokens')) return;
        Schema::create('user_push_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('token', 512);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        DB::table('users')->whereNotNull('fcm_token')->where('fcm_token', '!=', '')
            ->select('id', 'fcm_token')->orderBy('id')
            ->chunk(500, function ($users) {
                $rows = [];
                foreach ($users as $u) {
                    $h = hash('sha256', $u->fcm_token);
                    $rows[$h] = ['user_id' => $u->id, 'token' => $u->fcm_token, 'token_hash' => $h,
                        'last_seen_at' => now(), 'created_at' => now(), 'updated_at' => now()];
                }
                DB::table('user_push_tokens')->insertOrIgnore(array_values($rows));
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_push_tokens');
    }
};
