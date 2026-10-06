<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release C: Pro/Business features bought one by one with points (no subscription needed). */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feature_unlocks')) {
            return;
        }
        Schema::create('feature_unlocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('feature', 40);
            $table->unsignedBigInteger('target_id')->nullable(); // a post, when the unlock is for one post
            $table->unsignedInteger('points');
            $table->timestamp('expires_at')->nullable(); // null: as long as the target exists
            $table->timestamps();
            $table->index(['user_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_unlocks');
    }
};
