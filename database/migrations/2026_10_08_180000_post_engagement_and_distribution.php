<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post statistics and reach (2026-10-08).
 *  - post_engagements: once per person and post, what they did beyond seeing it in the feed (feed_seen) and opening
 *    it (post_views): stopped on it (2 s+ on screen), read it (8 s+ on the post page or to the end), tapped call,
 *    WhatsApp or share.
 *  - post_distribution: how far each post is spread now (its stage) and why (its engagement against similar posts).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_engagements')) {
            Schema::create('post_engagements', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('service_post_id');
                $t->unsignedBigInteger('user_id');
                $t->string('kind', 12); // dwell | read | call | whatsapp | share
                $t->timestamp('created_at')->useCurrent();
                $t->unique(['service_post_id', 'user_id', 'kind']);
                $t->index(['service_post_id', 'kind']);
            });
        }
        if (! Schema::hasTable('post_distribution')) {
            Schema::create('post_distribution', function (Blueprint $t) {
                $t->unsignedBigInteger('service_post_id')->primary();
                $t->unsignedTinyInteger('stage')->default(1); // 1 city, 2 country, 3 nearby countries, 4 everywhere
                $t->boolean('testing')->default(true);
                $t->decimal('score', 8, 4)->default(0);
                $t->decimal('relative', 8, 4)->default(1); // score against the category's average
                $t->unsignedInteger('reach')->default(0);
                $t->unsignedInteger('stopped')->default(0);
                $t->unsignedInteger('opened')->default(0);
                $t->unsignedInteger('read')->default(0);
                $t->unsignedInteger('actions')->default(0);
                $t->timestamp('updated_at')->nullable();
                $t->index('stage');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_distribution');
        Schema::dropIfExists('post_engagements');
    }
};
