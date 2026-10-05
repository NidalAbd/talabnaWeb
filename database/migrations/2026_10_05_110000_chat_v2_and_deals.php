<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chat v2 (2026-10-05): message types (text, image, voice, location, post, system), replies,
 * reactions, delete-for-everyone, last seen; and two-sided confirmed deals for trust.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'type')) $table->string('type', 16)->default('text')->after('sender_id');
            if (! Schema::hasColumn('messages', 'meta')) $table->json('meta')->nullable()->after('body');
            if (! Schema::hasColumn('messages', 'reply_to_id')) $table->unsignedBigInteger('reply_to_id')->nullable()->after('meta');
            if (! Schema::hasColumn('messages', 'reactions')) $table->json('reactions')->nullable()->after('reply_to_id');
            if (! Schema::hasColumn('messages', 'deleted_at')) $table->timestamp('deleted_at')->nullable()->after('read_at');
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_seen_at')) $table->timestamp('last_seen_at')->nullable();
        });

        if (! Schema::hasTable('deals')) {
            Schema::create('deals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id')->index();
                $table->unsignedBigInteger('service_post_id')->nullable()->index();
                $table->unsignedBigInteger('seller_id')->index();
                $table->unsignedBigInteger('buyer_id')->index();
                $table->unsignedBigInteger('proposed_by');
                $table->string('status', 16)->default('pending'); // pending | confirmed | declined | cancelled
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('last_seen_at'));
        Schema::table('messages', fn (Blueprint $t) => $t->dropColumn(['type', 'meta', 'reply_to_id', 'reactions', 'deleted_at']));
    }
};
