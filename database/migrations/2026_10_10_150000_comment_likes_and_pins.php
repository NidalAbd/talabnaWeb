<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Comment likes and the post owner's pinned comment (2026-10-10). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (! Schema::hasColumn('comments', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable();
            }
            if (! Schema::hasColumn('comments', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0);
            }
        });
        if (! Schema::hasTable('comment_likes')) {
            Schema::create('comment_likes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comment_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('created_at')->nullable();
                $table->unique(['comment_id', 'user_id']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['pinned_at', 'likes_count']);
        });
    }
};
