<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_post_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('viewed_at')->useCurrent();

            $table->foreign('service_post_id')->references('id')->on('service_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // One record per user per post (track unique views, update viewed_at on re-view)
            $table->unique(['service_post_id', 'user_id']);

            $table->index('user_id');
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');
    }
};
