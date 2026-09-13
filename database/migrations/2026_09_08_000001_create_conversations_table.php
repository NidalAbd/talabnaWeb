<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            // Always stored with user_one_id < user_two_id so a unique
            // constraint on the pair naturally prevents duplicate threads
            // between the same two users, regardless of who starts it.
            $table->unsignedBigInteger('user_one_id');
            $table->unsignedBigInteger('user_two_id');
            $table->unsignedBigInteger('service_post_id')->nullable();
            $table->text('last_message_body')->nullable();
            $table->unsignedBigInteger('last_message_sender_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('user_one_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_two_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('service_post_id')->references('id')->on('service_posts')->nullOnDelete();
            $table->unique(['user_one_id', 'user_two_id', 'service_post_id']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
