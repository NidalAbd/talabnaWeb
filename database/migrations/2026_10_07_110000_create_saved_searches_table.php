<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Release A: searches people save to be told about new matching posts. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_searches')) {
            return;
        }
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('query', 120)->nullable();
            $table->json('filters')->nullable();
            $table->boolean('muted')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
