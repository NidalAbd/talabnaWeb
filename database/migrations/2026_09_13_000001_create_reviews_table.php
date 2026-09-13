<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * User-to-user reviews (a buyer rating a seller after an interaction),
     * shown on the Profile screen's "Reviews" tab. `service_post_id` is
     * optional context (which listing the review is about) — a review
     * isn't required to reference one.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('reviewed_user_id');
            $table->unsignedBigInteger('service_post_id')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_post_id')->references('id')->on('service_posts')->onDelete('set null');

            // One review per (reviewer, reviewed user, listing) — prevents
            // spamming repeat reviews for the same interaction. Reviews with
            // no listing reference aren't covered by this (MySQL treats NULL
            // as distinct in a unique index), which is an acceptable gap.
            $table->unique(['reviewer_id', 'reviewed_user_id', 'service_post_id']);

            $table->index('reviewed_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
