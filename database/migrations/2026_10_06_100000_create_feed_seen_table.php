<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Posts a user has scrolled past in the Home feed (on screen ~1 s), so the feed shows them new ones first. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feed_seen')) {
            return;
        }
        Schema::create('feed_seen', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_post_id');
            $table->timestamp('seen_at')->useCurrent();
            $table->primary(['user_id', 'service_post_id']);
            $table->index('seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_seen');
    }
};
